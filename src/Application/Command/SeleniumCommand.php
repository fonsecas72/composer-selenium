<?php

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class SeleniumCommand extends Command
{
    /** @var Client */
    protected $httpClient;

    /** @var ProgressBar */
    protected $progressBar;

    public function setProgressBar(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }
    
    public function setHttpClient(Client $httpClient)
    {
        $this->httpClient = $httpClient;
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
     * @param int $userOption
     */
    protected function setSeleniumTimeout($userOption)
    {
        if ($userOption !== false) {
            $this->seleniumTimeout = (int) $userOption * 1000000;
        }
    }
    public function waitForSeleniumState($state)
    {
        $this->progressBar->start($this->seleniumTimeout);
        $timeLeft = $this->seleniumTimeout;
        while (true) {
            $timeLeft = $this->updateTimeleft($timeLeft);
            $this->progressBar->setProgress($this->seleniumTimeout - $timeLeft);
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
        $this->progressBar->finish();
        return $timeLeft;
    }
    
    public function updateTimeleft($timeLeft)
    {
        $timeLeft -= $this->seleniumWaitInterval;
        if ($timeLeft < 0) {
            return false;
        }
        usleep($this->seleniumWaitInterval);

        return $timeLeft;
    }
    /**
     *
     * @param string $file
     */
    public function followFileContent($file)
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
                echo $line;
            }
            fclose($fh);
            $size = $currentSize;
        }
    }
}
