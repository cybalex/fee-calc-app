<?php

declare(strict_types=1);

namespace FeeCalcApp\Helper\File;

use SplFileInfo;

interface FileInfoInterface
{
    public function getFileInfo(string $filePath): SplFileInfo;
}
