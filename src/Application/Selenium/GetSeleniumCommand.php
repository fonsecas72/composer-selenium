<?php

namespace BeubiQA\Application\Selenium;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Process\ExecutableFinder;

class GetSeleniumCommand
{
    /** @var ExecutableFinder */
    protected $exeFinder;

    public function __construct(ExecutableFinder $exeFinder)
    {
        $this->exeFinder = $exeFinder;
    }
    /**
     *
     * @param InputInterface $input
     * @param string $seleniumLocation
     * @param string $seleniumLogFile
     * @return string
     * @throws \RuntimeException
     */
    public function getStartCommand(InputInterface $input, $seleniumLocation, $seleniumLogFile)
    {
        $cmd = $this->exeFinder->find('java').' -jar '.$seleniumLocation;
        if ($input->getOption('xvfb')) {
            $xvfbCmd = 'DISPLAY=:1 '.$this->exeFinder->find('xvfb-run').' --auto-servernum --server-num=1';
            $cmd = $xvfbCmd.' '.$cmd;
        }
        if ($input->getOption('firefox-profile')) {
            if (!is_dir($input->getOption('firefox-profile'))) {
                throw new \RuntimeException('The Firefox-profile you set is not available.');
            }
            $cmd .= ' -firefoxProfileTemplate '.$input->getOption('firefox-profile');
        }
        if ($input->getOption('chrome-driver')) {
            $cmd .= ' -Dwebdriver.chrome.driver='.$input->getOption('chrome-driver');
        }

        return $cmd.' > '.$seleniumLogFile.' 2> '.$seleniumLogFile;
    }
}
