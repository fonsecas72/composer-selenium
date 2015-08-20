<?php

namespace BeubiQA\Application\Selenium\Options;

class SeleniumDownloaderOptions extends SeleniumOptions
{
    // example full url: http://selenium-release.storage.googleapis.com/2.47/selenium-server-standalone-2.47.1.jar
    protected $seleniumDestination = '.';
    protected $seleniumVersion = '2.44';
    protected $seleniumDownloadUrl = 'http://selenium-release.storage.googleapis.com/%s/%s';
    
    public function getSeleniumDownloadUrl()
    {
        $versionCut = substr($this->getSeleniumVersion(), 0, -2);
        
        return sprintf(
            $this->seleniumDownloadUrl,
            $versionCut,
            'selenium-server-standalone-'.$this->getSeleniumVersion().'.jar'
        );
    }

    public function setSeleniumDownloadUrl($seleniumDownloadUrl)
    {
        $this->seleniumDownloadUrl = $seleniumDownloadUrl;
    }



    public function getSeleniumDestination()
    {
        return $this->seleniumDestination;
    }
    public function setSeleniumDestination($seleniumDestination)
    {
        $this->seleniumDestination = $seleniumDestination;
    }
    public function getSeleniumVersion()
    {
        return $this->seleniumVersion;
    }
    public function setSeleniumVersion($seleniumVersion)
    {
        $this->seleniumVersion = $seleniumVersion;
    }


    
}
