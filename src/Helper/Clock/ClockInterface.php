<?php

declare(strict_types=1);

namespace FeeCalcApp\Helper\Clock;

use DateTime;

interface ClockInterface
{
    public function getCurrentDateTime(): DateTime;
}
