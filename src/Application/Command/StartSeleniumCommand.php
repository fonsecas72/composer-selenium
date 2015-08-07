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

    /**
     *
     * @param SeleniumHandler $seleniumHandler
     */
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
            'selenium-location',
            'l',
            InputOption::VALUE_REQUIRED,
            'Set a custom selenium jar location',
            './selenium-server-standalone.jar'
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
        ->addOption(
            'selextra',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Extra options to selenium cmd. You may give multiple options. E.g.: firefoxProfileTemplate=/profileDir',
            []
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
        $starter = $this->seleniumHandler->getStarter();
        $this->setStarterOptionsFromInput($input);
        $starter->start();
        $output->writeln(PHP_EOL, true);
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $output->writeln($starter->getStartCommand(), true);
        }
        $output->writeln('Done', true);
    }

    private function setStarterOptionsFromInput(InputInterface $input)
    {
        $starter = $this->seleniumHandler->getStarter();
        $starter->getResponseWaitter()->setTimeout($input->getOption('timeout'));
        $starterOptions = $starter->getStartOptions();
        $starterOptions->setSeleniumLogLocation($input->getOption('log-location'));
        $starterOptions->setSeleniumJarLocation($input->getOption('selenium-location'));
        if ($input->getOption('xvfb')) {
            $starterOptions->enabledXvfb();
        }
        $this->processSeleniumExtraArguments($input->getOption('selextra'));
    }
    private function processSeleniumExtraArguments(array $cmdExtraArgs)
    {
        $starterOptions = $this->seleniumHandler->getStarter()->getStartOptions();
        $extraArgs = [];
        foreach ($cmdExtraArgs as $cmdExtraArgString) {
            $resultArray = explode('=', $cmdExtraArgString);
            $argName = $resultArray[0];
            $argValue = $resultArray[1];
            $extraArgs[$argName] = $argValue;
        }
        $starterOptions->setSeleniumExtraArguments($extraArgs);
    }
}
