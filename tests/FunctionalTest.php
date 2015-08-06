<?php

namespace BeubiQA\Tests;

use BeubiQA\Tests\SeleniumTestCase;
use BeubiQA\Application\Command\DownloadSeleniumCommand;
use Symfony\Component\Console\Tester\CommandTester;

class FunctionalTest extends SeleniumTestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not enough permissions
     */
    public function test_Get_Without_Permissions()
    {
        $getCmdTester = new CommandTester(new DownloadSeleniumCommand($this->handler));
        $getCmdTester->execute(array(
             '-d' => '/opt/'
        ));
    }
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage File already exists. bin/selenium-server-standalone.jar
     */
    public function test_Get_Will_Download_a_File()
    {
        is_file($this->seleniumJarLocation) ? unlink($this->seleniumJarLocation) : '';
        $output = $this->exeGetCmd();
        $this->assertFileExists($this->seleniumJarLocation);
        $this->assertEquals('deb2a8d4f6b5da90fd38d1915459ced2e53eb201', sha1_file($this->seleniumJarLocation));
        $this->assertContains('Done', $output);

        $output = $this->exeGetCmd();
        $this->assertFileExists($this->seleniumJarLocation);
        $this->assertContains('Skipping download as the file already exists.', $output);
        $this->assertContains('Done', $output);
    }
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Selenium jar not readable
     * @depends test_Get_Will_Download_a_File
     */
    public function test_Start_Does_Not_Exists()
    {
        $this->startSelenium(array(), array('-l' => 'no_selenium.jar'));
    }
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The Firefox-profile you set is not available.
     * @depends test_Get_Will_Download_a_File
     */
    public function test_Start_Cmd_Firefox_Profile_That_Does_Not_Exists()
    {
        $this->startSelenium(array('-p' => '/sasa/'));
    }
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Timeout
     * @depends test_Get_Will_Download_a_File
     */
    public function test_Start_Cmd_With_Short_Timeout()
    {
        $this->startSelenium(array('-t' => 0));
    }
    private function exeGetCmd()
    {
        $getCmd = new DownloadSeleniumCommand($this->handler);
        $getCmdTester = new CommandTester($getCmd);
        $getCmdTester->execute(array(
             '-d' => $this->seleniumJarDir
        ));
        return $getCmdTester->getDisplay();
    }

    /**
     * @depends test_Get_Will_Download_a_File
     */
    public function test_Start_Works()
    {
        $output = $this->startSelenium();
        $this->assertSeleniumIsRunning();
        $this->assertContains($this->seleniumBasicCommand.' > selenium.log 2> selenium.log', $output);
    }

    /**
     * @depends test_Get_Will_Download_a_File
     */
    public function test_Start_Cmd_Firefox_Profile()
    {
        $profileDirPath = __DIR__.'/Resources/firefoxProfile/';
        $output = $this->startSelenium(array('-p' => $profileDirPath));
        
        $this->assertSeleniumIsRunning();
        $this->assertContains($this->seleniumBasicCommand.' -firefoxProfileTemplate '.$profileDirPath.' > selenium.log 2> selenium.log', $output);
    }

    /**
     * @depends test_Get_Will_Download_a_File
     */
    public function test_Start_Cmd_XVFB()
    {
        $output = $this->startSelenium(array('--xvfb' => true));
        $this->assertContains('DISPLAY=:1 /usr/bin/xvfb-run --auto-servernum --server-num=1 '.$this->seleniumBasicCommand, $output);
        $this->assertSeleniumIsRunning();
    }
}
