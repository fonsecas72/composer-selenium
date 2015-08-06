<?php

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use BeubiQA\Application\Selenium\SeleniumLogWatcher;
use Symfony\Component\Console\Command\Command;

class ShowSeleniumCommand extends Command
{
    /** @var SeleniumLogWatcher  */
    protected $seleniumLogWatcher;

    public function __construct(SeleniumLogWatcher $seleniumLogWatcher)
    {
        $this->seleniumLogWatcher = $seleniumLogWatcher;
        parent::__construct('show');
    }

    protected function configure()
    {
        $this
        ->setName('show')
        ->setDescription('Displays selenium server log (tails the log file)')
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
        $output->writeln('Displaying '.$this->seleniumLogFile.' file:'.PHP_EOL);
        $this->seleniumLogWatcher->followFileContent($this->seleniumLogFile, $input->getOption('follow'));
    }
}
