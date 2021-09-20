<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Logger;

use FeeCalcApp\Helper\Clock\ClockInterface;
use FeeCalcApp\Helper\File\FileInfoInterface;
use Psr\Log\AbstractLogger;
use RuntimeException;
use SplFileInfo;

class FileLogger extends AbstractLogger
{
    private LogFormatterInterface $logFormatter;
    private string $logFilePath;
    private ClockInterface $clock;
    private FileInfoInterface $fileInfo;

    public function __construct(
        LogFormatterInterface $logFormatter,
        string                $logFilePath,
        ClockInterface        $clockInterface,
        FileInfoInterface     $fileInfo
    ) {
        $this->logFormatter = $logFormatter;
        $this->logFilePath = $logFilePath;
        $this->clock = $clockInterface;
        $this->fileInfo = $fileInfo;
    }

    public function log($level, $message, array $context = [])
    {
        $dateTime = $this->clock->getCurrentDateTime();
        $splFile = $this->openLogFileForWrite();
        $splFile->fwrite($this->logFormatter->format($level, $message, $context, $dateTime));
    }

    private function openLogFileForWrite(): SplFileInfo
    {
        $splFileInfo = $this->fileInfo->getFileInfo($this->logFilePath);

        if ($splFileInfo->isFile() && !$splFileInfo->isWritable()) {
            throw new RuntimeException(sprintf('Log file "%s" is not writable', $this->logFilePath));
        }

        return $splFileInfo->openFile('a');
    }
}
