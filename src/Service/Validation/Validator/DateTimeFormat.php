<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Validation\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DateTimeFormat extends Constraint
{
    public $callback;
    public $message = 'The provided value is not in supported date time format';

    public function __construct(
        $callback = null,
        string $message = null,
        $groups = null,
        $payload = null,
        array $options = []
    ) {
        parent::__construct($options, $groups, $payload);
        $this->callback = current($callback);
        $this->message = $message ?? $this->message;
    }
}
