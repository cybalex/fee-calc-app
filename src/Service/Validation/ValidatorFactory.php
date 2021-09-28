<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Validation;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

class ValidatorFactory
{
    public function __invoke(): ValidatorInterface
    {
        $validatorBuilder = new ValidatorBuilder();

        return $validatorBuilder
            ->addMethodMapping('loadValidatorMetadata')
            ->getValidator();
    }
}
