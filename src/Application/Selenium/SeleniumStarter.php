<?php

namespace BeubiQA\Application\Selenium;

use Symfony\Component\Process\Process;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Console\Helper\ProgressBar;
use BeubiQA\Application\Selenium\SeleniumWaitter;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ExecutableFinder;

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
     * @param InputInterface $input
     * @param string $seleniumLocation
     * @param string $seleniumLogFile
     * @return string
     * @throws \RuntimeException
     */
    private function getStartCommand($options)
    {
        $cmd = $this->exeFinder->find('java').' -jar '.$options['selenium-location'];
        if ($options['xvfb']) {
            $xvfbCmd = 'DISPLAY=:1 '.$this->exeFinder->find('xvfb-run').' --auto-servernum --server-num=1';
            $cmd = $xvfbCmd.' '.$cmd;
        }
        if ($options['firefox-profile']) {
            if (!is_dir($options['firefox-profile'])) {
                throw new \RuntimeException('The Firefox-profile you set is not available.');
            }
            $cmd .= ' -firefoxProfileTemplate '.$options['firefox-profile'];
        }
        if ($options['chrome-driver']) {
            $cmd .= ' -Dwebdriver.chrome.driver='.$options['chrome-driver'];
        }

        return $cmd.' > '.$options['log-location'].' 2> '.$options['log-location'];
    }
}
