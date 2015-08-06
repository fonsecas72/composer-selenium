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

    /**
     *
     * @param Client $httpClient
     */
    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     *
     * @param ProgressBar $progressBar
     */
    public function setProgressBar(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }

    /**
     *
     * @param string $url
     * @param int $timeLeft
     * @return int
     */
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
    
    /**
     *
     * @param string $url
     * @param int $timeLeft
     * @return int
     */
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

    /**
     *
     * @param array $options
     * @return int
     */
    public function waitForSeleniumStart($options)
    {
        $this->setSeleniumTimeout($options['timeout']);
        $timeLeft = $this->waitUntilAvailable($this->getSeleniumUrl($options['port']), $this->seleniumTimeout);
        $this->progressBarFinish();

        return $timeLeft;
    }

    /**
     *
     * @param int $port
     * @return string
     */
    private function getSeleniumUrl($port)
    {
        return 'http://localhost:'.$port.'/selenium-server/driver/';
    }
    
    /**
     *
     * @param array $options
     * @return int
     */
    public function waitForSeleniumStop($options)
    {
        $this->setSeleniumTimeout($options['timeout']);
        $timeLeft = $this->waitUntilException($this->getSeleniumUrl($options['port']), $this->seleniumTimeout);
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

    /**
     *
     * @param int $userOption
     */
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
