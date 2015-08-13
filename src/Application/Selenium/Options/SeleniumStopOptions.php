<?php

namespace BeubiQA\Application\Selenium\Options;

class SeleniumStopOptions extends SeleniumOptions
{
    protected $seleniumShutdownUrl = 'http://localhost:%d/selenium-server/driver/';
    protected $seleniumShutdownOptions = ['query' => ['cmd' => 'shutDownSeleniumServer']];

    public function setSeleniumStopUrl($url)
    {
        $this->seleniumShutdownUrl = $url;
    }
    public function getSeleniumShutdownUrl()
    {
        return sprintf($this->seleniumShutdownUrl, $this->getSeleniumPort());
    }

    public function setSeleniumStopOptions($options)
    {
        $this->seleniumShutdownOptions = $options;
    }
    public function getSeleniumShutDownOptions()
    {
        return $this->seleniumShutdownOptions;
    }
}
