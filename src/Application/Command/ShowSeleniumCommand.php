<?php

namespace BeubiQA\Application\Command;

use BeubiQA\Application\Command\SeleniumCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowSeleniumCommand extends SeleniumCommand
{
    protected function configure()
    {
        $this
        ->setName('show')
        ->setDescription('Displays selenium server log (tails the log file)');
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (file_exists($this->seleniumLogFile) && !is_writable($this->seleniumLogFile)) {
            throw new \RuntimeException('No permissions in '.$this->seleniumLogFile);
        }
        $output->writeln('Displaying '.$this->seleniumLogFile.' file:'.PHP_EOL);
        $this->followFileContent($this->seleniumLogFile);
    }
}
