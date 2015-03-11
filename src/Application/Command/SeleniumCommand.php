<?php

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class SeleniumCommand extends Command
{
    /**
     * @var integer
     */
    public $seleniumStartTimeout = 5000000; // 5 seconds
    /**
     * @var integer
     */
    public $seleniumStartWaitInterval = 25000; // 0.025 seconds

    public function getSeleniumHostDriverURL()
    {
        return 'http://localhost:4444/selenium-server/driver/';
    }
    
    /**
     *
     * @param boolean $expectedReturnStatus
     * @param OutputInterface $output
     * @param string $url
     * @param int $timeout
     * @param int $waitInterval
     * @return boolean whether if the expectedReturn was successful or not
     */
    public function waitForSeleniumCurlToReturn($expectedReturnStatus, OutputInterface $output, $seleniumCmd)
    {
        $timeLeft = $this->seleniumStartTimeout;
        $progress = new ProgressBar($output, $this->seleniumStartTimeout);
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
            $timeLeft -= $this->seleniumStartWaitInterval;
            if ($timeLeft < 0) {
                $output->writeln('Timeout to curl: '.$url);

                return false;
            }
            usleep($this->seleniumStartWaitInterval);
            $progress->setProgress($this->seleniumStartTimeout - $timeLeft);
        }
        $progress->finish();
        curl_close($ch);

        return true;
    }

    /**
     *
     * @param string $cmd
     * @param boolean $tolerate whether to throw exception on failure or not
     */
    public function runCmdToStdOut($cmd)
    {
        $process = new Process($cmd);
        $process->start();
    }
}
