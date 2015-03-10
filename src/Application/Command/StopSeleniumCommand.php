<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BeubiQA\Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class StopSeleniumCommand extends Command
{
    /**
     * @var integer
     */
    public $port = 4444;
    
    /**
     * Command configuration
     *
     * @return void
     */
    protected function configure()
    {
        $this
        ->setName('stop')
        ->setDescription('This will start/stop Selenium2 server.');
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleStop($input, $output);
        $output->writeln("\nDone");
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function handleStop(InputInterface $input, OutputInterface $output)
    {
        $this->waitForCurlToReturn(
            false,
            $output,
            $this->getSeleniumHostDriverURL().'?cmd=shutDownSeleniumServer',
            $this->seleniumStartTimeout,
            $this->seleniumStartWaitInterval
        );
    }

    /**
     *
     * @return string
     */
    private function getSeleniumHostDriverURL()
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
    private function waitForCurlToReturn($expectedReturnStatus, OutputInterface $output, $url, $timeout, $waitInterval)
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
}
