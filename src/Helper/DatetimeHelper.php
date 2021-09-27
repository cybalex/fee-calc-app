<?php

declare(strict_types=1);

namespace FeeCalcApp\Helper;

use DateTime;

class DatetimeHelper
{
    public function datesAreWithinSameWeek(DateTime $date1, DateTime $date2): bool
    {
        return $date1->format('oW') === $date2->format('oW');
    }
}
