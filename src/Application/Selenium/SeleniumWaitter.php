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

    public function waitForSeleniumStart($options)
    {
        $this->setSeleniumTimeout($options['timeout']);
        $this->progressBar ? $this->progressBar->start($this->seleniumTimeout) : '';
        $timeLeft = $this->seleniumTimeout;
        while (true) {
            $timeLeft = $this->updateTimeleft($timeLeft);
            $this->progressBar ? $this->progressBar->setProgress($this->seleniumTimeout - $timeLeft) : '';
            try {
                $this->httpClient->get($this->getSeleniumHostDriverURL(), ['query' => ['cmd' => 'getLogMessages']]);
            } catch (ConnectException $exc) {
                continue; // try again
            }
            break;
        }
        $this->progressBar ? $this->progressBar->finish() : '';
        return $timeLeft;
    }

    public function waitForSeleniumStop($options)
    {
        $this->setSeleniumTimeout($options['timeout']);
        $this->progressBar ? $this->progressBar->start($this->seleniumTimeout) : '';
        $timeLeft = $this->seleniumTimeout;
        while (true) {
            $timeLeft = $this->updateTimeleft($timeLeft);
            $this->progressBar ? $this->progressBar->setProgress($this->seleniumTimeout - $timeLeft) : '';
            try {
                $this->httpClient->get($this->getSeleniumHostDriverURL(), ['query' => ['cmd' => 'getLogMessages']]);
            } catch (ConnectException $exc) {
                break;
            }
        }
        $this->progressBar ? $this->progressBar->finish() : '';
        return $timeLeft;
    }

    private function setSeleniumTimeout($userOption)
    {
        if ($userOption !== false) {
            $this->seleniumTimeout = (int) $userOption * 1000000;
        }
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

        return $timeLeft;
    }

    /**
     *
     * @return string
     */
    private function getSeleniumHostDriverURL()
    {
        return 'http://localhost:4444/selenium-server/driver/';
    }
}
