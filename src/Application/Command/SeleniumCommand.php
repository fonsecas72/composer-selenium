<?php

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class SeleniumCommand extends Command
{
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
    public function waitForSeleniumState($state, OutputInterface $output)
    {
        $client = new Client();
        $progress = new ProgressBar($output, $this->seleniumTimeout);
        $progress->start();
        $timeLeft = $this->seleniumTimeout;
        while (true) {
            $timeLeft = $this->updateTimeleft($timeLeft);
            $progress->setProgress($this->seleniumTimeout - $timeLeft);
            try {
                $client->get($this->getSeleniumHostDriverURL(), ['query' => ['cmd' => 'getLogMessages']]);
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
        $progress->finish();
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
