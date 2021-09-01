<?php

declare(strict_types=1);

namespace FeeCalcApp\Helper;

class DatetimeHelper
{
    public static function datesAreWithinSameWeek(\DateTime $date1, \DateTime $date2): bool
    {
        $daysAfterMonday = (int) $date1->format('N') - 1;
        $daysBeforeSunday = 7 - (int) $date1->format('N');

        $prevMonday = clone $date1;
        $prevMonday->sub(new \DateInterval('P'.$daysAfterMonday.'D'));

        $nextSunday = clone $date1;
        $nextSunday->add(new \DateInterval('P'.$daysBeforeSunday.'D'));

        return $date2 >= $prevMonday && $date2 <= $nextSunday;
    }
}
