<?php

namespace BeubiQA\Application\Selenium;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Console\Helper\ProgressBar;

class SeleniumWaitter
{

    /** @var Client */
    protected $httpClient;

    /** @var ProgressBar */
    protected $progressBar;

    /**
     * @var integer
     */
    public $seleniumTimeout = 30000000;

    /**
     * @var integer
     */
    public $seleniumWaitInterval = 25000; // 0.025 seconds
    /**
     * @var string
     */
    public $seleniumLogFile = 'selenium.log';

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function setProgressBar(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }

    private function waitUntilAvailable($url, $timeLeft)
    {
        while (true) {
            $timeLeft = $this->updateTimeleft($timeLeft);
            try {
                $this->httpClient->get($url, ['query' => ['cmd' => 'getLogMessages']]);
            } catch (ConnectException $exc) {
                continue; // try again
            }
            break;
        }
        
        return $timeLeft;
    }
    
    private function waitUntilException($url, $timeLeft)
    {
        while (true) {
            $timeLeft = $this->updateTimeleft($timeLeft);
            try {
                $this->httpClient->get($url, ['query' => ['cmd' => 'getLogMessages']]);
            } catch (ConnectException $exc) {
                break;
            }
        }

        return $timeLeft;
    }

    public function waitForSeleniumStart($options)
    {
        $this->setSeleniumTimeout($options['timeout']);
        $url = 'http://localhost:'.$options['port'].'/selenium-server/driver/';
        $timeLeft = $this->waitUntilAvailable($url, $this->seleniumTimeout);
        $this->progressBarFinish();

        return $timeLeft;
    }

    public function waitForSeleniumStop($options)
    {
        $this->setSeleniumTimeout($options['timeout']);
        $url = 'http://localhost:'.$options['port'].'/selenium-server/driver/';
        $timeLeft = $this->waitUntilException($url, $this->seleniumTimeout);
        $this->progressBarFinish();
        
        return $timeLeft;
    }
    
    private function progressBarStart()
    {
        $this->progressBar ? $this->progressBar->start($this->seleniumTimeout) : '';
    }
    private function progressBarSetProgress($timeLeft)
    {
        $this->progressBar ? $this->progressBar->setProgress($this->seleniumTimeout - $timeLeft) : '';
    }
    private function progressBarFinish()
    {
        $this->progressBar ? $this->progressBar->finish() : '';
    }
    
    private function setSeleniumTimeout($userOption)
    {
        if ($userOption !== false) {
            $this->seleniumTimeout = (int) $userOption * 1000000;
        }
        $this->progressBarStart();
    }
    
    /**
     *
     * @param int $timeLeft
     * @return int timeleft
     */
    private function updateTimeleft($timeLeft)
    {
        $timeLeft -= $this->seleniumWaitInterval;
        if ($timeLeft < 0) {
            throw new \RuntimeException('Timeout!');
        }
        usleep($this->seleniumWaitInterval);
        $this->progressBarSetProgress($timeLeft);
        
        return $timeLeft;
    }
}
