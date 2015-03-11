<?php

namespace BeubiQA\Tests;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use BeubiQA\Application\Console\SeleniumApplication;

class FunctionalTest extends \PHPUnit_Framework_TestCase
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
    private function assertSeleniumIsRunning()
    {
        $ch = curl_init('http://localhost:4444/selenium-server/driver/?cmd=getLogMessages');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $status = curl_exec($ch);
        $this->assertNotFalse($status);
        curl_close($ch);
    }
    private function assertSeleniumIsNotRunning()
    {
        $ch = curl_init('http://localhost:4444/selenium-server/driver/?cmd=getLogMessages');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $status = curl_exec($ch);
        $this->assertFalse($status);
        curl_close($ch);
    }
    
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not enough permissions
     */
    public function test_Get_Without_Permissions()
    {
        $dir = '/opt/';
        $app = new SeleniumApplication();
        $app->get('get')->run(
            new ArrayInput(
                array(
                    'get',
                    '-d' => $dir
                )
            ),
            new BufferedOutput()
        );
    }
    
    public function test_Get_Will_Download_a_File()
    {
        $dir = 'bin/';
        $jarExpectedLocation = $dir.'selenium-server-standalone.jar';
        
        $app = new SeleniumApplication();
        $app->get('get')->run(
            new ArrayInput(
                array(
                    'get',
                    '-d' => $dir
                )
            ),
            new BufferedOutput()
        );
        
        $this->assertFileExists($jarExpectedLocation);
        $this->assertEquals('deb2a8d4f6b5da90fd38d1915459ced2e53eb201', sha1_file($jarExpectedLocation));
    }
    
    public function test_Start_Works()
    {
        $dir = 'bin/';
        $jarExpectedLocation = $dir.'selenium-server-standalone.jar';
        
        $output = new BufferedOutput();
        $app = new SeleniumApplication();
        $app->get('start')->run(
            new ArrayInput(
                array(
                    'start',
                    '-l' => $jarExpectedLocation
                )
            ),
            $output
        );
        $this->assertSeleniumIsRunning();
        $this->assertContains('java -jar bin/selenium-server-standalone.jar > selenium.log 2> selenium.log &', $output->fetch());
    }
    
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Selenium jar not found
     */
    public function test_Start_Does_Not_Exists()
    {
        $dir = 'bin/';
        $jarExpectedLocation = $dir.'no_selenium.jar';
        
        $app = new SeleniumApplication();
        $app->get('start')->run(
            new ArrayInput(
                array(
                    'start',
                    '-l' => $jarExpectedLocation
                )
            ),
            new BufferedOutput()
        );
    }
    
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Selenium hasn't started successfully.
     */
    public function test_Start_Cmd_Firefox_Profile_That_Does_Not_Exists()
    {
        $dir = 'bin/';
        $jarExpectedLocation = $dir.'selenium-server-standalone.jar';
        
        $output = new BufferedOutput();
        $app = new SeleniumApplication();
        $app->get('start')->run(
            new ArrayInput(
                array(
                    'start',
                    '-l' => $jarExpectedLocation,
                    '-p' => '/sasa/',
                )
            ),
            $output
        );
        $this->assertSeleniumIsRunning();
        $this->assertContains('java -jar bin/selenium-server-standalone.jar -firefoxProfileTemplate /opt/fidd > selenium.log 2> selenium.log &', $output->fetch());
        $this->assertContains('Firefox profile template doesn\'t exist', $output->fetch());
    }
    
    public function test_Start_Cmd_Firefox_Profile()
    {
        $dir = 'bin/';
        $jarExpectedLocation = $dir.'selenium-server-standalone.jar';
        $output = new BufferedOutput();
        $app = new SeleniumApplication();
        $profileDirPath = __DIR__.'/Resources/firefoxProfile/';
        $app->get('start')->run(
            new ArrayInput(
                array(
                    'start',
                    '-l' => $jarExpectedLocation,
                    '-p' => $profileDirPath,
                )
            ),
            $output
        );
        $this->assertSeleniumIsRunning();
        $this->assertContains('java -jar bin/selenium-server-standalone.jar -firefoxProfileTemplate '.$profileDirPath.' > selenium.log 2> selenium.log &', $output->fetch());
    }
    
    public function test_Start_Cmd_XVFB_Fail()
    {
        $dir = 'bin/';
        $jarExpectedLocation = $dir.'selenium-server-standalone.jar';
        $output = new BufferedOutput();
        $app = new SeleniumApplication();
        $app->get('start')->run(
            new ArrayInput(
                array(
                    'start',
                    '-l' => $jarExpectedLocation,
                    '--xvfb' => true
                )
            ),
            $output
        );
        $this->assertContains('DISPLAY=:1 /usr/bin/xvfb-run --auto-servernum --server-num=1 java -jar', $output->fetch());
        $this->assertSeleniumIsRunning();
    }
}
