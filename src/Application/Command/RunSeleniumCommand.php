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
            'start|stop|get',
            'start'
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $root = __DIR__ . '/../../..';
        switch ($input->getArgument('action')) {
            case 'start':
                $seleniumLocation = $input->getOption('selenium-location') ?: '/opt/selenium/selenium-server-standalone.jar';
                if (!is_readable($seleniumLocation)) {
                    throw new \RuntimeException('selenium jar not found - '.$seleniumLocation);
                }
                $cmd = 'java -jar '.$seleniumLocation;
                if ($input->getOption('xvfb')) {
                    $xvfbCmd = 'DISPLAY=:1 /usr/bin/xvfb-run --auto-servernum --server-num=1';
                    $cmd = $xvfbCmd.' '.$cmd;
                }
                if ($input->getOption('firefox-profile')) {
                    $cmd = $cmd.' -firefoxProfileTemplate '.$input->getOption('firefox-profile');
                }
                $cmd = $cmd.' > selenium.log 2> selenium.log &';
                $this->runCmdToStdOut($cmd);
                
                $seleniumLog = file_get_contents($root.'/selenium.log');
                $matches = array();
                preg_match('/usr\/bin\/xvfb-run: not found/', $seleniumLog, $matches);
                if (count($matches)) {
                    throw new \RuntimeException('xvfb-run: not found');
                }
                
                sleep(3);
                if ($input->getOption('verbose')) {
                    $cmd = 'tail -f selenium.log';
                    $this->runCmdToStdOut($cmd);
                }
                break;
            case 'stop':
                $cURL = curl_init('http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer');
                curl_exec($cURL);
                curl_close($cURL);
                break;
            case 'get':
                $version = $input->getOption('selenium-version') ?: '2.44';
                $destination = $input->getOption('selenium-destination') ?: '/opt/selenium';
                $this->updateSelenium($input, $output, $version, $destination);
                break;
            default:
                throw new \RuntimeException('Invalid Argument');
        }
        $output->writeln('Done');
    }

    private function downloadFile(OutputInterface $output, $url, $outputFile)
    {
        
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "Content-type: application/force-download",
            )
        );

        $progress = new ProgressBar($output);
        $ctx = stream_context_create($opts, array('notification' => function ($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) use ($output, $progress) {
            switch ($notification_code) {
                case STREAM_NOTIFY_FILE_SIZE_IS:
                    $progress->start($bytes_max);
                    break;
                case STREAM_NOTIFY_PROGRESS:
                    $progress->setCurrent($bytes_transferred);
                    break;
            }
        }));
        file_put_contents($outputFile, file_get_contents($url, false, $ctx));
        $progress->finish();
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
        $url = 'http://selenium-release.storage.googleapis.com/' . $version . '/selenium-server-standalone-' . $version . '.0.jar';
        $this->downloadFile($output, $url, $outputFile);
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
