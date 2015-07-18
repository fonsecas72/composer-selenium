<?php

namespace BeubiQA\Application\Command;

use BeubiQA\Application\Command\SeleniumCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class GetSeleniumCommand extends SeleniumCommand
{
    protected function configure()
    {
        $this
        ->setName('get')
        ->addOption(
            'selenium-version',
            's',
            InputOption::VALUE_REQUIRED,
            'Set a custom selenium version'
        )
        ->addOption(
            'selenium-destination',
            'd',
            InputOption::VALUE_REQUIRED,
            'Set a custom selenium destination directory'
        )
        ->setDescription('Downloads selenium server');
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getOption('selenium-version') ?: '2.44';
        $destination = $input->getOption('selenium-destination') ?: './';
        $this->updateSelenium($output, $version, $destination);
        $output->writeln("\nDone");
    }

    /**
     *
     * @param string $version e.g. "2.44"
     * @return string
     */
    public function getSeleniumDownloadURL($version)
    {
        return 'http://selenium-release.storage.googleapis.com/'.$version.'/selenium-server-standalone-'.$version.'.0.jar';
    }

    /**
     *
     * @param OutputInterface $output
     * @param string $version e.g. "2.44"
     * @param string $destination
     * @throws \RuntimeException
     * @throws \LogicException
     */
    private function updateSelenium(OutputInterface $output, $version, $destination)
    {
        if (!is_writable(dirname($destination))) {
            throw new \RuntimeException('Not enough permissions in '.$destination.". \nTry with sudo.");
        }
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }
        $outputFile = $destination.'/selenium-server-standalone.jar';
        if (!is_file($outputFile)) {
            $this->downloadFile($this->getSeleniumDownloadURL($version), $outputFile);
        } else {
            $output->write('Skipping download as the file already exists.');
        }

        if (!file_exists($outputFile)) {
            throw new \LogicException('Something wrong happent. The selenium file does not exists. '.$outputFile);
        }
    }

    /**
     *
     * @param OutputInterface $output
     * @param string $url
     * @param string $saveTo
     */
    private function downloadFile($url, $saveTo)
    {
        if ($this->progressBar) {
            $emitter = $this->httpClient->getEmitter();
            $emitter->on('before', function (\GuzzleHttp\Event\BeforeEvent $event) {
                echo $event->getRequest();
            });
            $emitter->once('progress', function (\GuzzleHttp\Event\ProgressEvent $event) {
                $this->progressBar->start($event->downloadSize);
            });
            $emitter->on('progress', function (\GuzzleHttp\Event\ProgressEvent $event) {
                $this->progressBar->setProgress($event->downloaded);
            });
        }

        $this->httpClient->get($url, ['save_to' => $saveTo]);
    }
}
