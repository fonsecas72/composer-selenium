<?php

namespace BeubiQA\Application\Selenium\Options;

class SeleniumStopOptions extends SeleniumOptions
{
    protected $seleniumStopUrl = 'http://localhost:%d/selenium-server/driver/';
    protected $seleniumStopOptions = ['query' => ['cmd' => 'shutDownSeleniumServer']];
        
    public function setSeleniumStopUrl($url)
    {
        $this->seleniumStopUrl = $url;
    }
    public function getSeleniumStopUrl()
    {
        return sprintf($this->seleniumStopUrl, $this->getSeleniumPort());
    }

    public function setSeleniumStopOptions($options)
    {
        $this->seleniumStopOptions = $options;
    }
    public function getSeleniumStopOptions()
    {
        return $this->seleniumStopOptions;
    }
}
