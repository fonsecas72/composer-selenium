<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BeubiQA\Application\Command;

use BeubiQA\Application\Command\SeleniumCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class StartSeleniumCommand extends SeleniumCommand
{
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
            'Set a custom firefox profile location directory'
        )
        ->addOption(
            'selenium-location',
            'l',
            InputOption::VALUE_REQUIRED,
            'Set a custom selenium jar location'
        )
        ->addOption(
            'xvfb',
            null,
            InputOption::VALUE_NONE,
            'Use xvfb to start selenium server'
        )
        ->setDescription('Starts selenium server');
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $seleniumLocation = $input->getOption('selenium-location') ?: '/opt/selenium/selenium-server-standalone.jar';
        if (!is_readable($seleniumLocation)) {
            throw new \RuntimeException('Selenium jar not found - '.$seleniumLocation);
        }
        $startSeleniumCmd = $this->getSeleniumStartCommand($input, $seleniumLocation);
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $output->write($startSeleniumCmd);
        }
        $this->runCmdToStdOut($startSeleniumCmd);
        $res = $this->waitForSeleniumOn($output);
        if (true !== $res) {
            $this->debugSeleniumNotStarted($input, $output);
            throw new \RuntimeException('Selenium hasn\'t started successfully.');
        }
        if ($input->getOption('verbose')) {
            $output->writeln(PHP_EOL);
            $this->runCmdToStdOut('tail -f selenium.log');
        }
        $output->writeln("\nDone");
    }
    
    /**
     *
     * @param InputInterface $input
     * @param string $seleniumLocation
     * @return string
     */
    private function getSeleniumStartCommand(InputInterface $input, $seleniumLocation)
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     */
    private function debugSeleniumNotStarted(InputInterface $input, OutputInterface $output)
    {
        $seleniumLog = file_get_contents('selenium.log');
        $matches = array();
        if ($input->getOption('xvfb')) {
            preg_match('/usr\/bin\/xvfb-run: not found/', $seleniumLog, $matches);
            if (count($matches)) {
                $output->writeln('xvfb-run: not found');
            }
        }
        if ($input->getOption('firefox-profile')) {
            preg_match('/Firefox profile template doesn\'t exist:/', $seleniumLog, $matches);
            if (count($matches)) {
                $output->writeln('Firefox profile template doesn\'t exist');
            }
        }
    }
}
