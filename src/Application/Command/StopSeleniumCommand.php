<?php

namespace BeubiQA\Application\Command;

use BeubiQA\Application\Command\SeleniumCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StopSeleniumCommand extends SeleniumCommand
{
    protected function configure()
    {
        $this
        ->setName('stop')
        ->setDescription('Stops selenium server')
        ->addOption(
            'timeout',
            't',
            InputOption::VALUE_REQUIRED,
            'Set how much you are willing to wait until selenium server is stopped (in seconds)',
            30
        );
    }

    private function sendShutdownCmd()
    {
        try {
            $this->httpClient->get($this->getSeleniumHostDriverURL(), ['query' => ['cmd' => 'shutDownSeleniumServer']]);
        } catch (\Exception $exc) {
            // we don't need to do anything here
        }
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setSeleniumTimeout($input->getOption('timeout'));
        $this->sendShutdownCmd();
        $this->waitForSeleniumState('off');
        $output->writeln("\nDone");
    }

//    public function getName()
//    {
//        return 'stop';
//    }
}
