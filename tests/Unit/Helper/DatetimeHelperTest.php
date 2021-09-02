<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Helper;

use FeeCalcApp\Helper\DatetimeHelper;
use PHPUnit\Framework\TestCase;

class DatetimeHelperTest extends TestCase
{
    /**
     * @dataProvider dateTimeProvider
     * Date strings should be provided in 'Y-m-d' format
     */
    public function testDatesAreWithinSameWeek(string $date1, string $date2, bool $result): void
    {
        $date1 = new \DateTime($date1);
        $date2 = new \DateTime($date2);

        $dateTimeHelper = new DateTimeHelper();

        $this->assertEquals($result, $dateTimeHelper->datesAreWithinSameWeek($date1, $date2));
        $this->assertEquals($result, $dateTimeHelper->datesAreWithinSameWeek($date2, $date1));
    }

    public function dateTimeProvider(): \Generator
    {
        yield ['2014-12-31', '2015-01-01', true];
        yield ['2016-01-06', '2016-01-07', true];
        yield ['2016-01-06', '2016-01-13', false];
        yield ['2014-01-03', '2015-01-03', false];
        yield ['2014-01-03', '2014-01-03', true];
        yield ['2021-08-30', '2021-09-05', true];
        yield ['2021-08-30', '2021-09-06', false];
        yield ['2016-01-10', '2016-01-07', true];
    }
}
