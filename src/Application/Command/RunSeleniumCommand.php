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
            'start|stop',
            'start'
        )
        ->addOption(
            'firefox-profile',
            'fp',
            InputOption::VALUE_REQUIRED,
            'Give a custom firefox profile location'
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
            default:
                throw new \RuntimeException('Invalid Argument');
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
