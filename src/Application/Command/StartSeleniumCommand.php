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
        ->addOption(
            'timeout',
            't',
            InputOption::VALUE_REQUIRED,
            'Set how much you are willing to wait until selenium server is started (in seconds)',
            false
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
        $this->setSeleniumTimeout($input->getOption('timeout'));
        if (!is_readable($seleniumLocation)) {
            throw new \RuntimeException('Selenium jar not found - '.$seleniumLocation);
        }
        $startSeleniumCmd = $this->getSeleniumStartCommand($input, $seleniumLocation);
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $output->write($startSeleniumCmd);
        }
        exec($startSeleniumCmd);
        $res = $this->waitForSeleniumCurlToReturn(true, $output, 'getLogMessages');
        if (true !== $res) {
            $output->writeln(file_get_contents($this->seleniumLogFile));
            throw new \RuntimeException('Selenium hasn\'t started successfully.');
        }
        if ($input->getOption('verbose')) {
            $output->writeln(PHP_EOL);
            $this->followFileContent($this->seleniumLogFile);
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

        return $cmd.' > '.$this->seleniumLogFile.' 2> '.$this->seleniumLogFile.' &';
    }
}
