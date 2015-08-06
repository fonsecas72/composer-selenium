<?php

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use BeubiQA\Application\Selenium\SeleniumCommandGetter;
use BeubiQA\Application\Selenium\SeleniumStarter;
use BeubiQA\Application\Selenium\SeleniumLogWatcher;
use Symfony\Component\Console\Command\Command;

class StartSeleniumCommand extends Command
{
    /** @var SeleniumCommandGetter  */
    protected $getSeleniumCommand;
    
    /** @var SeleniumStarter  */
    protected $seleniumStarter;

    /** @var SeleniumLogWatcher  */
    protected $seleniumLogWatcher;

    public function __construct(SeleniumStarter $seleniumStarter, SeleniumLogWatcher $seleniumLogWatcher, SeleniumCommandGetter $getSeleniumCommand)
    {
        $this->getSeleniumCommand = $getSeleniumCommand;
        $this->seleniumStarter = $seleniumStarter;
        $this->seleniumLogWatcher = $seleniumLogWatcher;
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
        $options['firefox-profile']     = $input->getOption('firefox-profile');
        $options['chrome-driver']       = $input->getOption('chrome-driver');
        $options['selenium-location']   = $input->getOption('selenium-location') ? : './selenium-server-standalone.jar';
        $options['xvfb']                = $input->getOption('xvfb');
        $options['follow']              = $input->getOption('follow');
        $options['timeout']             = $input->getOption('timeout');
        $options['log-location']        = $input->getOption('log-location');
        $options['verbose_level']       = $output->getVerbosity();
        
        $this->seleniumStarter->start($options);

        if ($options['verbose_level'] >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $output->write($this->getSeleniumCommand->getStartCommand($options));
        }

        if ($options['follow']) {
            $output->writeln(PHP_EOL);
            $this->seleniumLogWatcher->followFileContent($options['log-location'], $options['follow']);
        }
        $output->writeln("\nDone");
    }
}
