<?php

declare(strict_types=1);

namespace FeeCalcApp\Helper\File;

use SplFileInfo;

class FileInfo implements FileInfoInterface
{
    public function getFileInfo(string $filePath): SplFileInfo
    {
        return new SplFileInfo($filePath);
    }
}
