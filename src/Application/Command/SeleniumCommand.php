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
    public $seleniumTimeout;

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
        $this->seleniumTimeout = 30000000;
        if ($userOption !== false) {
            $this->seleniumTimeout = (int) $userOption * 1000000;
        }
    }

    /**
     *
     * @param boolean $expectedReturnStatus
     * @param OutputInterface $output
     * @param string $seleniumCmd
     * @return boolean whether if the expectedReturn was successful or not
     */
    public function orderAndWaitForIt($expectedReturnStatus, OutputInterface $output, $seleniumCmd)
    {
        $client = new Client();
        $timeLeft = $this->seleniumTimeout;
        $progress = new ProgressBar($output, $this->seleniumTimeout);
        $progress->start();
        while (true) {
            $res = null;
            try {
                $res = $client->get($this->getSeleniumHostDriverURL(), [
                    'query' => ['cmd' => $seleniumCmd],
                    // 'synchronous' => true, // guzzle 6
                ]);
            } catch (ConnectException $e) {
                if ($expectedReturnStatus > 400) {

                    break;
                }
            }

            if (isset($res) && $res->getStatusCode() == $expectedReturnStatus) {
                break;
            };

            $timeLeft -= $this->seleniumWaitInterval;
            if ($timeLeft < 0) {
                $output->writeln(PHP_EOL.'Timeout: '.$this->getSeleniumHostDriverURL());

                return false;
            }
            usleep($this->seleniumWaitInterval);
            $progress->setProgress($this->seleniumTimeout - $timeLeft);
        }
        $progress->finish();

        return true;
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
