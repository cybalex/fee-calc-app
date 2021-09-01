<?php

declare(strict_types=1);

namespace FeeCalcApp\Service;

use FeeCalcApp\Calculator\FeeCalculatorInterface;

class FeeCalculatorCollection
{
    /**
     * @var FeeCalculatorInterface[]
     */
    private array $feeCalculators = [];

    public function get(): array
    {
        return $this->feeCalculators;
    }

    public function add(FeeCalculatorInterface $feeCalculator): self
    {
        $this->feeCalculators[get_class($feeCalculator)] = $feeCalculator;

        return $this;
    }
}
