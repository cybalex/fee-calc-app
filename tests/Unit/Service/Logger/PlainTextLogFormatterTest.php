<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Service\Logger;

use DateTime;
use FeeCalcApp\Service\Logger\PlainTextLogFormatter;
use PHPUnit\Framework\TestCase;

class PlainTextLogFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $level = 'info';
        $message = 'Something went wrong';
        $context = ['some context'];
        $dateTime = new DateTime('2021-09-16 10:19:24');

        $expectedResult = <<<TEXT
[info] 2021-09-16 10:19:24 Something went wrong ["some context"]

TEXT;

        $plainTextLogFormatter = new PlainTextLogFormatter('Y-m-d H:i:s');
        $actualResult = $plainTextLogFormatter->format($level, $message, $context, $dateTime);
        $this->assertEquals($expectedResult, $actualResult);
    }
}
