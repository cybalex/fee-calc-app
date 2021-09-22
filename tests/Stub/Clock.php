<?php

declare(strict_types=1);

namespace FeeCalcApp\Stub;

use DateTime;
use FeeCalcApp\Helper\Clock\ClockInterface;

class Clock implements ClockInterface
{
    private ?DateTime $dateTime = null;

    public function getCurrentDateTime(): DateTime
    {
        return $this->dateTime ?? new DateTime();
    }

    public function setCurrentDateTime(DateTime $dateTime): self
    {
        $this->dateTime = $dateTime;

        return $this;
    }
}
