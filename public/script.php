<?php

require_once('./vendor/autoload.php');

use FeeCalcApp\Calculator\Fee\DepositCalculator;
use FeeCalcApp\Exception\TransactionException;
use FeeCalcApp\Helper\DatetimeHelper;
use FeeCalcApp\Calculator\Fee\{
    WithdrawalBusinessCalculator,
    WithdrawalPrivateCalculator,
    WithdrawalPrivateNoDiscountCalculator
};
use FeeCalcApp\Service\ExchangeRate\{ExchangeRateCacheProxy, ExchangeRateClient};
use FeeCalcApp\Service\FeeCalculatorCollection;
use FeeCalcApp\Service\HttpClient\HttpClient;
use FeeCalcApp\Service\Printer\PlainPrinter;
use FeeCalcApp\Service\Reader\CsvFileReader;
use FeeCalcApp\Service\Transaction\InMemoryTransactionStorage;
use FeeCalcApp\Service\{Math,
    TransactionBuilder,
    TransactionHistoryManager,
    TransactionProcessor,
    TransactionProcessorObserver};
use FeeCalcApp\Stub\ExchangeRateClientStub;

const CURRENCY_API_URL = 'http://api.currencylayer.com/live';
const CURRENCY_API_KEY = '6ba50a7460abb5dacb95c02de8caa194';
const DEFAULT_CURRENCY_CODE = 'EUR';

const PRIVATE_WITHDRAWAL_FREE_WEEKLY_AMOUNT = 100000;
const PRIVATE_WITHDRAWAL_MAX_WEEKLY_DISCOUNTS_NUMBER = 3;

const DEPOSIT_FEE_RATE = 0.0003;
const WITHDRAWAL_PRIVATE_FEE_RATE = 0.003;

const WITHDRAW_BUSINESS_FEE_RATE = 0.005;

const MATH_SCALE = 2;

if (!isset($argv[1])) {
    throw new \InvalidArgumentException('Missing input file with transaction information');
}

$transactionsData = (new CsvFileReader())->read($argv[1]);

$transactionBuilder = new TransactionBuilder();

$feeCalculatorCollection = new FeeCalculatorCollection();

$math = new Math(MATH_SCALE);

$depositCalculator = new DepositCalculator($math, DEPOSIT_FEE_RATE);
$feeCalculatorCollection->add($depositCalculator);

$withdrawalBusinessCalculator = new WithdrawalBusinessCalculator($math, WITHDRAW_BUSINESS_FEE_RATE);
$feeCalculatorCollection->add($withdrawalBusinessCalculator);


$exchangeRateClient = (isset($argv[2]) && $argv[2] === 'test')
    ? new ExchangeRateClientStub()
    : new ExchangeRateClient(new HttpClient(), CURRENCY_API_URL, CURRENCY_API_KEY);

$exchangeRateCacheProxy = new ExchangeRateCacheProxy($exchangeRateClient);
$transactionStorage = new InMemoryTransactionStorage();

$transactionHistoryManager = new TransactionHistoryManager(
    $exchangeRateCacheProxy,
    $transactionStorage,
    new DateTimeHelper(),
    $math
);

$withdrawalPrivateNoDiscountCalculator = new WithdrawalPrivateNoDiscountCalculator(
    $math,
    $transactionHistoryManager,
    WITHDRAWAL_PRIVATE_FEE_RATE
);
$feeCalculatorCollection->add($withdrawalPrivateNoDiscountCalculator);

$withdrawalPrivateCalculator = new WithdrawalPrivateCalculator(
    $transactionHistoryManager,
    $exchangeRateCacheProxy,
    $math,
    WITHDRAWAL_PRIVATE_FEE_RATE,
    DEFAULT_CURRENCY_CODE,
    PRIVATE_WITHDRAWAL_FREE_WEEKLY_AMOUNT,
    PRIVATE_WITHDRAWAL_MAX_WEEKLY_DISCOUNTS_NUMBER
);
$feeCalculatorCollection->add($withdrawalPrivateCalculator);

$transactionProcessorObserver = new TransactionProcessorObserver($transactionHistoryManager);

$transactionProcessor = new TransactionProcessor($feeCalculatorCollection, new PlainPrinter());
$transactionProcessor->attach($transactionProcessorObserver);

$transactions = [];
foreach ($transactionsData as $transactionData) {
    try {
        $transaction = $transactionBuilder
            ->setUserId($transactionData[1])
            ->setClientType($transactionData[2])
            ->setDate($transactionData[0])
            ->setOperationType($transactionData[3])
            ->setCurrencyAmount($transactionData[4], $transactionData[5])
            ->build();

        $transactionProcessor->process($transaction);
    } catch (TransactionException $e) {
        // just skip invalid transaction data for now
    }
}
