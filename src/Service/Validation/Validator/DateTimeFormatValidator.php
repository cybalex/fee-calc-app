<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Validation\Validator;

use function is_callable;
use function is_string;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DateTimeFormatValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DateTimeFormat) {
            throw new UnexpectedTypeException($constraint, DateTimeFormat::class);
        }

        if (!$constraint->callback) {
            throw new ConstraintDefinitionException('The "callback" option must be specified on constraint DateTimeFormat.');
        }

        if (null === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        if (
            !is_callable($callback = [$this->context->getObject(), $constraint->callback])
            && !is_callable($callback = [$this->context->getClassName(), $constraint->callback])
            && !is_callable($callback = $constraint->callback)
        ) {
            throw new ConstraintDefinitionException('The Choice constraint expects a valid callback.');
        }

        if ($callback($value) === true) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $this->formatValue($value))
            ->addViolation();
    }
}
