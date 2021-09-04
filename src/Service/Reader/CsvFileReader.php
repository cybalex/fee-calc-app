<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Reader;

use InvalidArgumentException;
use RuntimeException;
use SplFileInfo;

class CsvFileReader implements FileReaderInterface
{
    private const CSV_SEPARATOR = ',';

    public function read(string $filePath): array
    {
        $fileInfo = new SplFileInfo($filePath);

        if (!$fileInfo->isFile()) {
            throw new InvalidArgumentException("The provided file \"$filePath\" is not a valid file");
        }

        if (!$fileInfo->isReadable()) {
            throw new RuntimeException("The provided file \"$filePath\" cannot be read");
        }

        $splFile = $fileInfo->openFile();

        $data = [];
        while (!$splFile->eof()) {
            $row = $splFile->fgetcsv(self::CSV_SEPARATOR);
            if (!empty(current($row))) {
                $data[] = $row;
            }
            $splFile->next();
        }

        return $data;
    }
}
