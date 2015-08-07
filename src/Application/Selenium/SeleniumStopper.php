<?php

namespace BeubiQA\Application\Selenium;

use BeubiQA\Application\Lib\ResponseWaitter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use BeubiQA\Application\Selenium\Options\SeleniumStopOptions;

class SeleniumStopper
{
    /** @var ResponseWaitter */
    private $responseWaitter;
    
    /** @var Client */
    private $httpClient;
    
    /** @var SeleniumStopOptions */
    private $seleniumOptions;

    public function __construct(SeleniumStopOptions $seleniumOptions , ResponseWaitter $responseWaitter, Client $httpClient)
    {
        $this->seleniumOptions = $seleniumOptions;
        $this->responseWaitter = $responseWaitter;
        $this->httpClient = $httpClient;
    }

    public function stop()
    {
        $seleniumPort = $this->seleniumOptions->getSeleniumPort();
        $seleniumUrl = $this->seleniumOptions->getSeleniumUrl();
        $seleniumQuery = $this->seleniumOptions->getSeleniumQuery();
        $seleniumShutdownUrl = $this->seleniumOptions->getSeleniumShutdownUrl();
        $seleniumShutdownOptions = $this->seleniumOptions->getSeleniumShutDownOptions();
        
        if (!$seleniumShutdownOptions || !$seleniumShutdownUrl || !$seleniumPort || !$seleniumUrl || !$seleniumQuery) {
            throw new \LogicException('Port, Url, Shutdown Url, Shutdown Options, and Query are mandatory.');
        }
        
        $this->sendShutdownCmd($this->seleniumOptions->getSeleniumPort());
        $this->responseWaitter->waitUntilNotAvailable($seleniumUrl, $seleniumQuery);
    }

    private function sendShutdownCmd()
    {
        try {
            $this->httpClient->get(
                $this->seleniumOptions->getSeleniumShutdownUrl(),
                $this->seleniumOptions->getSeleniumShutDownOptions()
            );
        } catch (ConnectException $exc) {
            throw new \RuntimeException($exc->getMessage().PHP_EOL.'Probably selenium is already stopped.');
        }
    }
    
    /**
     *
     * @return SeleniumStopOptions
     */
    public function getStopOptions()
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
