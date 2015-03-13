<?php

namespace BeubiQA\Tests;

use BeubiQA\Application\Console\SeleniumApplication;
use BeubiQA\Tests\SeleniumTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class FunctionalTest extends SeleniumTestCase
{
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
        $output = $this->startSelenium();
        $this->assertSeleniumIsRunning();
        $this->assertContains($this->seleniumBasicCommand.' > selenium.log 2> selenium.log &', $output->fetch());
    }
    
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Selenium jar not found
     */
    public function test_Start_Does_Not_Exists()
    {
        $this->startSelenium(array(), array('-l' => 'no_selenium.jar'));
    }
    
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Selenium hasn't started successfully.
     */
    public function test_Start_Cmd_Firefox_Profile_That_Does_Not_Exists()
    {
        $output = $this->startSelenium(array('-p' => '/sasa/'));
        $this->assertSeleniumIsRunning();
        $this->assertContains($this->seleniumBasicCommand.' -firefoxProfileTemplate /opt/fidd > selenium.log 2> selenium.log &', $output->fetch());
        $this->assertContains('Firefox profile template doesn\'t exist', $output->fetch());
    }
    
    public function test_Start_Cmd_Firefox_Profile()
    {
        $profileDirPath = __DIR__.'/Resources/firefoxProfile/';
        $output = $this->startSelenium(array('-p' => $profileDirPath));
        
        $this->assertSeleniumIsRunning();
        $this->assertContains($this->seleniumBasicCommand.' -firefoxProfileTemplate '.$profileDirPath.' > selenium.log 2> selenium.log &', $output->fetch());
    }
    
    public function test_Start_Cmd_XVFB()
    {
        $output = $this->startSelenium(array('--xvfb' => true));
        $this->assertContains('DISPLAY=:1 /usr/bin/xvfb-run --auto-servernum --server-num=1 '.$this->seleniumBasicCommand, $output->fetch());
        $this->assertSeleniumIsRunning();
    }
    
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Selenium hasn't started successfully.
     */
    public function test_Start_Cmd_With_Short_Timeout()
    {
        $output = $this->startSelenium(array('-t' => 0));
        $this->assertSeleniumIsNotRunning();
        $this->assertContains('Timeout curling', $output->fetch());
    }
}
