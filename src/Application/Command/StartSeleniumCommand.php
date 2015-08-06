<?php

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use BeubiQA\Application\Selenium\SeleniumCommandGetter;
use Symfony\Component\Console\Command\Command;
use BeubiQA\Application\Selenium\SeleniumHandler;

class StartSeleniumCommand extends Command
{
    /** @var SeleniumHandler  */
    protected $seleniumHandler;

    public function __construct(SeleniumHandler $seleniumHandler)
    {
        $this->seleniumHandler = $seleniumHandler;
        parent::__construct('start');
    }
    
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
            'follow',
            'f',
            InputOption::VALUE_OPTIONAL,
            'Follow selenium log. You may choose a specific level to follow. E.g. --follow ERROR ',
            false
        )
        ->addOption(
            'timeout',
            't',
            InputOption::VALUE_REQUIRED,
            'Set how much you are willing to wait until selenium server is started (in seconds)',
            30
        )
        ->addOption(
            'log-location',
            null,
            InputOption::VALUE_REQUIRED,
            'Set the location of the selenium.log file',
            'selenium.log'
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
        $options = [];
        $options['firefox-profile']     = $input->getOption('firefox-profile');
        $options['chrome-driver']       = $input->getOption('chrome-driver');
        $options['selenium-location']   = $input->getOption('selenium-location') ? : './selenium-server-standalone.jar';
        $options['xvfb']                = $input->getOption('xvfb');
        $options['follow']              = $input->getOption('follow');
        $options['timeout']             = $input->getOption('timeout');
        $options['log-location']        = $input->getOption('log-location');
        
        $startCmd = $this->seleniumHandler->start($options);

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $output->write($startCmd);
        }

        if ($options['follow']) {
            $output->writeln(PHP_EOL);
             $this->seleniumHandler->watch($options);
        }
        $output->writeln("\nDone");
    }
}
