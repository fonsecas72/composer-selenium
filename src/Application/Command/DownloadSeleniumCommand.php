<?php

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use BeubiQA\Application\Selenium\SeleniumHandler;

class DownloadSeleniumCommand extends Command
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
        ->setName('get')
        ->addOption(
            'selenium-version',
            's',
            InputOption::VALUE_REQUIRED,
            'Set a custom selenium version'
        )
        ->addOption(
            'selenium-destination',
            'd',
            InputOption::VALUE_REQUIRED,
            'Set a custom selenium destination directory'
        )
        ->setDescription('Downloads selenium server');
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
        $options['selenium-destination']  = $input->getOption('selenium-destination') ?: './';
        $options['selenium-version']      = $input->getOption('selenium-version') ?: '2.44';
        $this->seleniumHandler->download($options);
        $output->writeln("\nDone");
    }
}
