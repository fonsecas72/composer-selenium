<?php

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use BeubiQA\Application\Selenium\SeleniumHandler;

class ShowSeleniumCommand extends Command
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
        ->setName('show')
        ->setDescription('Displays selenium server log (tails the log file)')
        ->addOption(
            'log-location',
            null,
            InputOption::VALUE_REQUIRED,
            'Set the location of the selenium.log file',
            'selenium.log'
        )
        ->addOption(
            'follow',
            'f',
            InputOption::VALUE_OPTIONAL,
            'Follow selenium log. You may choose a specific level to follow. E.g. --follow ERROR ',
            'INFO'
        );
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
        $options['follow']              = $input->getOption('follow');
        $options['log-location']        = $input->getOption('log-location');

        $output->writeln('Displaying '.$options['log-location'].' file:'.PHP_EOL);
        $this->seleniumHandler->watch($options);
    }
}
