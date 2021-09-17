<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Calculator\Fee;

use DateTime;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCustomCurrencyCalculator;
use FeeCalcApp\Config\CurrencyConfig;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateHttpClient;
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
     * @var ExchangeRateHttpClient|MockObject
     */
    private $exchangeRateClient;

    /**
     * @var CurrencyConfig|MockObject
     */
    private $currencyConfig;


    public function setUp()
    {
        $this->transactionHistoryManager = $this->createMock(TransactionHistoryManager::class);
        $this->math = $this->createMock(Math::class);

        $this->exchangeRateClient = $this->createMock(ExchangeRateHttpClient::class);
        $this->currencyConfig = $this->createMock(CurrencyConfig::class);

        $this->calculator = new WithdrawalPrivateCustomCurrencyCalculator(
            $this->math,
            $this->transactionHistoryManager,
            self::WITHDRAWAL_FEE_RATE,
            self::FREE_WITHDRAWALS_WEEKLY,
            $this->exchangeRateClient,
            $this->currencyConfig,
            self::FREE_WITHDRAWAL_WEEKLY_AMOUNT
        );
    }

    public function testCalculateDiscountDifferentCurrencies(): void
    {
        $transactionCurrencyCode = CurrencyConfig::EUR_CODE;
        $transactionDto = $this->getApplicableTransaction(1500, CurrencyConfig::EUR_CODE);
        $this->transactionHistoryManager->expects($this->once())->method('getUserTransactionsTotalAmount')
            ->with([], self::DEFAULT_CURRENCY_CODE)->willReturn('0');

        $this->math->expects($this->once())->method('sub')->with('1000', '0')->willReturn('1000');
        $this->math->expects($this->once())->method('max')->with('1000', '0')->willReturn('1000.00');
        $this->exchangeRateClient->expects($this->once())->method('getExchangeRate')
            ->with(self::DEFAULT_CURRENCY_CODE, $transactionCurrencyCode)
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
