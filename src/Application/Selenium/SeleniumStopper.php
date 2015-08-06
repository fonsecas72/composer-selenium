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

    /**
     *
     * @param SeleniumWaitter $seleniumWaitter
     * @param Client $httpClient
     */
    public function __construct(SeleniumWaitter $seleniumWaitter, Client $httpClient)
    {
        $this->seleniumWaitter = $seleniumWaitter;
        $this->httpClient = $httpClient;
    }

    /**
     *
     * @param array $options
     */
    public function stop($options)
    {
        $this->sendShutdownCmd($options['port']);
        $this->seleniumWaitter->waitForSeleniumStop($options);
    }

    /**
     *
     * @param int $port
     * @throws \RuntimeException
     */
    private function sendShutdownCmd($port)
    {
        $url = 'http://localhost:'.$port.'/selenium-server/driver/';
        try {
            $this->httpClient->get($url, ['exceptions' => false, 'query' => ['cmd' => 'shutDownSeleniumServer']]);
        } catch (ConnectException $exc) {
            throw new \RuntimeException($exc->getMessage().PHP_EOL.'Probably selenium is already stopped.');
        }
    }
}
