<?php

namespace BeubiQA\Application\Selenium;

use GuzzleHttp\Client;
use Symfony\Component\Console\Helper\ProgressBar;

class SeleniumDownloader
{
    /** @var Client */
    protected $httpClient;
    
    /** @var ProgressBar */
    protected $progressBar;
    
    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function setProgressBar(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }

    public function download($options)
    {
        if (!is_writable(dirname($options['selenium-destination']))) {
            throw new \RuntimeException(
                'Not enough permissions in '.$options['selenium-destination'].". \nTry with sudo."
            );
        }
        if (!is_dir($options['selenium-destination'])) {
            mkdir($options['selenium-destination'], 0777, true);
        }
        $outputFile = $options['selenium-destination'].'selenium-server-standalone.jar';
        if (is_file($outputFile)) {
            throw new \RuntimeException('File already exists. '.$outputFile);
        }

        $downloadUrl = $this->getSeleniumDownloadURL($options['selenium-version']);
        $this->downloadFile($downloadUrl, $outputFile);

        if (!file_exists($outputFile)) {
            throw new \RuntimeException('Something wrong happent. The selenium file does not exists. '.$outputFile);
        }
    }
    
    /**
     *
     * @param string $version e.g. "2.44"
     * @return string
     */
    private function getSeleniumDownloadURL($version)
    {
        // TODO: match version with a list of valid versions and add a checksum later
        $server = 'http://selenium-release.storage.googleapis.com';
        $filename = 'selenium-server-standalone-'.$version.'.0.jar';
        return $server.'/'.$version.'/'.$filename;
    }

    /**
     *
     * @param string $url
     * @param string $saveTo
     */
    private function downloadFile($url, $saveTo)
    {
        if ($this->progressBar) {
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

        $this->httpClient->get($url, ['save_to' => $saveTo]);
    }
}
