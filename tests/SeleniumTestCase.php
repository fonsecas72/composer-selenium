<?php

namespace BeubiQA\Tests;

use BeubiQA\Application\Command\StartSeleniumCommand;
use BeubiQA\Application\Command\StopSeleniumCommand;
use BeubiQA\Application\Selenium\SeleniumDownloader;
use BeubiQA\Application\Selenium\SeleniumHandler;
use BeubiQA\Application\Selenium\SeleniumLogWatcher;
use BeubiQA\Application\Selenium\SeleniumStarter;
use BeubiQA\Application\Selenium\SeleniumStopper;
use BeubiQA\Application\Selenium\SeleniumWaitter;
use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;

class SeleniumTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var Client */
    protected $httpClient;

    /** @var Process */
    protected $process;
    
    /** @var SeleniumWaitter */
    protected $waitter;
    
    /** @var SeleniumStopper */
    protected $stopper;

    /** @var SeleniumDownloader */
    protected $downloader;
    /** @var SeleniumHandler */
    protected $handler;

    public function setUp()
    {
        parent::setUp();
        $this->httpClient = new Client();
        $this->process = new Process('');
        $this->waitter = new SeleniumWaitter($this->httpClient);
        $this->stopper = new SeleniumStopper($this->waitter, $this->httpClient);
        $this->logWatcher = new SeleniumLogWatcher();
        $this->starter = new SeleniumStarter(
            $this->process,
            $this->waitter,
            new \Symfony\Component\Process\ExecutableFinder()
        );
        $this->downloader = new SeleniumDownloader($this->httpClient);
        $this->handler = new SeleniumHandler($this->starter, $this->stopper, $this->downloader, $this->logWatcher);


        $stopCmd = new StopSeleniumCommand($this->handler);
        $stopCmdTester = new CommandTester($stopCmd);
        try {
            $stopCmdTester->execute([]);
        } catch (\Exception $exc) {
        }
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
    protected $seleniumBasicCommand = '/usr/bin/java -jar bin/selenium-server-standalone.jar';
    protected $seleniumJarDir = 'bin/';
    
    /**
     *
     * @param array $extraOptions
     * @param array $inpuOptions
     * @return string
     */
    protected function startSelenium(
        array $extraOptions = array(),
        array $inpuOptions = array('-l' => 'bin/selenium-server-standalone.jar')
    ) {
        $input = array_merge($inpuOptions, $extraOptions);
        $output = [];
        $output['verbosity'] = OutputInterface::VERBOSITY_VERY_VERBOSE;

        $startCmd = new StartSeleniumCommand($this->handler);
        $startCmdTester = new CommandTester($startCmd);
        $startCmdTester->execute($input, $output);
        
        return $startCmdTester->getDisplay();
    }
}
