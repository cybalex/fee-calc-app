<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Calculator\Fee;

use FeeCalcApp\Calculator\Fee\DepositCalculator;
use FeeCalcApp\DTO\TransactionDto;
use PHPUnit\Framework\TestCase;

class DepositCalculatorTest extends TestCase
{
    /**
     * @dataProvider amountProvider
     */
    public function testCalculate(int $transactionAmount, $expectedFeeAmount): void
    {
        $transactionDto = $this->createMock(TransactionDto::class);
        $transactionDto->expects($this->once())->method('getAmount')->with()->willReturn($transactionAmount);
        $this->assertEquals($expectedFeeAmount, (new DepositCalculator())->calculate($transactionDto));
    }

    public function amountProvider(): \Generator
    {
        yield [100000, 30];
        yield [100001, 31];
        yield [100002, 31];
    }

    /**
     * @dataProvider transactionTypeProvider
     */
    public function testApplicable(string $operationType, bool $expectedResult): void
    {
        $depositCalculator = new DepositCalculator();
        $transactionDto = $this->createMock(TransactionDto::class);
        $transactionDto->expects($this->once())->method('getOperationType')->with()->willReturn($operationType);
        $this->assertEquals($expectedResult, $depositCalculator->isApplicable($transactionDto));
    }

    public function transactionTypeProvider(): \Generator
    {
        yield [TransactionDto::OPERATION_TYPE_DEPOSIT, true];
        yield [TransactionDto::OPERATION_TYPE_WITHDRAW, false];
    }
}
