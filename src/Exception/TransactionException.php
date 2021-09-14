<?php

declare(strict_types=1);

namespace FeeCalcApp\Exception;

use FeeCalcApp\DTO\TransactionInterface;
use Throwable;

class TransactionException extends \InvalidArgumentException
{
    public function __construct(
        TransactionInterface $transactionDto,
        $message = '',
        $code = 0,
        Throwable $previous = null
    ) {
        $message = sprintf('Transaction of userId %d on %s');
        parent::__construct($message, $code, $previous);
    }
}
