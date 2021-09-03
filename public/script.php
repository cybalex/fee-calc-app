<?php

require_once('./vendor/autoload.php');

use FeeCalcApp\Exception\TransactionException;
use FeeCalcApp\Service\Reader\CsvFileReader;
use FeeCalcApp\Service\{TransactionBuilder,TransactionProcessor};

if (!isset($argv[1])) {
    throw new \InvalidArgumentException('Missing input file with transaction information');
}

$env = isset($argv[2]) && $argv[2] === 'test' ? 'test' : 'prod';
$container = (new \AppFactory())->create($env)->buildContainer();

$transactionsData = $container->get(CsvFileReader::class)->read($argv[1]);

$transactions = [];
foreach ($transactionsData as $transactionData) {
    try {
        $transaction = $container->get(TransactionBuilder::class)
            ->setUserId($transactionData[1])
            ->setClientType($transactionData[2])
            ->setDate($transactionData[0])
            ->setOperationType($transactionData[3])
            ->setCurrencyAmount($transactionData[4], $transactionData[5])
            ->build();

        $container->get(TransactionProcessor::class)->process($transaction);
    } catch (TransactionException $e) {
        // ToDo: add logging
    }
}
