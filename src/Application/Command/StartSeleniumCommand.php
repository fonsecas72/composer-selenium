<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;

class StartSeleniumCommand extends Command
{
    /**
     * @var integer
     */
    public $seleniumStartTimeout = 5000000; // 5 seconds
    /**
     * @var integer
     */
    public $seleniumStartWaitInterval = 25000; // 0.025 seconds
    /**
     * @var integer
     */
    public $port = 4444;

    /**
     * Command configuration
     *
     * @return void
     */
    protected function configure()
    {
        $this
        ->setName('start')
        ->addOption(
            'firefox-profile',
            'p',
            InputOption::VALUE_REQUIRED,
            'Give a custom firefox profile location'
        )
        ->addOption(
            'selenium-location',
            'l',
            InputOption::VALUE_REQUIRED,
            'Set a custom selenium location'
        )
        ->addOption(
            'xvfb',
            null,
            InputOption::VALUE_NONE,
            'Use xvfb to start selenium'
        )
        ->setDescription('This will start Selenium2 server.');
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleStart($input, $output);
        $output->writeln("\nDone");
    }
    
    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     */
    public function handleStart(InputInterface $input, OutputInterface $output)
    {
        $seleniumLocation = $input->getOption('selenium-location') ? : '/opt/selenium/selenium-server-standalone.jar';
        if (!is_readable($seleniumLocation)) {
            throw new \RuntimeException('Selenium jar not found - ' . $seleniumLocation);
        }
        $startSeleniumCmd = $this->getSeleniumStartCommand($input, $output, $seleniumLocation);
        $this->runCmdToStdOut($startSeleniumCmd);
        $res = $this->waitForSeleniumOn($output);
        if (true !== $res) {
            $this->debugSeleniumNotStarted($input, $output);
            throw new \RuntimeException('Selenium hasn\'t started successfully.');
        }
        if ($input->getOption('verbose')) {
            $this->tailSeleniumLog();
        }
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $seleniumLocation
     * @return string
     */
    private function getSeleniumStartCommand(InputInterface $input, OutputInterface $output, $seleniumLocation)
    {
        $cmd = 'java -jar '.$seleniumLocation;
        if ($input->getOption('xvfb')) {
            $xvfbCmd = 'DISPLAY=:1 /usr/bin/xvfb-run --auto-servernum --server-num=1';
            $cmd = $xvfbCmd.' '.$cmd;
        }
        if ($input->getOption('firefox-profile')) {
            $cmd .= ' -firefoxProfileTemplate '.$input->getOption('firefox-profile');
        }

        return $cmd.' > selenium.log 2> selenium.log &';
    }
    
    /**
     *
     * @return string
     */
    private function getSeleniumHostDriverURL()
    {
        return 'http://localhost:'.$this->port.'/selenium-server/driver/';
    }

    /**
     * Will wait until the selenium is started or timeout
     *
     * @param OutputInterface $output
     * @return boolean true if selenium is successfully started, false otherwise
     */
    private function waitForSeleniumOn(OutputInterface $output)
    {
        return $this->waitForCurlToReturn(
            true,
            $output,
            $this->getSeleniumHostDriverURL().'?cmd=getLogMessages',
            $this->seleniumStartTimeout,
            $this->seleniumStartWaitInterval
        );
    }

    /**
     *
     * @param boolean $expectedReturnStatus
     * @param OutputInterface $output
     * @param string $url
     * @param int $timeout
     * @param int $waitInterval
     * @return boolean whether if the expectedReturn was successful or not
     */
    private function waitForCurlToReturn($expectedReturnStatus, OutputInterface $output, $url, $timeout, $waitInterval)
    {
        $timeLeft = $timeout;
        $progress = new ProgressBar($output, $timeout);
        $progress->start();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        while (true) {
            $status = curl_exec($ch);
            if (false !== $status && $expectedReturnStatus !== false
             || $status === $expectedReturnStatus
            ) {
                break;
            }
            $timeLeft -= $waitInterval;
            if ($timeout < 0) {
                $output->writeln('Timeout to curl: '.$url);
                
                return false;
            }
            usleep($waitInterval);
            $progress->setProgress($timeout - $timeLeft);
        }
        $progress->finish();
        curl_close($ch);

        return true;
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     */
    private function debugSeleniumNotStarted(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('xvfb')) {
            $seleniumLog = file_get_contents('selenium.log');
            $matches = array();
            preg_match('/usr\/bin\/xvfb-run: not found/', $seleniumLog, $matches);
            if (count($matches)) {
                $output->writeln('xvfb-run: not found');
            }
        }
    }

    /**
     *
     * @param string $cmd
     * @param boolean $tolerate whether to throw exception on failure or not
     */
    private function runCmdToStdOut($cmd, $tolerate = false)
    {
        $process = new Process($cmd);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if ($tolerate === false && !$process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'An error occurred when executing the "%s" command.',
                escapeshellarg($cmd)
            ));
        }
    }
}
