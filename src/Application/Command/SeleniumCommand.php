<?php

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Process\Process;

class SeleniumCommand extends Command
{
    /** @var Client */
    protected $httpClient;

    /** @var ProgressBar */
    protected $progressBar;
    
    /** @var Process */
    protected $process;

    public function setProgressBar(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }
    
    public function setHttpClient(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    
    public function setProcess(Process $process)
    {
        $this->process = $process;
    }

    /**
     * @var integer
     */
    public $seleniumTimeout = 30000000;

    /**
     * @var integer
     */
    public $seleniumWaitInterval = 25000; // 0.025 seconds

    /**
     * @var string
     */
    public $seleniumLogFile = 'selenium.log';

    /**
     *
     * @return string
     */
    public function getSeleniumHostDriverURL()
    {
        return 'http://localhost:4444/selenium-server/driver/';
    }

    /**
     *
     * @param integer $userOption
     */
    protected function setSeleniumTimeout($userOption)
    {
        if ($userOption !== false) {
            $this->seleniumTimeout = (int) $userOption * 1000000;
        }
    }
    /**
     *
     * @param string $state 'on' or 'off'
     * @return integer false on timout or timeleft
     */
    public function waitForSeleniumState($state)
    {
        $this->progressBar ? $this->progressBar->start($this->seleniumTimeout) : '';
        $timeLeft = $this->seleniumTimeout;
        while (true) {
            $timeLeft = $this->updateTimeleft($timeLeft);
            $this->progressBar ? $this->progressBar->setProgress($this->seleniumTimeout - $timeLeft) : '';
            try {
                $this->httpClient->get($this->getSeleniumHostDriverURL(), ['query' => ['cmd' => 'getLogMessages']]);
            } catch (ConnectException $exc) {
                if (strtolower($state) == 'off') {
                    break;
                }
                continue; // try again
            }
            if (strtolower($state) == 'on') {
                break;
            }
        }
        $this->progressBar ? $this->progressBar->finish() : '';
        return $timeLeft;
    }

    /**
     * 
     * @param integer $timeLeft
     * @return integer timeleft
     */
    public function updateTimeleft($timeLeft)
    {
        $timeLeft -= $this->seleniumWaitInterval;
        if ($timeLeft < 0) {
            throw new \RuntimeException('Timeout!');
        }
        usleep($this->seleniumWaitInterval);

        return $timeLeft;
    }

    public function verifyLogFileWritable()
    {
        if (file_exists($this->seleniumLogFile) && !is_writable($this->seleniumLogFile)) {
            throw new \RuntimeException('No permissions in '.$this->seleniumLogFile);
        }
    }

    /**
     *
     * @param string $file
     */
    public function followFileContent($file, $level = 'INFO')
    {
        $size = 0;
        while (true) {
            clearstatcache();
            $currentSize = filesize($file);
            if ($size === $currentSize) {
                usleep(500);
                continue;
            }
            $fh = fopen($file, 'r');
            fseek($fh, $size);
            while ($line = fgets($fh)) {
                if (strpos($line, $level) !== false) {
                    echo $line;
                }
            }
            fclose($fh);
            $size = $currentSize;
        }
    }
}
