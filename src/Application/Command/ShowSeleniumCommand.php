<?php

namespace BeubiQA\Application\Command;

use BeubiQA\Application\Command\SeleniumCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class ShowSeleniumCommand extends SeleniumCommand
{
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
        $this->verifyLogFileWritable();
        $output->writeln('Displaying '.$this->seleniumLogFile.' file:'.PHP_EOL);
        $this->followFileContent($this->seleniumLogFile, $input->getOption('follow'));
    }
}
