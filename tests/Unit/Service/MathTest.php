<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Service;

use PHPUnit\Framework\TestCase;
use FeeCalcApp\Service\Math;

class MathTest extends TestCase
{
    private Math $math;

    public function setUp(): void
    {
        $this->math = new Math(2);
    }

    /**
     * @dataProvider dataProviderForAddTesting
     */
    public function testAdd(string $leftOperand, string $rightOperand, string $expectation): void
    {
        $this->assertEquals(
            $expectation,
            $this->math->add($leftOperand, $rightOperand)
        );
    }

    public function dataProviderForAddTesting(): array
    {
        return [
            'add 2 natural numbers' => ['1', '2', '3.00'],
            'add negative number to a positive' => ['-1', '2', '1.00'],
            'add natural number to a float' => ['1', '1.05123', '2.05'],
        ];
    }

    /**
    * @dataProvider dataProviderForAubTesting
    */
    public function testSub(string $num1, string $num2, string $expectation): void
    {
        $this->assertEquals(
            $expectation,
            $this->math->sub($num1, $num2)
        );
    }

    public function dataProviderForAubTesting(): array
    {
        return [
            'subtract 2 natural numbers' => ['1', '2', '-1'],
            'subtract positive number from negative number' => ['-1', '2', '-3'],
            'subtract float number from natural number' => ['1', '1.05123', '0'],
        ];
    }
}
