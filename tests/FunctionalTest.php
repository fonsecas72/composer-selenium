<?php

namespace BeubiQA\tests;

use BeubiQA\Application\Command\DownloadSeleniumCommand;
use BeubiQA\Tests\SeleniumTestCase;
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
        $getCmdTester->execute([
                '-d' => '/opt/',
        ]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage File already exists. ./bin/selenium-server-standalone.jar
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
     * @expectedExceptionMessage Selenium jar is not a file
     * @depends test_Get_Will_Download_a_File
     */
    public function test_Start_Does_Not_Exists()
    {
        $this->startSelenium([], ['-l' => 'no_selenium.jar']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Timeout
     * @depends test_Get_Will_Download_a_File
     */
    public function test_Start_Cmd_With_Short_Timeout()
    {
        $this->startSelenium(['-t' => 0]);
    }

    private function exeGetCmd()
    {
        $getCmd = new DownloadSeleniumCommand($this->handler);
        $getCmdTester = new CommandTester($getCmd);
        $getCmdTester->execute([
                '-d' => $this->seleniumJarDir,
        ]);

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
    public function test_Start_Cmd_XVFB()
    {
        $output = $this->startSelenium(['--xvfb' => true]);
        $this->assertContains('DISPLAY=:21 '.$this->seleniumBasicCommand, $output);
        $this->assertSeleniumIsRunning();
    }
}
