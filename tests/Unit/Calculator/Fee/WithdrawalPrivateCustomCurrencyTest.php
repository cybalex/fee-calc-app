<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Calculator\Fee;

use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCustomCurrencyCalculator;
use FeeCalcApp\DTO\Currency;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateCacheProxy;
use FeeCalcApp\Service\Math;
use FeeCalcApp\Service\TransactionHistoryManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WithdrawalPrivateCustomCurrencyTest extends TestCase
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

    /**
     * @var ExchangeRateCacheProxy|MockObject
     */
    private $exchangeRateCacheProxy;


    public function setUp()
    {
        $this->transactionHistoryManager = $this->createMock(TransactionHistoryManager::class);
        $this->math = $this->createMock(Math::class);

        $this->exchangeRateCacheProxy = $this->createMock(ExchangeRateCacheProxy::class);

        $this->calculator = new WithdrawalPrivateCustomCurrencyCalculator(
            $this->math,
            $this->transactionHistoryManager,
            self::WITHDRAWAL_FEE_RATE,
            self::FREE_WITHDRAWALS_WEEKLY,
            $this->exchangeRateCacheProxy,
            self::DEFAULT_CURRENCY_CODE,
            self::FREE_WITHDRAWAL_WEEKLY_AMOUNT
        );
    }

    /**
     * @dataProvider transactionDtoProvider
     */
    public function testIsApplicable(
        TransactionDto $transactionDto,
        bool $transactionHistoryManagerCalled,
        int $countPrevOperations,
        bool $expectedResult
    ): void {
        $this->transactionHistoryManager->expects($transactionHistoryManagerCalled ? $this->once() : $this->never())
            ->method('getUserTransactionsWithinAWeek')->willReturn(
                array_fill(0, $countPrevOperations, $this->createMock(TransactionDto::class))
            );

        $this->assertEquals($expectedResult, $this->calculator->isApplicable($transactionDto));
    }

    public function testCalculateDiscountDifferentCurrencies(): void
    {
        $transactionCurrencyCode = Currency::EUR_CODE;
        $transactionDto = $this->getApplicableTransaction(1500, Currency::EUR_CODE);

        $this->transactionHistoryManager->expects($this->once())->method('getUserTransactionsTotalAmount')
            ->with(null, self::DEFAULT_CURRENCY_CODE)->willReturn('0');

        $this->math->expects($this->once())->method('sub')->with('1000', '0')->willReturn('1000');
        $this->math->expects($this->once())->method('max')->with('1000', '0')->willReturn('1000.00');
        $this->exchangeRateCacheProxy->expects($this->once())->method('getExchangeRateForDate')
            ->with($transactionDto->getDate(), self::DEFAULT_CURRENCY_CODE, $transactionCurrencyCode)
            ->willReturn('1.2');
        $this->math->expects($this->exactly(2))->method('mul')
            ->withConsecutive(['0.01', '1000'], ['10', '1.2'])->willReturnOnConsecutiveCalls('10', '12');
        $this->math->expects($this->once())->method('div')->with('12', '1')->willReturn('12');
        $this->math->expects($this->once())->method('min')
            ->with('12', '1000')->willReturn('12');
        $this->math->expects($this->once())->method('floor')->with('12')->willReturn('12');

        $this->assertEquals('12', $this->calculator->calculateDiscount($transactionDto, '1000'));
    }

    public function transactionDtoProvider(): \Generator
    {
        yield [
            new TransactionDto(
                self::USER_ID,
                TransactionDto::CLIENT_TYPE_PRIVATE,
                new \DateTime('2016-01-05'),
                $this->getCurrency(),
                300,
                TransactionDto::OPERATION_TYPE_DEPOSIT
            ),
            false,
            2,
            false
        ];

        yield [
            new TransactionDto(
                self::USER_ID,
                TransactionDto::CLIENT_TYPE_BUSINESS,
                new \DateTime('2016-01-05'),
                $this->getCurrency(),
                1300,
                TransactionDto::OPERATION_TYPE_WITHDRAW
            ),
            false,
            2,
            false,
        ];

        yield [
            $this->getApplicableTransaction(13000),
            false,
            3,
            false,
        ];

        yield [
            $this->getApplicableTransaction(13000, Currency::JPY_CODE),
            true,
            2,
            true,
        ];
        yield [
            $this->getApplicableTransaction(13000),
            false,
            2,
            false,
        ];
    }

    private function getApplicableTransaction(
        int $amount,
        ?string $currencyCode = null
    ): TransactionDto {
        return new TransactionDto(
            self::USER_ID,
            TransactionDto::CLIENT_TYPE_PRIVATE,
            new \DateTime('2016-01-05'),
            $this->getCurrency($currencyCode ?? self::DEFAULT_CURRENCY_CODE),
            $amount,
            TransactionDto::OPERATION_TYPE_WITHDRAW
        );
    }

    private function getCurrency(string $currencyCode = self::DEFAULT_CURRENCY_CODE): Currency
    {
        return new Currency($currencyCode);
    }
}
