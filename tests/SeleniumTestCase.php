<?php

namespace BeubiQA\Tests;

use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use BeubiQA\Application\Command\StartSeleniumCommand;
use BeubiQA\Application\Command\StopSeleniumCommand;
use Symfony\Component\Console\Tester\CommandTester;

class SeleniumTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $stopCmd = new StopSeleniumCommand();
        $this->httpClient = new \GuzzleHttp\Client();
        $stopCmd->setHttpClient($this->httpClient);
        $stopCmdTester = new CommandTester($stopCmd);
        $stopCmdTester->execute([]);
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
        $startCmd = new StartSeleniumCommand();
        $startCmd->setHttpClient($this->httpClient);
        $startCmdTester = new CommandTester($startCmd);
        $startCmdTester->execute($input, $output);
        
       return $startCmdTester->getDisplay();
    }
}
