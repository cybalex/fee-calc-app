<?php

declare(strict_types=1);

namespace FeeCalcApp\Helper;

use DateInterval;

class DatetimeHelper
{
    public function datesAreWithinSameWeek(\DateTime $date1, \DateTime $date2): bool
    {
        $daysAfterMonday = (int) $date1->format('N') - 1;
        $daysBeforeSunday = 7 - (int) $date1->format('N');

        $prevMonday = clone $date1;
        $prevMonday->sub(new DateInterval('P'.$daysAfterMonday.'D'))->setTime(0, 0, 0, 0);

        $nextSunday = clone $date1;
        $nextSunday->add(new DateInterval('P'.$daysBeforeSunday.'D'))->setTime(23, 59, 59, 999);

        return $date2 >= $prevMonday && $date2 <= $nextSunday;
    }
}
