<?php

namespace BeubiQA\Application\Selenium;

use BeubiQA\Application\Lib\ResponseWaitter;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use BeubiQA\Application\Selenium\Options\SeleniumStartOptions;

class SeleniumStarter
{
    /** @var Process */
    private $process;
    /** @var ResponseWaitter */
    private $responseWaitter;
    /** @var ExecutableFinder */
    private $exeFinder;
    /**  @var SeleniumStartOptions */
    private $seleniumOptions;
    /**  @var string */
    private $startCommand;
    
    /**
     *
     * @param Process $process
     * @param ResponseWaitter $responseWaitter
     * @param ExecutableFinder $exeFinder
     */
    public function __construct(SeleniumStartOptions $seleniumOptions, Process $process, ResponseWaitter $responseWaitter, ExecutableFinder $exeFinder)
    {
        $this->seleniumOptions = $seleniumOptions;
        $this->process = $process;
        $this->responseWaitter = $responseWaitter;
        $this->exeFinder = $exeFinder;
    }
    private function setStartCommand($command)
    {
        $this->startCommand = $command;
    }
    public function getStartCommand()
    {
        return $this->startCommand;
    }
    
    public function start()
    {
        if (!is_readable($this->seleniumOptions->getSeleniumJarLocation())) {
            throw new \RuntimeException('Selenium jar not readable - '.$this->seleniumOptions->getSeleniumJarLocation());
        }

        $this->setStartCommand($this->createStartCommand());
        $this->process->setCommandLine($this->getStartCommand());
        $this->process->start();
        $this->responseWaitter->waitUntilAvailable(
            $this->seleniumOptions->getSeleniumUrl(),
            $this->seleniumOptions->getSeleniumQuery()
        );
    }

    /**
     *
     * @param array $options
     * @return string
     */
    private function createStartCommand()
    {
        $cmd = $this->exeFinder->find('java').' -jar '.$this->seleniumOptions->getSeleniumJarLocation();
        if ($this->seleniumOptions->isXvfbEnabled()) {
            $xvfbCmd = 'DISPLAY=:1 '.$this->exeFinder->find('xvfb-run').' --auto-servernum --server-num=1';
            $cmd = $xvfbCmd.' '.$cmd;
        }

        if (!empty($this->seleniumOptions->getSeleniumExtraArguments())) {
            foreach ($this->seleniumOptions->getSeleniumExtraArguments() as $optionName => $optionValue) {
                $cmd .= ' -'.$optionName.' '.$optionValue;
            }
        }
        
        return $cmd.' > '.$this->seleniumOptions->getSeleniumLogLocation().' 2> '.$this->seleniumOptions->getSeleniumLogLocation();
    }
    
    /**
     *
     * @return SeleniumStartOptions
     */
    public function getStartOptions()
    {
        return $this->seleniumOptions;
    }
    /**
     *
     * @return ResponseWaitter
     */
    public function getResponseWaitter()
    {
        return $this->responseWaitter;
    }
}
