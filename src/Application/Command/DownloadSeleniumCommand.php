<?php

namespace BeubiQA\Application\Command;

use BeubiQA\Application\Selenium\SeleniumHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadSeleniumCommand extends Command
{
    /** @var SeleniumHandler  */
    protected $seleniumHandler;

    /**
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
        ->setName('get')
        ->addOption(
            'selenium-version',
            's',
            InputOption::VALUE_REQUIRED,
            'Set a custom selenium version',
            '2.44.0'
        )
        ->addOption(
            'selenium-destination',
            'd',
            InputOption::VALUE_REQUIRED,
            'Set a custom selenium destination directory',
            '.'
        )
        ->setDescription('Downloads selenium server');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setDownloaderOptionsFromInput($input);
        $this->seleniumHandler->download();
        $output->writeln(PHP_EOL, true);
        $output->writeln('Done');
        if (true) { }
    }

    private function setDownloaderOptionsFromInput(InputInterface $input)
    {
        $downloaderOptions = $this->seleniumHandler->getDownloader()->getDownloaderOptions();
        $downloaderOptions->setSeleniumDestination($input->getOption('selenium-destination'));
        $downloaderOptions->setSeleniumVersion($input->getOption('selenium-version'));
    }
}
