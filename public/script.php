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
use FeeCalcApp\Service\{
    TransactionBuilder,
    TransactionHistoryManager,
    TransactionProcessor,
    TransactionProcessorObserver
};
use FeeCalcApp\Stub\ExchangeRateClientStub;

const CURRENCY_API_URL = 'http://api.currencylayer.com/live';
const CURRENCY_API_KEY = '6ba50a7460abb5dacb95c02de8caa194';
const DEFAULT_CURRENCY_CODE = 'EUR';

const PRIVATE_WITHDRAWAL_FREE_WEEKLY_AMOUNT = 100000;

if (!isset($argv[1])) {
    throw new \InvalidArgumentException('Missing input file with transaction information');
}

$transactionsData = (new CsvFileReader())->read($argv[1]);

$transactionBuilder = new TransactionBuilder();

$feeCalculatorCollection = new FeeCalculatorCollection();
foreach ([DepositCalculator::class, WithdrawalBusinessCalculator::class] as $calculatorClass) {
    $feeCalculatorCollection->add(new $calculatorClass());
}

$exchangeRateClient = (isset($argv[2]) && $argv[2] === 'test')
    ? new ExchangeRateClientStub()
    : new ExchangeRateClient(new HttpClient(), CURRENCY_API_URL, CURRENCY_API_KEY);

$exchangeRateCacheProxy = new ExchangeRateCacheProxy($exchangeRateClient);
$transactionStorage = new InMemoryTransactionStorage();

$transactionHistoryManager = new TransactionHistoryManager(
    $exchangeRateCacheProxy,
    $transactionStorage,
    new DateTimeHelper()
);

$withdrawalPrivateNoDiscountCalculator = new WithdrawalPrivateNoDiscountCalculator($transactionHistoryManager);
$feeCalculatorCollection->add($withdrawalPrivateNoDiscountCalculator);

$withdrawalPrivateCalculator = new WithdrawalPrivateCalculator(
    $transactionHistoryManager,
    $exchangeRateCacheProxy,
    DEFAULT_CURRENCY_CODE,
    PRIVATE_WITHDRAWAL_FREE_WEEKLY_AMOUNT
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
