<?php

namespace BeubiQA\Tests;

use BeubiQA\Application\Console\SeleniumApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class SeleniumTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $stopCmd = new \BeubiQA\Application\Command\StopSeleniumCommand();
        $stopCmd->setHttpClient(new \GuzzleHttp\Client());
        $stopCmd->run(
            new ArrayInput(),
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
     * @param array $extraOptions
     * @param array $defaultOptions
     * @return BufferedOutput
     */
    protected function startSelenium(
        array $extraOptions = array(),
        array $defaultOptions = array('-l' => 'bin/selenium-server-standalone.jar')
    ) {
        $defaultOptions[] = 'start';
        $options = array_merge($defaultOptions, $extraOptions);
        $output = new BufferedOutput();
        $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $app = new SeleniumApplication();
        $app->get('start')->run(
            new ArrayInput($options),
            $output
        );
        return $output;
    }
}
