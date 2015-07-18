<?php

namespace BeubiQA\Application\Command;

use BeubiQA\Application\Command\SeleniumCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use BeubiQA\Application\Selenium\GetSeleniumCommand;

class StartSeleniumCommand extends SeleniumCommand
{
    /** @var GetSeleniumCommand  */
    protected $getSeleniumCommand;

    public function __construct(GetSeleniumCommand $getSeleniumCommand)
    {
        $this->getSeleniumCommand = $getSeleniumCommand;
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
        $startSeleniumCmd = $this->getSeleniumCommand->getStartCommand($input, $seleniumLocation, $this->seleniumLogFile);
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
}
