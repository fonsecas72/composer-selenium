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
    private $port = 4444;
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
        return 'http://localhost:'.$this->port.'/selenium-server/driver/';
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
    public function waitForCurlToReturn($expectedReturnStatus, OutputInterface $output, $url, $timeout, $waitInterval)
    {
        $timeLeft = $timeout;
        $progress = new ProgressBar($output, $timeout);
        $progress->start();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        while (true) {
            $status = curl_exec($ch);
            if (false !== $status && $expectedReturnStatus !== false
             || $status === $expectedReturnStatus
            ) {
                break;
            }
            $timeLeft -= $waitInterval;
            if ($timeout < 0) {
                $output->writeln('Timeout to curl: '.$url);

                return false;
            }
            usleep($waitInterval);
            $progress->setProgress($timeout - $timeLeft);
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
    public function runCmdToStdOut($cmd, $tolerate = false)
    {
        $process = new Process($cmd);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if ($tolerate === false && !$process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'An error occurred when executing the "%s" command.',
                escapeshellarg($cmd)
            ));
        }
    }
}
