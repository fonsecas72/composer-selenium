<?php

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use BeubiQA\Application\Selenium\SeleniumStopper;
use Symfony\Component\Console\Command\Command;

class StopSeleniumCommand extends Command
{
    /**  @var SeleniumStopper  */
    protected $seleniumStopper;


    public function __construct(SeleniumStopper $seleniumStopper)
    {
        $this->seleniumStopper = $seleniumStopper;
        parent::__construct('stop');
    }

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


    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options['timeout'] = $input->getOption('timeout');
        $this->seleniumStopper->stop($options);
        $output->writeln("\nDone");
    }
}
