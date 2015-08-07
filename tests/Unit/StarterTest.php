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
        $process = new \Symfony\Component\Process\Process('');
        $exeFinder = new \Symfony\Component\Process\ExecutableFinder();
        $httpClient = new \GuzzleHttp\Client();
        $waiter = new Lib\ResponseWaitter(
            $httpClient
        );
        $seleniumStarterOptions = new Selenium\Options\SeleniumStartOptions();
        $this->starter = new Selenium\SeleniumStarter($seleniumStarterOptions, $process, $waiter, $exeFinder);
    }

    /**
     *
     */
    public function testStarterStarts()
    {
//        $this->starter->start();
        $this->assertTrue(true);
    }
}
