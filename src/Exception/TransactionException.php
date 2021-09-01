<?php

declare(strict_types=1);

namespace FeeCalcApp\Exception;

use Throwable;

class TransactionException extends \InvalidArgumentException
{
    private ?string $field;

    public function __construct(string $field, $code = 0, Throwable $previous = null)
    {
        $this->field = $field;
        $msg = $previous ? $previous->getMessage() : sprintf('Invalid field "%s" value provided', $this->field);

        parent::__construct($msg, $code, $previous);
    }
}
