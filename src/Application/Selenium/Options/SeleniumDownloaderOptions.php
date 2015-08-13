<?php

namespace BeubiQA\Application\Selenium\Options;

class SeleniumDownloaderOptions extends SeleniumOptions
{
    protected $seleniumDestination = '.';
    protected $seleniumVersion = '2.44';
    protected $seleniumDownloadUrl = 'http://selenium-release.storage.googleapis.com/%s/%s';

    public function getSeleniumDownloadUrl()
    {
        return sprintf(
            $this->seleniumDownloadUrl,
            $this->getSeleniumVersion(),
            'selenium-server-standalone-'.$this->getSeleniumVersion().'.0.jar'
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
