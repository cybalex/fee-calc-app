<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Reader;

interface FileReaderInterface
{
    public function read(string $filePath): array;
}
