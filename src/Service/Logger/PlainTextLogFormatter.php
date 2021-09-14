<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Logger;

use DateTime;

class PlainTextLogFormatter implements LogFormatterInterface
{
    private string $datetimeFormat;

    public function __construct(string $datetimeFormat)
    {
        $this->datetimeFormat = $datetimeFormat;
    }

    public function format(string $level, string $message, array $context, DateTime $dateTime): string
    {
        return sprintf(
            '[%s] %s %s %s',
            $level,
            $dateTime->format($this->datetimeFormat),
            $message,
            json_encode($context)
        ).PHP_EOL;
    }
}
