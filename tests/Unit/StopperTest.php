<?php

namespace BeubiQA\tests\Unit;

use BeubiQA\Application\Selenium;

class StopperTest extends \PHPUnit_Framework_TestCase
{
    /** @var Selenium\SeleniumStopper  */
    private $stopper;

    public function setup()
    {
        $this->httpClient = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $this->waiter = $this->getMockBuilder('BeubiQA\Application\Lib\ResponseWaitter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->seleniumOptions = $this->getMockBuilder('BeubiQA\Application\Selenium\Options\SeleniumStopOptions')->getMock();
        $this->stopper = new Selenium\SeleniumStopper($this->seleniumOptions, $this->waiter, $this->httpClient);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Port, Url, Shutdown Url, Shutdown Options, and Query are mandatory.
     */
    public function testNoQueryThrowsException()
    {
        $this->seleniumOptions->expects($this->any())->method('getSeleniumUrl')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumPort')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumShutdownUrl')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumShutDownOptions')->willReturn('not_empty');
        $this->stopper->stop();
    }
    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Port, Url, Shutdown Url, Shutdown Options, and Query are mandatory.
     */
    public function testNoUrlThrowsException()
    {
        $this->seleniumOptions->expects($this->any())->method('getSeleniumQuery')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumPort')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumShutdownUrl')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumShutDownOptions')->willReturn('not_empty');
        $this->stopper->stop();
    }
    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Port, Url, Shutdown Url, Shutdown Options, and Query are mandatory.
     */
    public function testNoPortThrowsException()
    {
        $this->seleniumOptions->expects($this->any())->method('getSeleniumQuery')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumUrl')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumShutdownUrl')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumShutDownOptions')->willReturn('not_empty');
        $this->stopper->stop();
    }
    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Port, Url, Shutdown Url, Shutdown Options, and Query are mandatory.
     */
    public function testNoShutdownOptionsThrowsException()
    {
        $this->seleniumOptions->expects($this->any())->method('getSeleniumPort')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumQuery')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumUrl')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumShutdownUrl')->willReturn('not_empty');
        $this->stopper->stop();
    }

    public function testStopperStops()
    {
        $shutUrl = 'shutUrl';
        $shutOptions = 'shutOptions';
        $this->seleniumOptions->expects($this->any())->method('getSeleniumQuery')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumUrl')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumPort')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumShutdownUrl')->willReturn($shutUrl);
        $this->seleniumOptions->expects($this->any())->method('getSeleniumShutDownOptions')->willReturn($shutOptions);

        $this->httpClient->expects($this->any())->method('get')->with('shutUrl', 'shutOptions');

        $this->stopper->stop();
    }
}
