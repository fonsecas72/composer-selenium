<?php

namespace BeubiQA\Application\Lib;

use BeubiQA\Application\Exception\Timeout;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Console\Helper\ProgressBar;

class ResponseWaitter
{
    /** @var Client */
    private $httpClient;
    /** @var ProgressBar */
    private $progressBar;
    private $timeout = 30000000;
    private $waitInterval = 25000; // 0.025 seconds

    public function __construct(Client $httpClient, $timeoutSeconds = 30000000, $waitInterval = 25000)
    {
        $this->setTimeout($timeoutSeconds);
        $this->waitInterval = $waitInterval;
        $this->httpClient = $httpClient;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function setTimeout($timeoutSeconds)
    {
        $this->timeout = (int) $timeoutSeconds * 1000000;
    }

    public function setProgressBar(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }

    private function progressBarStart()
    {
        $this->progressBar ? $this->progressBar->start($this->timeout) : '';
    }

    private function progressBarSetProgress()
    {
        $this->progressBar ? $this->progressBar->setProgress($this->timeout - $this->timeLeft) : '';
    }

    private function progressBarFinish()
    {
        $this->progressBar ? $this->progressBar->finish() : '';
    }

    private function updateTimeleft()
    {
        $this->timeLeft -= $this->waitInterval;
        if ($this->timeLeft < 0) {
            throw new Timeout('Timeout of '.var_export($this->timeout, true).' seconds.');
        }
        usleep($this->waitInterval);
        $this->progressBarSetProgress();
    }

    public function isAvailable($url, $requestOptions)
    {
        try {
            $this->httpClient->get($url, $requestOptions);
        } catch (ConnectException $e) {
            return false;
        }

        return true;
    }

    public function waitUntilAvailable($url, $requestOptions)
    {
        $this->progressBarStart();
        $this->timeLeft = $this->timeout;
        while (true) {
            $this->updateTimeleft();
            if ($this->isAvailable($url, $requestOptions)) {
                break;
            }
        }
        $this->progressBarFinish();
    }

    public function waitUntilNotAvailable($url, $requestOptions)
    {
        $this->progressBarStart();
        $this->timeLeft = $this->timeout;
        while (true) {
            $this->updateTimeleft();
            if (!$this->isAvailable($url, $requestOptions)) {
                break;
            }
        }
        $this->progressBarFinish();
    }
}
