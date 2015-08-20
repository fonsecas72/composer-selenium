<?php

namespace BeubiQA\Application\Lib;

class LogWatcher
{
    /**
     * @param array $logPath
     */
    public function watch($logPath, $stringToEcho)
    {
        $this->checkLogPermissions($logPath);
        $size = 0;
        while (true) {
            clearstatcache();
            $currentSize = filesize($logPath);
            if ($size === $currentSize) {
                usleep(500);
                continue;
            }
            $fh = fopen($logPath, 'r');
            fseek($fh, $size);
            while ($line = fgets($fh)) {
                if (strpos($line, $stringToEcho) !== false) {
                    echo $line;
                }
            }
            fclose($fh);
            $size = $currentSize;
        }
    }
    /**
     * @param string $file
     *
     * @throws \RuntimeException
     */
    private function checkLogPermissions($file)
    {
        if (file_exists($file) && !is_writable($file)) {
            throw new \RuntimeException('No permissions in '.$file);
        }
    }
}
