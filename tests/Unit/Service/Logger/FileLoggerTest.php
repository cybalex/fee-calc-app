<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Service\Logger;

use DateTime;
use FeeCalcApp\Helper\File\FileInfoInterface;
use FeeCalcApp\Service\Logger\FileLogger;
use FeeCalcApp\Service\Logger\LogFormatterInterface;
use FeeCalcApp\Stub\Clock;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use SplTempFileObject;

class FileLoggerTest extends TestCase
{
    private const LOG_LEVEL = 'info';
    private const LOG_TEXT = 'some log info';
    private const LOG_CONTEXT = ['some helpful data'];
    private const LOG_FILE_PATH = 'some_dummy_file_path';

    public function testLog(): void
    {
        $dateTime = new DateTime();
        $formattedData = 'formatted log data';

        $logFormatter = $this->createMock(LogFormatterInterface::class);
        $logFormatter
            ->expects($this->once())
            ->method('format')
            ->with(self::LOG_LEVEL, self::LOG_TEXT, self::LOG_CONTEXT, $dateTime)
            ->willReturn($formattedData);
        $clock = new Clock();
        $clock->setCurrentDateTime($dateTime);

        $fileInfoMock = $this->createMock(SplFileInfo::class);
        $fileInfoMock->expects($this->once())->method('isFile')->willReturn(true);
        $fileInfoMock->expects($this->once())->method('isWritable')->willReturn(true);
        $tmpFile = new SplTempFileObject();
        $fileInfoMock->expects($this->once())->method('openFile')->with('a')->willReturn($tmpFile);

        $fileInfo = $this->createMock(FileInfoInterface::class);
        $fileInfo->expects($this->once())->method('getFileInfo')->with(self::LOG_FILE_PATH)
            ->willReturn($fileInfoMock);

        $fileLogger = new FileLogger(
            $logFormatter,
            self::LOG_FILE_PATH,
            $clock,
            $fileInfo
        );

        $fileLogger->log(self::LOG_LEVEL, self::LOG_TEXT, self::LOG_CONTEXT);

        $tmpFile->rewind();
        $this->assertEquals($formattedData, $tmpFile->fgets());
    }

    public function testInvalidArgumentException() {
        $logFormatter = $this->createMock(LogFormatterInterface::class);

        $fileInfoMock = $this->createMock(SplFileInfo::class);
        $fileInfoMock->expects($this->once())->method('isFile')->willReturn(true);
        $fileInfoMock->expects($this->once())->method('isWritable')->willReturn(false);
        $fileInfoMock->expects($this->never())->method('openFile');

        $fileInfo = $this->createMock(FileInfoInterface::class);
        $fileInfo->expects($this->once())->method('getFileInfo')->with(self::LOG_FILE_PATH)
            ->willReturn($fileInfoMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Log file \"" . self::LOG_FILE_PATH . "\" is not writable");

        new FileLogger(
            $logFormatter,
            self::LOG_FILE_PATH,
            new Clock(),
            $fileInfo
        );
    }
}
