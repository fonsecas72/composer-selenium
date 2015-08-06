<?php

namespace BeubiQA\Application\Selenium;

use BeubiQA\Application\Selenium\SeleniumWaitter;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class SeleniumStarter
{
    /** @var Process */
    protected $process;
    /** @var SeleniumWaitter */
    protected $seleniumWaitter;
    /** @var ExecutableFinder */
    protected $exeFinder;

    public function __construct(Process $process, SeleniumWaitter $seleniumWaitter, ExecutableFinder $exeFinder)
    {
        $this->process = $process;
        $this->seleniumWaitter = $seleniumWaitter;
        $this->exeFinder = $exeFinder;
    }

    public function start($options)
    {
        if (!is_readable($options['selenium-location'])) {
            throw new \RuntimeException('Selenium jar not readable - '.$options['selenium-location']);
        }
        $startSeleniumCmd = $this->getStartCommand($options);
        $this->process->setCommandLine($startSeleniumCmd);
        $this->process->start();
        $this->seleniumWaitter->waitForSeleniumStart($options);
        
        return $startSeleniumCmd;
    }
    
    /**
     *
     * @return string
     */
    private function getStartCommand($options)
    {
        $cmd = $this->exeFinder->find('java').' -jar '.$options['selenium-location'];
        if ($options['xvfb']) {
            $xvfbCmd = 'DISPLAY=:1 '.$this->exeFinder->find('xvfb-run').' --auto-servernum --server-num=1';
            $cmd = $xvfbCmd.' '.$cmd;
        }

        if (!empty($options['selenium-extra-options'])) {
            foreach ($options['selenium-extra-options'] as $optionName => $optionValue) {
                $cmd .= ' -'.$optionName.' '.$optionValue;
            }
        }
        
        return $cmd.' > '.$options['log-location'].' 2> '.$options['log-location'];
    }
}
