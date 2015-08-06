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
        $options = $this->getHandlerOptionsFromInput($input);
        $startCmd = $this->seleniumHandler->start($options);
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $output->write($startCmd);
        }
        if ($options['follow']) {
            $output->writeln(PHP_EOL);
            $this->seleniumHandler->watch($options);
        }
        $output->writeln(PHP_EOL.'Done');
    }


    /**
     *
     * @param InputInterface $input
     * @return array
     */
    private function getHandlerOptionsFromInput(InputInterface $input)
    {
        $options = [];
        $options['selenium-extra-options'] = [];
        $options['selenium-location']   = $input->getOption('selenium-location') ?: './selenium-server-standalone.jar';
        $options['xvfb']                = $input->getOption('xvfb');
        $options['follow']              = $input->getOption('follow');
        $options['timeout']             = $input->getOption('timeout');
        $options['log-location']        = $input->getOption('log-location');
        $options['port']                = 4444;

        foreach ($input->getOption('selextra') as $extraOption) {
            $explode = explode('=', $extraOption);
            $optionName = $explode[0];
            $value = $explode[1];
            if ($optionName == 'port') {
                $options['port'] = $value;
                continue;
            }
            $options['selenium-extra-options'][$optionName] = $value;
        }

        return $options;
    }
}
