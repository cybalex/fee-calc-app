<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Calculator\Fee;

use DateTime;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCustomCurrencyCalculator;
use FeeCalcApp\Config\CurrencyConfig;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateCacheProxy;
use FeeCalcApp\Service\Math;
use FeeCalcApp\Service\TransactionHistoryManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WithdrawalPrivateCustomCurrencyCalculatorTest extends TestCase
{
    private const USER_ID = 1;

    private const DEFAULT_CURRENCY_CODE = 'USD';
    private const FREE_WITHDRAWAL_WEEKLY_AMOUNT = 1000;
    private const FREE_WITHDRAWALS_WEEKLY = 3;
    private const WITHDRAWAL_FEE_RATE = 0.01;

    private WithdrawalPrivateCustomCurrencyCalculator $calculator;

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

    /**
     * @var CurrencyConfig|MockObject
     */
    private $currencyConfig;


    public function setUp()
    {
        $this->transactionHistoryManager = $this->createMock(TransactionHistoryManager::class);
        $this->math = $this->createMock(Math::class);

        $this->exchangeRateCacheProxy = $this->createMock(ExchangeRateCacheProxy::class);
        $this->currencyConfig = $this->createMock(CurrencyConfig::class);

        $this->calculator = new WithdrawalPrivateCustomCurrencyCalculator(
            $this->math,
            $this->transactionHistoryManager,
            self::WITHDRAWAL_FEE_RATE,
            self::FREE_WITHDRAWALS_WEEKLY,
            $this->exchangeRateCacheProxy,
            $this->currencyConfig,
            self::FREE_WITHDRAWAL_WEEKLY_AMOUNT
        );
    }

    /**
     * @dataProvider transactionDtoProvider
     */
    public function testIsApplicable(
        TransactionDto $transactionDto,
        bool           $transactionHistoryManagerCalled,
        int            $countPrevOperations,
        bool           $getDefaultCurrency,
        bool           $expectedResult
    ): void {
        $this->transactionHistoryManager->expects($transactionHistoryManagerCalled ? $this->once() : $this->never())
            ->method('getUserTransactionsWithinAWeek')->willReturn(
                array_fill(0, $countPrevOperations, $this->createMock(TransactionDto::class))
            );

        $this->currencyConfig
            ->expects($getDefaultCurrency ? $this->once() : $this->never())
            ->method('getDefaultCurrencyCode')
            ->with()
            ->willReturn(self::DEFAULT_CURRENCY_CODE)
        ;

        $this->assertEquals($expectedResult, $this->calculator->isApplicable($transactionDto));
    }

    public function testCalculateDiscountDifferentCurrencies(): void
    {
        $transactionCurrencyCode = CurrencyConfig::EUR_CODE;
        $transactionDto = $this->getApplicableTransaction(1500, CurrencyConfig::EUR_CODE);

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

        $this->currencyConfig
            ->expects($this->exactly(2))
            ->method('getDefaultCurrencyCode')
            ->with()
            ->willReturn(self::DEFAULT_CURRENCY_CODE);

        $this->currencyConfig
            ->expects($this->once())
            ->method('getCurrencyScale')
            ->with(CurrencyConfig::EUR_CODE)
            ->willReturn(0);

        $this->assertEquals('12', $this->calculator->calculateDiscount($transactionDto, '1000'));
    }

    public function transactionDtoProvider(): \Generator
    {
        yield [
            new TransactionDto(
                self::USER_ID,
                TransactionDto::CLIENT_TYPE_PRIVATE,
                new \DateTime('2016-01-05'),
                self::DEFAULT_CURRENCY_CODE,
                300,
                TransactionDto::OPERATION_TYPE_DEPOSIT
            ),
            false,
            2,
            false,
            false
        ];

        yield [
            new TransactionDto(
                self::USER_ID,
                TransactionDto::CLIENT_TYPE_BUSINESS,
                new \DateTime('2016-01-05'),
                self::DEFAULT_CURRENCY_CODE,
                1300,
                TransactionDto::OPERATION_TYPE_WITHDRAW
            ),
            false,
            2,
            false,
            false,
        ];

        yield [
            $this->getApplicableTransaction(13000),
            false,
            3,
            true,
            false,
        ];

        yield [
            $this->getApplicableTransaction(13000, CurrencyConfig::JPY_CODE),
            true,
            2,
            true,
            true,
        ];
        yield [
            $this->getApplicableTransaction(13000),
            false,
            2,
            true,
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
            new DateTime('2016-01-05'),
            $currencyCode ?? self::DEFAULT_CURRENCY_CODE,
            $amount,
            TransactionDto::OPERATION_TYPE_WITHDRAW
        );
    }
}
