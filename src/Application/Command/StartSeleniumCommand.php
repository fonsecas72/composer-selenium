<?php

namespace BeubiQA\Application\Command;

use BeubiQA\Application\Command\SeleniumCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StartSeleniumCommand extends SeleniumCommand
{
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
            'chrome-driver',
            null,
            InputOption::VALUE_REQUIRED,
            'Set the chrome-driver path'
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
            30
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
        $seleniumLocation = $input->getOption('selenium-location') ?: './selenium-server-standalone.jar';
        $this->verifyLogFileWritable();
        $this->setSeleniumTimeout($input->getOption('timeout'));
        if (!is_readable($seleniumLocation)) {
            throw new \RuntimeException('Selenium jar not found - '.$seleniumLocation);
        }
        $startSeleniumCmd = $this->getSeleniumStartCommand($input, $seleniumLocation);
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $output->write($startSeleniumCmd);
        }
        exec($startSeleniumCmd);
        $res = $this->waitForSeleniumState('on');
        if (false === $res) {
            $output->writeln(file_get_contents($this->seleniumLogFile));
            throw new \RuntimeException('Selenium hasn\'t started successfully.');
        }
        if ($input->hasOption('verbose') && $input->getOption('verbose')) {
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
            if (!is_dir($input->getOption('firefox-profile'))) {
                throw new \RuntimeException('The Firefox-profile you set is not available.');
            }
            $cmd .= ' -firefoxProfileTemplate '.$input->getOption('firefox-profile');
        }
        if ($input->getOption('chrome-driver')) {
            $cmd .= ' -Dwebdriver.chrome.driver='.$input->getOption('chrome-driver');
        }

        return $cmd.' > '.$this->seleniumLogFile.' 2> '.$this->seleniumLogFile.' &';
    }
}
