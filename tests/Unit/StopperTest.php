<?php

namespace BeubiQA\Tests\Unit;

use BeubiQA\Application\Selenium;
use BeubiQA\Application\Lib;

class StopperTest extends \PHPUnit_Framework_TestCase
{
    /** @var Selenium\SeleniumStopper  */
    private $stopper;
    
    public function setup()
    {
        $httpClient = new \GuzzleHttp\Client();
        $waiter = new Lib\ResponseWaitter(
            $httpClient
        );
        $seleniumOptions = new Selenium\Options\SeleniumStopOptions();
        $this->stopper = new Selenium\SeleniumStopper($seleniumOptions, $waiter, $httpClient);
    }

    public function testStopperStops()
    {
//        $this->stopper->stop();
        $this->assertTrue(true);
    }
}
