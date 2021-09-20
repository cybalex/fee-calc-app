<?php

namespace FeeCalcApp\Helper\File;

use SplFileInfo;

interface FileInfoInterface
{
    public function getFileInfo(string $filePath): SplFileInfo;
}
