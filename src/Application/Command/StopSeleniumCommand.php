<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BeubiQA\Application\Command;

use BeubiQA\Application\Command\SeleniumCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StopSeleniumCommand extends SeleniumCommand
{
    /**
     * Command configuration
     *
     * @return void
     */
    protected function configure()
    {
        $this
        ->setName('stop')
        ->setDescription('This will start/stop Selenium2 server.');
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->waitForCurlToReturn(
            false,
            $output,
            $this->getSeleniumHostDriverURL().'?cmd=shutDownSeleniumServer',
            $this->seleniumStartTimeout,
            $this->seleniumStartWaitInterval
        );
        $output->writeln("\nDone");
    }
}
