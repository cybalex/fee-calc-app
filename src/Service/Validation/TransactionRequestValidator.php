<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Validation;

use FeeCalcApp\Service\TransactionRequest;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TransactionRequestValidator
{
    private ValidatorInterface $validator;
    private TransactionRequestMetadata $transactionRequestMetadata;

    public function __construct(
        ValidatorInterface $validator,
        TransactionRequestMetadata $transactionRequestMetadata
    ) {
        $this->validator = $validator;
        $this->transactionRequestMetadata = $transactionRequestMetadata;
    }

    public function validate(TransactionRequest $transactionRequest): ConstraintViolationList
    {
        $constraintViolationCollection = new ConstraintViolationList();

        foreach ($this->transactionRequestMetadata->getMetadata() as $propName => $validators) {
            $getter = $this->getPropGetter($propName);
            if (!method_exists($transactionRequest, $getter)) {
                continue;
            }

            $value = $transactionRequest->{$getter}();
            $constraintViolationCollection->addAll($this->validator->validate($value, $validators));
        }

        return $constraintViolationCollection;
    }

    private function getPropGetter(string $propName): string
    {
        return 'get'.ucfirst($propName);
    }
}
