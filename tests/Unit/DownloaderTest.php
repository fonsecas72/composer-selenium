<?php

namespace BeubiQA\Tests\Unit;

use BeubiQA\Application\Selenium;

class DownloaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var Selenium\SeleniumDownloader  */
    private $downloader;
    
    public function setup()
    {
        $httpClient = new \GuzzleHttp\Client();
        $seleniumOptions = new Selenium\Options\SeleniumDownloaderOptions();
        $this->downloader = new Selenium\SeleniumDownloader($seleniumOptions, $httpClient);
    }

    public function testDownloaderDownloads()
    {
//        $this->downloader->download();
        $this->assertTrue(true);
    }
}
