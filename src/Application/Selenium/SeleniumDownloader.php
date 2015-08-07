<?php

namespace BeubiQA\Application\Selenium;

use GuzzleHttp\Client;
use Symfony\Component\Console\Helper\ProgressBar;
use BeubiQA\Application\Selenium\Options\SeleniumDownloaderOptions;

class SeleniumDownloader
{
    /** @var Client */
    protected $httpClient;
    /** @var ProgressBar */
    protected $progressBar;
    /** @var SeleniumDownloaderOptions */
    protected $seleniumOptions;
    public function __construct(SeleniumDownloaderOptions $seleniumOptions, Client $httpClient)
    {
        $this->seleniumOptions = $seleniumOptions;
        $this->httpClient = $httpClient;
    }
    public function setProgressBar(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }

    public function download()
    {
        $destinationPath = $this->seleniumOptions->getSeleniumDestination();
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        if (!is_writable($destinationPath)) {
            throw new \RuntimeException(
                'Not enough permissions in '.$destinationPath.". \nTry with sudo."
            );
        }
        $outputFile = $destinationPath.'/selenium-server-standalone.jar';
        if (is_file($outputFile)) {
            throw new \RuntimeException('File already exists. '.$outputFile);
        }

        $this->downloadFile($this->seleniumOptions->getSeleniumDownloadUrl(), $outputFile);

        if (!file_exists($outputFile)) {
            throw new \RuntimeException('Something wrong happent. The selenium file does not exists. '.$outputFile);
        }
    }
    
    /**
     *
     * @param string $url
     * @param string $saveTo
     */
    private function downloadFile($url, $saveTo)
    {
        if ($this->progressBar) {
            $this->setDownloadWithProgressBar();
        }
        $this->httpClient->get($url, ['save_to' => $saveTo]);
    }
    
    private function setDownloadWithProgressBar()
    {
        $emitter = $this->httpClient->getEmitter();
        $emitter->on('before', function(\GuzzleHttp\Event\BeforeEvent $event) {
            echo $event->getRequest();
        });
        $emitter->once('progress', function(\GuzzleHttp\Event\ProgressEvent $event) {
            $this->progressBar->start($event->downloadSize);
        });
        $emitter->on('progress', function(\GuzzleHttp\Event\ProgressEvent $event) {
            $this->progressBar->setProgress($event->downloaded);
        });
    }
    /**
     *
     * @return SeleniumDownloaderOptions
     */
    public function getDownloaderOptions()
    {
        return $this->seleniumOptions;
    }
}
