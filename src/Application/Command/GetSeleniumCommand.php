<?php

namespace BeubiQA\Application\Command;

use BeubiQA\Application\Command\SeleniumCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;

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
        $this->downloadFile($output, $this->getSeleniumDownloadURL($version), $outputFile);
        $output->writeln('Done');

        if (!file_exists($outputFile)) {
            throw new \LogicException('Something wrong happent. The selenium file does not exists. '.$outputFile);
        }
    }

    /**
     *
     * @param OutputInterface $output
     * @param string $url
     * @param string $outputFile
     */
    private function downloadFile(OutputInterface $output, $url, $outputFile)
    {
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "Content-type: application/force-download",
            )
        );
        $progress = new ProgressBar($output, 35000000); // ~ 35Mb
        $ctx = stream_context_create(
            $opts,
            array('notification' =>
                function($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) use ($output, $progress) {
                    switch ($notification_code) {
                        case STREAM_NOTIFY_FILE_SIZE_IS:
                            $progress->start($bytes_max);
                            break;
                        case STREAM_NOTIFY_PROGRESS:
                            $progress->setProgress($bytes_transferred);
                            break;
                    }
                }
            )
        );
        file_put_contents($outputFile, file_get_contents($url, false, $ctx));
        $progress->finish();
    }
}
