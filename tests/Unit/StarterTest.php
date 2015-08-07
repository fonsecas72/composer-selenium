<?php

namespace BeubiQA\Tests\Unit;

use BeubiQA\Application\Selenium;
use BeubiQA\Application\Lib;

class StarterTest extends \PHPUnit_Framework_TestCase
{
    /** @var Selenium\SeleniumStarter */
    protected $starter;
    public function setup()
    {
        $this->process = $this->getMockBuilder('Symfony\Component\Process\Process')
            ->disableOriginalConstructor()
            ->getMock();
        $this->exeFinder = $this->getMockBuilder('Symfony\Component\Process\ExecutableFinder')->getMock();
        $this->httpClient = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $this->waiter = $this->getMockBuilder('BeubiQA\Application\Lib\ResponseWaitter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->seleniumOptions = $this->getMockBuilder('BeubiQA\Application\Selenium\Options\SeleniumStartOptions')->getMock();
        $this->starter = new Selenium\SeleniumStarter($this->seleniumOptions, $this->process, $this->waiter, $this->exeFinder);
    }
    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Url, Query and Selenium Jar Location is mandatory and Jar Location should point to a .jar file.
     */
    public function testNoJarLocationThrowsException()
    {
        $this->starter->start();
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Url, Query and Selenium Jar Location is mandatory and Jar Location should point to a .jar file.
     */
    public function testNoQueryThrowsException()
    {
        $jarLocation = __DIR__.'/../fixtures';
        $this->seleniumOptions->expects($this->any())->method('getSeleniumUrl')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumJarLocation')->willReturn($jarLocation);
        $this->starter->start();
    }
    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Url, Query and Selenium Jar Location is mandatory and Jar Location should point to a .jar file.
     */
    public function testNoUrlThrowsException()
    {
        $jarLocation = __DIR__.'/../fixtures';
        $this->seleniumOptions->expects($this->any())->method('getSeleniumQuery')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumJarLocation')->willReturn($jarLocation);
        $this->starter->start();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Selenium jar is not a file
     */
    public function testNotAFileThrowsException()
    {
        $jarLocation = __DIR__.'/../fixtures';
        $this->seleniumOptions->expects($this->any())->method('getSeleniumQuery')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumUrl')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumJarLocation')->willReturn($jarLocation);
        $this->starter->start();
    }
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Selenium jar not readable
     */
    public function testNotReadableThrowsException()
    {
        $jarLocation = __DIR__.'/../fixtures/selenium-no-permissions.jar';

        chmod($jarLocation, 100);

        $this->seleniumOptions->expects($this->any())->method('getSeleniumQuery')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumUrl')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumJarLocation')->willReturn($jarLocation);
        $this->starter->start();
    }

    public function testStarterStarts()
    {
        $jarLocation = __DIR__.'/../fixtures/selenium-dummy.jar';
        $javaLocation = 'java-location';
        $this->exeFinder->expects($this->any())->method('find')->with('java')->willReturn($javaLocation);
        $this->seleniumOptions->expects($this->any())->method('getSeleniumQuery')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumUrl')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumJarLocation')->willReturn($jarLocation);
        $this->process->expects($this->any())->method('setCommandLine')->with($javaLocation.' -jar '.$jarLocation);
        $this->starter->start();
    }
    
    public function testStarterStartsWithXvfb()
    {
        $jarLocation = __DIR__.'/../fixtures/selenium-dummy.jar';
        $javaLocation = 'java-location';
        $xvfbLocation = 'xvfb-location';
        $this->exeFinder->expects($this->at(0))->method('find')->with('java')->willReturn($javaLocation);
        $this->exeFinder->expects($this->at(1))->method('find')->with('xvfb-run')->willReturn($xvfbLocation);
        $this->seleniumOptions->expects($this->any())->method('getSeleniumQuery')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumUrl')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumJarLocation')->willReturn($jarLocation);
        $this->seleniumOptions->expects($this->any())->method('isXvfbEnabled')->willReturn(true);
        $exceptedCmd = 'DISPLAY=:1 '.$xvfbLocation.' --auto-servernum --server-num=1 '.$javaLocation.' -jar '.$jarLocation;
        $this->process->expects($this->any())->method('setCommandLine')->with($exceptedCmd);
        $this->starter->start();
    }

    public function testStarterStartsWithPortByExtraArgs()
    {
        $jarLocation = __DIR__.'/../fixtures/selenium-dummy.jar';
        $javaLocation = 'java-location';
        $port = 1234;
        $this->exeFinder->expects($this->any())->method('find')->with('java')->willReturn($javaLocation);
        $this->seleniumOptions->expects($this->any())->method('getSeleniumQuery')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumUrl')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumJarLocation')->willReturn($jarLocation);
        $this->seleniumOptions->expects($this->any())->method('getSeleniumExtraArguments')->willReturn(['port' => $port]);
        $this->process->expects($this->any())->method('setCommandLine')->with($javaLocation.' -jar '.$jarLocation.' -port '.$port);
        $this->starter->start();
    }
    public function testStarterStartsWithPortByOptions()
    {
        $jarLocation = __DIR__.'/../fixtures/selenium-dummy.jar';
        $javaLocation = 'java-location';
        $port = 1234;
        $this->exeFinder->expects($this->any())->method('find')->with('java')->willReturn($javaLocation);
        $this->seleniumOptions->expects($this->any())->method('getSeleniumQuery')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumUrl')->willReturn('not_empty');
        $this->seleniumOptions->expects($this->any())->method('getSeleniumJarLocation')->willReturn($jarLocation);
        $this->seleniumOptions->expects($this->any())->method('getSeleniumPort')->willReturn($port);
        $this->process->expects($this->any())->method('setCommandLine')->with($javaLocation.' -jar '.$jarLocation.' -port '.$port);
        $this->starter->start();
    }

}
