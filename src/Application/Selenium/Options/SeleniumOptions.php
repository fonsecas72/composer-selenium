<?php

namespace BeubiQA\Application\Selenium\Options;


class SeleniumOptions
{
    protected $seleniumJarLocation = './selenium-server-standalone.jar';
    protected $seleniumLogLocation = 'selenium.log';
    protected $seleniumPort = 4444;
    protected $seleniumUrl = 'http://localhost:%d/selenium-server/driver/';
    protected $seleniumQuery = ['query' => ['cmd' => 'getLogMessages']];
    
    public function setSeleniumUrl($url)
    {
        $this->seleniumUrl = $url;
    }
    public function getSeleniumUrl()
    {
        return sprintf($this->seleniumUrl, $this->getSeleniumPort());
    }

    /**
     * @param string $query
     */
    public function setSeleniumQuery($query)
    {
        $this->seleniumQuery = $query;
    }
    /**
     * @return string
     */
    public function getSeleniumQuery()
    {
        return $this->seleniumQuery;
    }
    /**
     * @param string $path
     */
    public function setSeleniumLogLocation($path)
    {
        $this->seleniumLogLocation = $path;
    }
    /**
     * @return string
     */
    public function getSeleniumLogLocation()
    {
        return $this->seleniumLogLocation;
    }
    /**
     * @param string $path
     */
    public function setSeleniumJarLocation($path)
    {
        $this->seleniumJarLocation = $path;
    }
    /**
     * @return string
     */
    public function getSeleniumJarLocation()
    {
        return $this->seleniumJarLocation;
    }
    /**
     * @param int $port
     */
    public function setSeleniumPort($port)
    {
        $this->seleniumPort = $port;
    }
    /**
     * @return int
     */
    public function getSeleniumPort()
    {
        return $this->seleniumPort;
    }
}
