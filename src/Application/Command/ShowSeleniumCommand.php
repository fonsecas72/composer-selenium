<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ShowSeleniumCommand extends Command
{

    /**
     * Command configuration
     *
     * @return void
     */
    protected function configure()
    {
        $this
        ->setName('show')
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
        $this->handleShow($input, $output);
        $output->writeln("\nDone");
    }

    public function handleShow(InputInterface $input, OutputInterface $output)
    {
        $this->tailSeleniumLog();
    }

    private function tailSeleniumLog()
    {
        $this->runCmdToStdOut('tail -f selenium.log');
    }

    /**
     *
     * @param string $cmd
     * @param boolean $tolerate whether to throw exception on failure or not
     */
    private function runCmdToStdOut($cmd, $tolerate = false)
    {
        $process = new Process($cmd);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if ($tolerate === false && !$process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'An error occurred when executing the "%s" command.',
                escapeshellarg($cmd)
            ));
        }
    }
}
