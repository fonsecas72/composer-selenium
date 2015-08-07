<?php

namespace BeubiQA\Application\Selenium;

use BeubiQA\Application\Selenium;
use BeubiQA\Application\Lib\LogWatcher;

class SeleniumHandler
{
    /** @var Selenium\SeleniumStarter  */
    protected $seleniumStarter;
    /** @var Selenium\SeleniumStopper  */
    protected $seleniumStopper;
    /** @var Selenium\SeleniumDownloader  */
    protected $seleniumDownloader;
    /** @var LogWatcher  */
    protected $seleniumLogWatcher;

    /**
     *
     * @param Selenium\SeleniumStarter $seleniumStarter
     * @param Selenium\SeleniumStopper $seleniumStopper
     * @param Selenium\SeleniumDownloader $seleniumDownloader
     * @param LogWatcher $seleniumLogWatcher
     */
    public function __construct(
        Selenium\SeleniumStarter $seleniumStarter,
        Selenium\SeleniumStopper $seleniumStopper,
        Selenium\SeleniumDownloader $seleniumDownloader,
        LogWatcher $seleniumLogWatcher
    ) {
        $this->seleniumStarter = $seleniumStarter;
        $this->seleniumStopper = $seleniumStopper;
        $this->seleniumDownloader = $seleniumDownloader;
        $this->seleniumLogWatcher = $seleniumLogWatcher;
    }

    /**
     *
     * @return Selenium\SeleniumStarter
     */
    public function getStarter()
    {
        return $this->seleniumStarter;
    }
    /**
     *
     * @return Selenium\SeleniumStopper
     */
    public function getStopper()
    {
        return $this->seleniumStopper;
    }
    /**
     *
     * @return Selenium\SeleniumDownloader
     */
    public function getDownloader()
    {
        return $this->seleniumDownloader;
    }

    
    public function start()
    {
        $this->seleniumStarter->start();
    }
    public function stop()
    {
        $this->seleniumStopper->stop();
    }
    public function download()
    {
        $this->seleniumDownloader->download();
    }
    public function watch()
    {
        $this->seleniumLogWatcher->watch();
    }
}
