<?php

namespace FeeCalcApp\Helper\Clock;

use DateTime;

interface ClockInterface
{
    public function getCurrentDateTime(): DateTime;
}
