<?php

namespace BeubiQA\Application\Selenium;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Process\ExecutableFinder;

class SeleniumCommandGetter
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
    public function getStartCommand($options)
    {
        $cmd = $this->exeFinder->find('java').' -jar '.$options['selenium-location'];
        if ($options['xvfb']) {
            $xvfbCmd = 'DISPLAY=:1 '.$this->exeFinder->find('xvfb-run').' --auto-servernum --server-num=1';
            $cmd = $xvfbCmd.' '.$cmd;
        }
        if ($options['firefox-profile']) {
            if (!is_dir($options['firefox-profile'])) {
                throw new \RuntimeException('The Firefox-profile you set is not available.');
            }
            $cmd .= ' -firefoxProfileTemplate '.$options['firefox-profile'];
        }
        if ($options['chrome-driver']) {
            $cmd .= ' -Dwebdriver.chrome.driver='.$options['chrome-driver'];
        }

        return $cmd.' > '.$options['log-location'].' 2> '.$options['log-location'];
    }
}
