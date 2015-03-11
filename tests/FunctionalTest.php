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
        $this->assertNotFalse($this->getSeleniumStatus());
    }
    private function assertSeleniumIsNotRunning()
    {
        $this->assertFalse($this->getSeleniumStatus());
    }
    private function getSeleniumStatus()
    {
        $ch = curl_init('http://localhost:4444/selenium-server/driver/?cmd=getLogMessages');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $status = curl_exec($ch);
        curl_close($ch);
        
        return $status;
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
    
    private $seleniumJarLocation = 'bin/selenium-server-standalone.jar';
    private $seleniumBasicCommand = 'java -jar bin/selenium-server-standalone.jar';
    private $seleniumJarDir = 'bin/';
    
    public function test_Get_Will_Download_a_File()
    {
        $app = new SeleniumApplication();
        $app->get('get')->run(
            new ArrayInput(
                array(
                    'get',
                    '-d' => $this->seleniumJarDir
                )
            ),
            new BufferedOutput()
        );
        
        $this->assertFileExists($this->seleniumJarLocation);
        $this->assertEquals('deb2a8d4f6b5da90fd38d1915459ced2e53eb201', sha1_file($this->seleniumJarLocation));
    }
    
    public function test_Start_Works()
    {
        $output = new BufferedOutput();
        $app = new SeleniumApplication();
        $app->get('start')->run(
            new ArrayInput(
                array(
                    'start',
                    '-l' => $this->seleniumJarLocation
                )
            ),
            $output
        );
        $this->assertSeleniumIsRunning();
        $this->assertContains($this->seleniumBasicCommand.' > selenium.log 2> selenium.log &', $output->fetch());
    }
    
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Selenium jar not found
     */
    public function test_Start_Does_Not_Exists()
    {
        $app = new SeleniumApplication();
        $app->get('start')->run(
            new ArrayInput(
                array(
                    'start',
                    '-l' => 'no_selenium.jar'
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
        $output = new BufferedOutput();
        $app = new SeleniumApplication();
        $app->get('start')->run(
            new ArrayInput(
                array(
                    'start',
                    '-l' => $this->seleniumJarLocation,
                    '-p' => '/sasa/',
                )
            ),
            $output
        );
        $this->assertSeleniumIsRunning();
        $this->assertContains($this->seleniumBasicCommand.' -firefoxProfileTemplate /opt/fidd > selenium.log 2> selenium.log &', $output->fetch());
        $this->assertContains('Firefox profile template doesn\'t exist', $output->fetch());
    }
    
    public function test_Start_Cmd_Firefox_Profile()
    {
        $output = new BufferedOutput();
        $app = new SeleniumApplication();
        $profileDirPath = __DIR__.'/Resources/firefoxProfile/';
        $app->get('start')->run(
            new ArrayInput(
                array(
                    'start',
                    '-l' => $this->seleniumJarLocation,
                    '-p' => $profileDirPath,
                )
            ),
            $output
        );
        $this->assertSeleniumIsRunning();
        $this->assertContains($this->seleniumBasicCommand.' -firefoxProfileTemplate '.$profileDirPath.' > selenium.log 2> selenium.log &', $output->fetch());
    }
    
    public function test_Start_Cmd_XVFB_Fail()
    {
        $output = new BufferedOutput();
        $app = new SeleniumApplication();
        $app->get('start')->run(
            new ArrayInput(
                array(
                    'start',
                    '-l' => $this->seleniumJarLocation,
                    '--xvfb' => true
                )
            ),
            $output
        );
        $this->assertContains('DISPLAY=:1 /usr/bin/xvfb-run --auto-servernum --server-num=1 '.$this->seleniumBasicCommand, $output->fetch());
        $this->assertSeleniumIsRunning();
    }
}
