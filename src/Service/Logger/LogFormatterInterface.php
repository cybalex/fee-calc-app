<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Logger;

use DateTime;

interface LogFormatterInterface
{
    public function format(string $level, string $message, array $context, DateTime $dateTime): string;
}
