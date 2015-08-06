<?php

namespace BeubiQA\Application\Selenium;

use BeubiQA\Application\Selenium\SeleniumStarter;
use BeubiQA\Application\Selenium\SeleniumStopper;
use BeubiQA\Application\Selenium\SeleniumDownloader;
use BeubiQA\Application\Selenium\SeleniumLogWatcher;

class SeleniumHandler
{
    /** @var SeleniumStarter  */
    protected $seleniumStarter;
    /** @var SeleniumStopper  */
    protected $seleniumStopper;
    /** @var SeleniumDownloader  */
    protected $seleniumDownloader;
    /** @var SeleniumLogWatcher  */
    protected $seleniumLogWatcher;

    public function __construct(
        SeleniumStarter $seleniumStarter,
        SeleniumStopper $seleniumStopper,
        SeleniumDownloader $seleniumDownloader,
        SeleniumLogWatcher $seleniumLogWatcher
    ) {
        $this->seleniumStarter = $seleniumStarter;
        $this->seleniumStopper = $seleniumStopper;
        $this->seleniumDownloader = $seleniumDownloader;
        $this->seleniumLogWatcher = $seleniumLogWatcher;
    }

    public function start($options)
    {
        return $this->seleniumStarter->start($options);
    }
    public function stop($options)
    {
        return $this->seleniumStopper->stop($options);
    }
    public function download($options)
    {
        return $this->seleniumDownloader->download($options);
    }
    public function watch($options)
    {
        return $this->seleniumLogWatcher->watch($options);
    }
}
