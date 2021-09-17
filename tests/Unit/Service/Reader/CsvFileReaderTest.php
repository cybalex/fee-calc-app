<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Service\Reader;

use FeeCalcApp\Service\Reader\CsvFileReader;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CsvFileReaderTest extends TestCase
{
    public function testReadDoesNotExist(): void
    {
        $filePath = 'this_file_does_not_exist.txt';
        $csvFileReader = new CsvFileReader();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The provided file \"this_file_does_not_exist.txt\" is not a valid file");

        $csvFileReader->read($filePath);
    }
}
