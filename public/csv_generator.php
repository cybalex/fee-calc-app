<?php

declare(strict_types=1);

$filePath = __DIR__ . '/../etc/input_' . uniqid() . '.csv';

$splFileInfo = new \SplFileInfo($filePath);
$splFile = $splFileInfo->openFile('w');

$transactionsCount = $argv[1] ?? 10000;

for ($i = 0; $i < $transactionsCount; $i++) {
    $splFile->fwrite(generateRaw());
}

function generateRaw(): string
{
    $data = [
        getRandomDate(),
        getRandomUserId(),
        getRandomClientType(),
        getRandomOperationType(),
        getRandomAmount(),
        getRandomCurrency(),
    ];

    return implode(',', $data) . PHP_EOL;
}

function getRandomDate(): string
{
    $min = strtotime('2013-12-25');
    $max = strtotime((new \DateTime())->format('Y-m-d'));

    $val = rand($min, $max);

    return date('Y-m-d', $val);
}

function getRandomUserId(): int
{
    return mt_rand(0, 25);
}

function getRandomClientType(): string
{
    $clientTypes = ['private', 'business'];

    return $clientTypes[mt_rand(0, count($clientTypes) - 1)];
}

function getRandomOperationType(): string
{
    $operationTypes = ['deposit', 'withdraw'];

    return $operationTypes[mt_rand(0, count($operationTypes) - 1)];
}

function getRandomAmount(): float
{
    return rand(0, 100000000) / 100;
}

function getRandomCurrency(): string
{
    $currencies = ['EUR', 'USD', 'JPY'];

    return $currencies[mt_rand(0, count($currencies) - 1)];
}
