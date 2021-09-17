<?php

declare(strict_types=1);

namespace FeeCalcApp\DTO;

interface TransactionInterface
{
    public function getId(): string;

    public function getDate(): \DateTime;

    public function getUserId(): int;

    public function getClientType(): string;

    public function getOperationType(): string;

    public function getAmount(): int;

    public function getCurrencyCode(): string;
}
