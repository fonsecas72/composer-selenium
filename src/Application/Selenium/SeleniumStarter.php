<?php

namespace BeubiQA\Application\Selenium;

use BeubiQA\Application\Lib\ResponseWaitter;
use BeubiQA\Application\Selenium\Options\SeleniumStartOptions;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

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
     * @param Process          $process
     * @param ResponseWaitter  $responseWaitter
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
        $seleniumUrl = $this->seleniumOptions->getSeleniumUrl();
        $seleniumQuery = $this->seleniumOptions->getSeleniumQuery();
        $seleniumJarLocation = $this->seleniumOptions->getSeleniumJarLocation();
        if (!$seleniumUrl || !$seleniumQuery || !$seleniumJarLocation) {
            throw new \LogicException('Url, Query and Selenium Jar Location is mandatory and Jar Location should point to a .jar file.');
        }
        if (!is_file($seleniumJarLocation)) {
            throw new \RuntimeException('Selenium jar is not a file');
        }
        if (!is_readable($seleniumJarLocation)) {
            throw new \RuntimeException('Selenium jar not readable - '.$seleniumJarLocation);
        }
        if ($this->isSeleniumAvailable()) {
            throw new \RuntimeException('Selenium was already started');
        }
        $this->setStartCommand($this->createStartCommand());
        $this->process->setCommandLine($this->getStartCommand());
        $this->process->start();
        $this->responseWaitter->waitUntilAvailable($seleniumUrl, $seleniumQuery);
    }

    public function isSeleniumAvailable()
    {
        return $this->responseWaitter->isAvailable($this->seleniumOptions->getSeleniumUrl(), $this->seleniumOptions->getSeleniumQuery());
    }

    /**
     * @param array $options
     *
     * @return string
     */
    private function createStartCommand()
    {
        $cmd = $this->exeFinder->find('java').' -jar '.$this->seleniumOptions->getSeleniumJarLocation();
        if ($this->seleniumOptions->isXvfbEnabled()) {
            $xvfbCmd = 'DISPLAY=:1 '.$this->exeFinder->find('xvfb-run').' --auto-servernum --server-num=1';
            $cmd = $xvfbCmd.' '.$cmd;
        }
        $seleniumExtraArgs = $this->seleniumOptions->getSeleniumExtraArguments();
        if ($seleniumExtraArgs) {
            foreach ($seleniumExtraArgs as $optionName => $optionValue) {
                $cmd .= ' -'.$optionName.' '.$optionValue;
            }
        }
        $seleniumPort = $this->seleniumOptions->getSeleniumPort();
        if ($seleniumPort) {
            $cmd .= ' -port '.$seleniumPort;
        }
        $logLocation = $this->seleniumOptions->getSeleniumLogLocation();
        if ($logLocation) {
            $cmd .= ' > '.$logLocation.' 2> '.$logLocation;
        }

        return $cmd;
    }

    /**
     * @return SeleniumStartOptions
     */
    public function getStartOptions()
    {
        return $this->seleniumOptions;
    }
    /**
     * @return ResponseWaitter
     */
    public function getResponseWaitter()
    {
        return $this->responseWaitter;
    }
}
