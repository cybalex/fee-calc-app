<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Calculator\Fee;

use FeeCalcApp\Calculator\Fee\DepositCalculator;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\Math;
use PHPUnit\Framework\TestCase;

class DepositCalculatorTest extends TestCase
{
    private const FEE_RATE = 0.0003;

    private Math $math;

    protected function setUp()
    {
        $this->math = new Math(2);
    }

    public function testCalculate(): void
    {
        $math = $this->createMock(Math::class);
        $math->expects($this->once())->method('mul')
            ->with('100002.00', (string) self::FEE_RATE)
            ->willReturn('30.00');


        $transactionDto = $this->createMock(TransactionDto::class);
        $transactionDto->expects($this->once())->method('getAmount')->with()->willReturn(100002);
        $this->assertEquals('30.00', (new DepositCalculator($math, self::FEE_RATE))->calculate($transactionDto));
    }

    public function transactionTypeProvider(): \Generator
    {
        yield [TransactionDto::OPERATION_TYPE_DEPOSIT, true];
        yield [TransactionDto::OPERATION_TYPE_WITHDRAW, false];
    }
}
