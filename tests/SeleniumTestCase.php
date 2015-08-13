<?php

namespace BeubiQA\tests;

use BeubiQA\Application\Command;
use BeubiQA\Application\Lib;
use BeubiQA\Application\Selenium;
use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class SeleniumTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var SeleniumHandler */
    protected $handler;

    public function setUp()
    {
        parent::setUp();
        $seleniumStarterOptions = new Selenium\Options\SeleniumStartOptions();
        $process = new Process('');
        $exeFinder = new ExecutableFinder();
        $httpClient = new Client();
        $waiter = new Lib\ResponseWaitter($httpClient);
        $starter = new Selenium\SeleniumStarter($seleniumStarterOptions, $process, $waiter, $exeFinder);

        $seleniumOptions = new Selenium\Options\SeleniumStopOptions();
        $stopper = new Selenium\SeleniumStopper($seleniumOptions, $waiter, $httpClient);

        $seleniumOptions = new Selenium\Options\SeleniumDownloaderOptions();
        $downloader = new Selenium\SeleniumDownloader($seleniumOptions, $httpClient);

        $logWatcher = new Lib\LogWatcher();

        $this->handler = new Selenium\SeleniumHandler($starter, $stopper, $downloader, $logWatcher);

        $stopCmd = new Command\StopSeleniumCommand($this->handler);
        $stopCmdTester = new CommandTester($stopCmd);
        try {
            $stopCmdTester->execute([]);
        } catch (\Exception $exc) {
            // just to avoid failing when selenium is already stopped
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
    protected $seleniumBasicCommand = '/usr/bin/java -jar bin/selenium-server-standalone.jar -port 4444';
    protected $seleniumJarDir = './bin';

    /**
     * @param array $extraOptions
     * @param array $inpuOptions
     *
     * @return string
     */
    protected function startSelenium(
        array $extraOptions = [],
        array $inpuOptions = ['-l' => 'bin/selenium-server-standalone.jar']
    ) {
        $input = array_merge($inpuOptions, $extraOptions);
        $output = [];
        $output['verbosity'] = OutputInterface::VERBOSITY_VERY_VERBOSE;

        $startCmd = new Command\StartSeleniumCommand($this->handler);
        $startCmdTester = new CommandTester($startCmd);
        $startCmdTester->execute($input, $output);

        return $startCmdTester->getDisplay();
    }
}
