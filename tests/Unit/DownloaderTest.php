<?php

namespace BeubiQA\Tests\Unit;

use BeubiQA\Application\Selenium;

class DownloaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var Selenium\SeleniumDownloader  */
    private $downloader;
    private $httpClient;
    private $seleniumOptions;
    
    public function setup()
    {
        $this->httpClient = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $this->seleniumOptions = $this->getMockBuilder('BeubiQA\Application\Selenium\Options\SeleniumDownloaderOptions')->getMock();
        $this->downloader = new Selenium\SeleniumDownloader($this->seleniumOptions, $this->httpClient);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Destination and Download Url are mandatory.
     */
    public function testSeleniumUrlAndDestinationAreMandatory()
    {
        $this->downloader->download();
    }
    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Destination and Download Url are mandatory.
     */
    public function testSeleniumUrlIsMandatory()
    {
        $this->seleniumOptions->expects($this->any())->method('getSeleniumDownloadUrl')->willReturn('some_url');
        $this->downloader->download();
    }
    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Destination and Download Url are mandatory.
     */
    public function testSeleniumDestinationIsMandatory()
    {
        $this->seleniumOptions->expects($this->any())->method('getSeleniumDestination')->willReturn('some_dest');
        $this->downloader->download();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage  The selenium file does not exists. /tmp/selenium-server-standalone.jar
     */    
    public function testDownloaderDownloads()
    {
        $destinationFolder = '/tmp';
        $destination = $destinationFolder.'/selenium-server-standalone.jar';
        is_file($destination) ? unlink($destination) : '';
        $downloadUrl = 'some_url';
        $this->seleniumOptions->expects($this->any())->method('getSeleniumDestination')->willReturn($destinationFolder);
        $this->seleniumOptions->expects($this->any())->method('getSeleniumDownloadUrl')->willReturn($downloadUrl);
        $this->httpClient->expects($this->any())->method('get')->with($downloadUrl, ['save_to' => $destination]);
        $this->downloader->download();
    }
}
