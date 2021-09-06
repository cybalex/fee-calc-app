<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Logger;

use Psr\Log\AbstractLogger;

class FileLogger extends AbstractLogger
{
    private LogFormatterInterface $logFormatter;
    private string $logFile;

    public function __construct(LogFormatterInterface $logFormatter, string $logFile)
    {
        $this->logFormatter = $logFormatter;
        $this->logFile = $logFile;
    }

    public function log($level, $message, array $context = [])
    {
        $dateTime = new \DateTime();
        $splFile = $this->openLogFileForWrite();
        $splFile->fwrite($this->logFormatter->format($level, $message, $context, $dateTime));
    }

    private function openLogFileForWrite(): \SplFileInfo
    {
        $splFileInfo = new \SplFileInfo($this->logFile);

        if (!$splFileInfo->isFile()) {
            throw new \RuntimeException(sprintf('Log file "%s" location is not a valid file', $this->logFile));
        }

        if (!$splFileInfo->isWritable()) {
            throw new \RuntimeException(sprintf('Log file "%s" is not writable', $this->logFile));
        }

        return $splFileInfo->openFile('a');
    }
}
