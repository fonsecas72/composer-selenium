<?php

namespace BeubiQA\Application\Selenium;

use BeubiQA\Application\Selenium\SeleniumWaitter;
use GuzzleHttp\Client;

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
        $this->sendShutdownCmd();
        $this->seleniumWaitter->waitForSeleniumStop($options);
    }
    
    private function sendShutdownCmd()
    {
        try {
            $this->httpClient->get($this->getSeleniumHostDriverURL(), ['query' => ['cmd' => 'shutDownSeleniumServer']]);
        } catch (\Exception $exc) {
            // we don't need to do anything here TODO: see if there is another way
        }
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
