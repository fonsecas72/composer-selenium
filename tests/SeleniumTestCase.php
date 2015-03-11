<?php

namespace BeubiQA\Tests;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use BeubiQA\Application\Console\SeleniumApplication;

class SeleniumTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $app = new SeleniumApplication();
        $app->get('stop')->run(
            new ArrayInput(array('stop')),
            new BufferedOutput()
        );
        $this->assertSeleniumIsNotRunning();
    }
    protected function assertSeleniumIsRunning()
    {
        $this->assertNotFalse($this->getSeleniumStatus());
    }
    protected function assertSeleniumIsNotRunning()
    {
        $this->assertFalse($this->getSeleniumStatus());
    }
    protected function getSeleniumStatus()
    {
        $ch = curl_init('http://localhost:4444/selenium-server/driver/?cmd=getLogMessages');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $status = curl_exec($ch);
        curl_close($ch);
        
        return $status;
    }
    
    protected $seleniumJarLocation = 'bin/selenium-server-standalone.jar';
    protected $seleniumBasicCommand = 'java -jar bin/selenium-server-standalone.jar';
    protected $seleniumJarDir = 'bin/';
    
    /**
     *
     * @param array $options
     * @return BufferedOutput
     */
    protected function startSelenium(array $options)
    {
        $output = new BufferedOutput();
        $app = new SeleniumApplication();
        $app->get('start')->run(
            new ArrayInput(
                $options
            ),
            $output
        );
        return $output;
    }
}
