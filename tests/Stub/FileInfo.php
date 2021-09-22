<?php

declare(strict_types=1);

namespace FeeCalcApp\Stub;

use FeeCalcApp\Helper\File\FileInfoInterface;
use SplFileInfo;

class FileInfo implements FileInfoInterface
{
    private SplFileInfo $fileInfo;

    public function getFileInfo(string $filePath): SplFileInfo
    {
        return $this->fileInfo;
    }

    public function setFileInfo(SplFileInfo $fileInfo): self
    {
        $this->fileInfo = $fileInfo;

        return $this;
    }
}
