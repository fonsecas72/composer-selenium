<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;

class RunSeleniumCommand extends Command
{

    /**
     * Command configuration
     *
     * @return void
     */
    protected function configure()
    {
        $this
        ->setName('selenium')
        ->addArgument(
            'action',
            InputArgument::OPTIONAL,
            'start|stop|get|show',
            null
        )
        ->addOption(
            'firefox-profile',
            'p',
            InputOption::VALUE_REQUIRED,
            'Give a custom firefox profile location'
        )
        ->addOption(
            'selenium-version',
            's',
            InputOption::VALUE_REQUIRED,
            '(get only) Set a custom selenium version'
        )
        ->addOption(
            'selenium-destination',
            'd',
            InputOption::VALUE_REQUIRED,
            '(get only) Set a custom selenium destination'
        )
        ->addOption(
            'selenium-location',
            'l',
            InputOption::VALUE_REQUIRED,
            '(start only) Set a custom selenium location'
        )
        ->addOption(
            'xvfb',
            null,
            InputOption::VALUE_REQUIRED,
            '(start only) Use xvfb to start selenium'
        )
        ->setDescription('This will start/stop Selenium2 server.');
    }

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

    public $seleniumStartTimeout = 5000000; // 5 seconds
    public $seleniumStartWaitInterval = 25000; // 0.025 seconds
    public $port = 4444;
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
            $progress->setCurrent($timeout - $timeLeft);
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

    public function handleGet(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getOption('selenium-version') ?: '2.44';
        $destination = $input->getOption('selenium-destination') ?: '/opt/selenium';
        $this->updateSelenium($input, $output, $version, $destination);
    }

    public function handleStop(InputInterface $input, OutputInterface $output)
    {
        $this->waitForCurlToReturn(
            false,
            $output,
            $this->getSeleniumHostDriverURL().'?cmd=shutDownSeleniumServer',
            $this->seleniumStartTimeout,
            $this->seleniumStartWaitInterval
        );
    }

    public function handleShow(InputInterface $input, OutputInterface $output)
    {
        $this->tailSeleniumLog();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        switch ($input->getArgument('action')) {
            case 'get':
                $this->handleGet($input, $output);
                break;
            case 'start':
                $this->handleStart($input, $output);
                break;
            case 'stop':
                $this->handleStop($input, $output);
                break;
            case 'show':
                $this->handleShow($input, $output);
                break;
            case null:
                throw new \RuntimeException('Action must be start|stop|get|show');
            default:
                throw new \RuntimeException('Invalid Argument');
        }
        $output->writeln("\nDone");
    }

    private function tailSeleniumLog()
    {
        $this->runCmdToStdOut('tail -f selenium.log');
    }
    
    private function downloadFile(OutputInterface $output, $url, $outputFile)
    {
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "Content-type: application/force-download",
            )
        );
        $progress = new ProgressBar($output, 35000000); // ~ 35Mb
        $ctx = stream_context_create(
            $opts,
            array('notification' =>
                function ($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) use ($output, $progress) {
                    switch ($notification_code) {
                        case STREAM_NOTIFY_FILE_SIZE_IS:
                            $progress->start($bytes_max);
                            break;
                        case STREAM_NOTIFY_PROGRESS:
                            $progress->setCurrent($bytes_transferred);
                            break;
                    }
                }
            )
        );
        file_put_contents($outputFile, file_get_contents($url, false, $ctx));
        $progress->finish();
    }

    public function getSeleniumDownloadURL($version)
    {
        return 'http://selenium-release.storage.googleapis.com/'.$version.'/selenium-server-standalone-'.$version.'.0.jar';
    }
    
    private function updateSelenium(InputInterface $input, OutputInterface $output, $version, $destination)
    {
        if (!is_writable(dirname($destination))) {
            throw new \RuntimeException('Not enought permissions. Try with sudo.');
        }
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }
        $outputFile = $destination.'/selenium-server-standalone.jar';
        $this->downloadFile($output, $this->getSeleniumDownloadURL($version), $outputFile);
        $output->writeln('Done');

        if (!file_exists($outputFile)) {
            throw new \LogicException('Something wrong happent: ' . $outputFile);
        }
    }

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
