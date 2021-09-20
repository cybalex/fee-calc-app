<?php

declare(strict_types=1);

namespace FeeCalcApp\Helper\Clock;

use DateTime;

class Clock implements ClockInterface
{
    public function getCurrentDateTime(): DateTime
    {
        return new DateTime();
    }
}
