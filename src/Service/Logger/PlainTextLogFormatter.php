<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Logger;

use DateTime;

class PlainTextLogFormatter implements LogFormatterInterface
{
    public function format(string $level, string $message, array $context, DateTime $dateTime): string
    {
        return sprintf(
            '[%s] %s %s %s',
            $level,
            $dateTime->format('Y-m-d H:i:s.SSS'),
            $message,
            json_encode($context)
        ).PHP_EOL;
    }
}
