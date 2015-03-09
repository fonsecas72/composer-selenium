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
        ->setDescription('This will start/stop Selenium2 server.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $root = __DIR__ . '/../../..';
        $action = $input->getArgument('action');
        $verboseCmd = !$input->getOption('verbose') ? '' : ' -v ';
        $firefoxProfCmd = !$input->getOption('firefox-profile') ? '' : ' -p '.$input->getOption('firefox-profile');

        switch ($action) {
            case 'start':
                $cmd = $root.'/src/Scripts/selenium.sh -a start '.$verboseCmd.$firefoxProfCmd;
                $this->runCmdToStdOut($cmd);
                break;
            case 'stop':
                $cmd = $root.'/src/Scripts/selenium.sh -a stop '.$verboseCmd;
                $this->runCmdToStdOut($cmd);
                break;
            case 'get':
                $version = $input->getOption('selenium-version') ?: '2.44';
                $destination = $input->getOption('selenium-destination') ?: __DIR__ . '/../../../bin';
                $this->updateSelenium($input, $output, $version, $destination);
                break;
            default:
                throw new \RuntimeException('Invalid Argument');
        }
    }

    private function downloadFile(OutputInterface $output, $url, $outputFile)
    {
        $progress = new ProgressBar($output);
        $ctx = stream_context_create(array(), array('notification' => function ($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) use ($output, $progress) {
            switch ($notification_code) {
                case STREAM_NOTIFY_FILE_SIZE_IS:
                    $progress->start($bytes_max);
                    break;
                case STREAM_NOTIFY_PROGRESS:
                    $progress->setCurrent($bytes_transferred);
                    break;
            }
        }));
        $file = file_get_contents($url, false, $ctx);
        file_put_contents($outputFile, $file);
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

    private function runCmdToStdOut($cmd)
    {
        $process = new Process($cmd);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'An error occurred when executing the "%s" command.',
                escapeshellarg($cmd)
            ));
        }
    }
}
