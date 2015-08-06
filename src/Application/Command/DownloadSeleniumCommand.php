<?php

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use BeubiQA\Application\Selenium\SeleniumDownloader;
use Symfony\Component\Console\Command\Command;

class DownloadSeleniumCommand extends Command
{
    /** @var SeleniumDownloader  */
    protected $seleniumDownloader;

    public function __construct(SeleniumDownloader $seleniumDownloader)
    {
        $this->seleniumDownloader = $seleniumDownloader;
        parent::__construct('donwload');
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
        $options['selenium-destination']  = $input->getOption('selenium-destination') ? : './';
        $options['selenium-version']      = $input->getOption('selenium-version') ? : '2.44';

        $this->seleniumDownloader->download($options);
        
        $output->writeln("\nDone");
    }
}
