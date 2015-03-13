<?php

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class SeleniumCommand extends Command
{
    /**
     * @var integer
     */
    public $seleniumCurlTimeout;
    
    /**
     * @var integer
     */
    public $seleniumCurlWaitInterval = 25000; // 0.025 seconds
    
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
        $this->seleniumCurlTimeout = 30000000;
        if ($userOption !== false) {
            $this->seleniumCurlTimeout = (int) $userOption * 1000000;
        }
    }
    
    /**
     *
     * @param boolean $expectedReturnStatus
     * @param OutputInterface $output
     * @param string $seleniumCmd
     * @return boolean whether if the expectedReturn was successful or not
     */
    public function waitForSeleniumCurlToReturn($expectedReturnStatus, OutputInterface $output, $seleniumCmd)
    {
        $timeLeft = $this->seleniumCurlTimeout;
        $progress = new ProgressBar($output, $this->seleniumCurlTimeout);
        $progress->start();
        $url = $this->getSeleniumHostDriverURL().'?cmd='.$seleniumCmd;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        while (true) {
            $status = curl_exec($ch);
            if (false !== $status && $expectedReturnStatus !== false
             || $status === $expectedReturnStatus
            ) {
                break;
            }
            $timeLeft -= $this->seleniumCurlWaitInterval;
            if ($timeLeft < 0) {
                $output->writeln(PHP_EOL.'Timeout curling: '.$url);

                return false;
            }
            usleep($this->seleniumCurlWaitInterval);
            $progress->setProgress($this->seleniumCurlTimeout - $timeLeft);
        }
        $progress->finish();
        curl_close($ch);

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
