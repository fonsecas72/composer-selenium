<?php

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
     * @param string $seleniumCmd
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
     */
    public function runCmdToStdOut($cmd)
    {
        $process = new Process($cmd);
        $process->start();
    }
}
