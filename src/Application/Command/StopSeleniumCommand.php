<?php

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use BeubiQA\Application\Selenium\SeleniumHandler;

class StopSeleniumCommand extends Command
{
    /** @var SeleniumHandler  */
    protected $seleniumHandler;

    /**
     *
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
        ->setName('stop')
        ->setDescription('Stops selenium server')
        ->addOption(
            'timeout',
            't',
            InputOption::VALUE_REQUIRED,
            'Set how much you are willing to wait until selenium server is stopped (in seconds)',
            30
        )
        ->addOption(
            'port',
            'p',
            InputOption::VALUE_REQUIRED,
            'Set how much you are willing to wait until selenium server is stopped (in seconds)',
            4444
        );
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = [];
        $options['timeout'] = $input->getOption('timeout');
        $options['port'] = $input->getOption('port');
        $this->seleniumHandler->stop($options);
        $output->writeln("\nDone");
    }
}
