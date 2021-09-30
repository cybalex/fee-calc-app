<?php

declare(strict_types=1);

namespace FeeCalcApp\Service;

class Math
{
    private int $scale;

    public function __construct(int $scale)
    {
        $this->scale = $scale;
    }

    public function add(string $leftOperand, string $rightOperand): string
    {
        return bcadd($leftOperand, $rightOperand, $this->scale);
    }

    public function sub(string $num1, string $num2): string
    {
        return bcsub($num1, $num2);
    }

    public function div(string $num1, string $num2): string
    {
        return bcdiv($num1, $num2, $this->scale);
    }

    public function mul(string $num1, string $num2): string
    {
        return bcmul($num1, $num2, $this->scale);
    }

    public function min(string $num1, string $num2): string
    {
        $comp = bccomp($num1, $num2, $this->scale);

        return $comp === 1 ? $num2 : $num1;
    }

    public function max(string $num1, string $num2): string
    {
        $comp = bccomp($num1, $num2, $this->scale);

        return $comp === 1 ? $num1 : $num2;
    }

    public function floor(string $num): string
    {
        return bcadd($num, '0', 0);
    }

    public function getScale()
    {
        return $this->scale;
    }
}
