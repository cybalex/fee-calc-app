<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Logger;

use FeeCalcApp\Helper\Clock\ClockInterface;
use FeeCalcApp\Helper\File\FileInfoInterface;
use InvalidArgumentException;
use Psr\Log\AbstractLogger;
use SplFileInfo;

class FileLogger extends AbstractLogger
{
    private SplFileInfo $splFileInfo;

    public function __construct(
        private LogFormatterInterface $logFormatter,
        private string $logFilePath,
        private ClockInterface $clock,
        private FileInfoInterface $fileInfo
    ) {
        $this->splFileInfo = $this->fileInfo->getFileInfo($this->logFilePath);

        if ($this->splFileInfo->isFile() && !$this->splFileInfo->isWritable()) {
            throw new InvalidArgumentException(sprintf('Log file "%s" is not writable', $this->logFilePath));
        }
    }

    public function log($level, $message, array $context = []): void
    {
        $dateTime = $this->clock->getCurrentDateTime();
        $splFile = $this->splFileInfo->openFile('a');
        $splFile->fwrite($this->logFormatter->format($level, $message, $context, $dateTime));
    }
}
