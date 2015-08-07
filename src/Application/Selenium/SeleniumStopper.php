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
        $this->sendShutdownCmd($this->seleniumOptions->getSeleniumPort());
        $this->responseWaitter->waitUntilNotAvailable(
            $this->seleniumOptions->getSeleniumUrl(),
            $this->seleniumOptions->getSeleniumQuery()
        );
    }

    private function sendShutdownCmd()
    {
        try {
            $this->httpClient->get(
                $this->seleniumOptions->getSeleniumStopUrl(),
                $this->seleniumOptions->getSeleniumStopOptions()
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
