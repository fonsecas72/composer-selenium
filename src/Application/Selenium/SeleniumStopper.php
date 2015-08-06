<?php

namespace BeubiQA\Application\Selenium;

use BeubiQA\Application\Selenium\SeleniumWaitter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class SeleniumStopper
{
    /** @var SeleniumWaitter */
    protected $seleniumWaitter;
    
    /** @var Client */
    protected $httpClient;

    public function __construct(SeleniumWaitter $seleniumWaitter, Client $httpClient)
    {
        $this->seleniumWaitter = $seleniumWaitter;
        $this->httpClient = $httpClient;
    }

    public function stop($options)
    {
        $this->sendShutdownCmd($options['port']);
        $this->seleniumWaitter->waitForSeleniumStop($options);
    }
    
    private function sendShutdownCmd($port)
    {
        $url = 'http://localhost:'.$port.'/selenium-server/driver/';
        try {
            $this->httpClient->get($url, ['exceptions' => false,'query' => ['cmd' => 'shutDownSeleniumServer']]);
        } catch (ConnectException $exc) {
            throw new \RuntimeException($exc->getMessage().PHP_EOL.'Probably selenium is already stopped.');
        }
    }
}
