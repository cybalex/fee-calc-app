<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Reader;

use LogicException;
use RuntimeException;
use SplFileObject;

class CsvFileReader implements FileReaderInterface
{
    private const CSV_SEPARATOR = ',';

    public function read(string $filePath): array
    {
        try {
            $splFile = new SplFileObject($filePath);

            $data = [];
            while (!$splFile->eof()) {
                $row = $splFile->fgetcsv(self::CSV_SEPARATOR);
                if (!empty(current($row))) {
                    $data[] = $row;
                }
                $splFile->next();
            }

            return $data;
        } catch (RuntimeException|LogicException $e) {
            echo "Problem opening or reading csv file ${filePath}";
            exit;
        }
    }
}
