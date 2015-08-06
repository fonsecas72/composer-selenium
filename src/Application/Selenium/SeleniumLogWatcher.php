<?php

namespace BeubiQA\Application\Selenium;

class SeleniumLogWatcher
{
    /**
     *
     * @param string $file
     */
    public function watch($options)
    {
        $file = $options['log-location'];
        $this->checkLogPermissions($file);
        $size = 0;
        while (true) {
            clearstatcache();
            $currentSize = filesize($file);
            if ($size === $currentSize) {
                usleep(500);
                continue;
            }
            $fh = fopen($file, 'r');
            fseek($fh, $size);
            while ($line = fgets($fh)) {
                if (strpos($line, $options['follow']) !== false) {
                    echo $line;
                }
            }
            fclose($fh);
            $size = $currentSize;
        }
    }
    private function checkLogPermissions($file)
    {
        if (file_exists($file) && !is_writable($file)) {
            throw new \RuntimeException('No permissions in '.$file);
        }
    }
}
