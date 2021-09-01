<?php

require_once('./vendor/autoload.php');

use FeeCalcApp\Calculator\Fee\DepositCalculator;
use FeeCalcApp\Exception\TransactionException;
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

if (!isset($argv[1])) {
    throw new \InvalidArgumentException('Missing input file with transaction information');
}

$transactionsData = (new CsvFileReader())->read($argv[1]);

$transactionBuilder = new TransactionBuilder();

$transactions = [];
foreach ($transactionsData as $transactionData) {
    try {
        $transactions[] = $transactionBuilder
            ->setUserId($transactionData[1])
            ->setClientType($transactionData[2])
            ->setDate($transactionData[0])
            ->setOperationType($transactionData[3])
            ->setCurrencyAmount($transactionData[4], $transactionData[5])
            ->build();
    } catch (TransactionException $e) {
        // just skip invalid transaction data for now
    }
}

$feeCalculatorCollection = new FeeCalculatorCollection();
foreach ([DepositCalculator::class, WithdrawalBusinessCalculator::class] as $calculatorClass) {
    $feeCalculatorCollection->add(new $calculatorClass());
}

$exchangeRateClient = (isset($argv[2]) && $argv[2] === 'test')
    ? new ExchangeRateClientStub()
    : new ExchangeRateClient(new HttpClient());

$exchangeRateCacheProxy = new ExchangeRateCacheProxy($exchangeRateClient);
$transactionStorage = new InMemoryTransactionStorage();
$transactionHistoryManager = new TransactionHistoryManager($exchangeRateCacheProxy, $transactionStorage);

$withdrawalPrivateNoDiscountCalculator = new WithdrawalPrivateNoDiscountCalculator($transactionHistoryManager);
$feeCalculatorCollection->add($withdrawalPrivateNoDiscountCalculator);

$withdrawalPrivateCalculator = new WithdrawalPrivateCalculator($transactionHistoryManager, $exchangeRateCacheProxy);
$feeCalculatorCollection->add($withdrawalPrivateCalculator);


$transactionProcessorObserver = new TransactionProcessorObserver($transactionHistoryManager);

$transactionProcessor = new TransactionProcessor($feeCalculatorCollection, new PlainPrinter());
$transactionProcessor->attach($transactionProcessorObserver);

foreach ($transactions as $transaction) {
    $transactionProcessor->process($transaction);
}

