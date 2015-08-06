<?php

namespace BeubiQA\Application\Selenium;

use Symfony\Component\Process\Process;
use BeubiQA\Application\Selenium\SeleniumCommandGetter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Console\Helper\ProgressBar;
use BeubiQA\Application\Selenium\SeleniumWaitter;
use Symfony\Component\Console\Output\OutputInterface;

class SeleniumStarter
{
    /** @var Process */
    protected $process;

    /** @var SeleniumCommandGetter  */
    
    protected $getSeleniumCommand;
    
    /** @var SeleniumWaitter */
    protected $seleniumWaitter;

    public function __construct(Process $process, SeleniumCommandGetter $getSeleniumCommand, SeleniumWaitter $seleniumWaitter)
    {
        $this->process = $process;
        $this->getSeleniumCommand = $getSeleniumCommand;
        $this->seleniumWaitter = $seleniumWaitter;
    }

    public function start($options)
    {
        if (!is_readable($options['selenium-location'])) {
            throw new \RuntimeException('Selenium jar not readable - '.$options['selenium-location']);
        }
        $startSeleniumCmd = $this->getSeleniumCommand->getStartCommand($options);
        $this->process->setCommandLine($startSeleniumCmd);
        $this->process->start();
        $this->seleniumWaitter->waitForSeleniumStart($options);
        
        return $startSeleniumCmd;
    }
}
