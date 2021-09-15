<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Calculator\Fee;

use DateTime;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCalculator;
use FeeCalcApp\Config\CurrencyConfig;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\Math;
use FeeCalcApp\Service\TransactionHistoryManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WithdrawalPrivateCalculatorTest extends TestCase
{
    private const USER_ID = 1;

    private const DEFAULT_CURRENCY_CODE = 'USD';
    private const FREE_WITHDRAWAL_WEEKLY_AMOUNT = 1000;
    private const FREE_WITHDRAWALS_WEEKLY = 3;
    private const WITHDRAWAL_FEE_RATE = 0.01;

    private WithdrawalPrivateCalculator $calculator;

    /**
     * @var TransactionHistoryManager|MockObject
     */
    private TransactionHistoryManager $transactionHistoryManager;

    /**
     * @var Math|MockObject
     */
    private $math;

    private array $constructorArgs;

    /**
     * @var CurrencyConfig|MockObject
     */
    private $currencyConfig;


    public function setUp()
    {
        $this->transactionHistoryManager = $this->createMock(TransactionHistoryManager::class);
        $this->math = $this->createMock(Math::class);
        $this->currencyConfig = $this->createMock(CurrencyConfig::class);

        $this->constructorArgs = [
            $this->math,
            $this->transactionHistoryManager,
            self::WITHDRAWAL_FEE_RATE,
            self::FREE_WITHDRAWALS_WEEKLY,
            $this->currencyConfig,
            self::FREE_WITHDRAWAL_WEEKLY_AMOUNT,
        ];

        $this->calculator = new WithdrawalPrivateCalculator(...$this->constructorArgs);
    }

    public function testCalculate(): void
    {
        $amount = 1300;
        $discount = '1.00';
        $transactionDto = $this->getApplicableTransaction($amount);

        $calculator = $this
            ->getMockBuilder(WithdrawalPrivateCalculator::class)
            ->setConstructorArgs($this->constructorArgs)
            ->setMethods(['calculateDiscount'])
            ->getMock();

        $calculator
            ->expects($this->once())
            ->method('calculateDiscount')
            ->with($transactionDto)
            ->willReturn($discount);

        $this->math
            ->expects($this->once())
            ->method('mul')
            ->with((string) $amount, (string) self::WITHDRAWAL_FEE_RATE)
            ->willReturn('10.50');

        $this->math->expects($this->once())->method('sub')->with('10.50', $discount)
            ->willReturn('9.50');

        $this->currencyConfig
            ->expects($this->never())
            ->method('getDefaultCurrencyCode');

        $this->assertEquals('9.50', $calculator->calculate($transactionDto));
    }

    public function testCalculateDiscountSameCurrency(): void
    {
        $amount = 1100;
        $maxFeeInCurrency = '10.00';
        $transactionDto = $this->getApplicableTransaction($amount);

        $historyTransaction = $this->createMock(TransactionDto::class);


        $this->transactionHistoryManager->expects($this->once())->method('getUserTransactionsWithinAWeek')
            ->with($transactionDto)->willReturn([$historyTransaction]);

        $this->transactionHistoryManager->expects($this->once())->method('getUserTransactionsTotalAmount')
            ->with([$historyTransaction], self::DEFAULT_CURRENCY_CODE)->willReturn('900.00');

        $this->math->expects($this->once())
            ->method('sub')
            ->with('1000', '900.00')
            ->willReturn('100.00');

        $this->math->expects($this->once())->method('mul')->with('0.01', '100.00')->willReturn('1.00');
        $this->math->expects($this->once())->method('max')->with('100.00', '0')->willReturn('100.00');
        $this->math->expects($this->once())->method('min')->with('1.00', $maxFeeInCurrency)->willReturn('1.00');
        $this->math->expects($this->once())->method('floor')->with('1.00')->willReturn('1');

        $this->currencyConfig
            ->expects($this->once())
            ->method('getDefaultCurrencyCode')
            ->willReturn(self::DEFAULT_CURRENCY_CODE);

        $this->assertEquals('1', $this->calculator->calculateDiscount($transactionDto, $maxFeeInCurrency));
    }

    private function getApplicableTransaction(int $amount): TransactionDto
    {
        return new TransactionDto(
            self::USER_ID,
            TransactionDto::CLIENT_TYPE_PRIVATE,
            new DateTime('2016-01-05'),
            self::DEFAULT_CURRENCY_CODE,
            $amount,
            TransactionDto::OPERATION_TYPE_WITHDRAW
        );
    }
}
