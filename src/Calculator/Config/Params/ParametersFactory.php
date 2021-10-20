<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Config\Params;

use FeeCalcApp\Calculator\Config\Params\Item\FeeRateParameter;
use FeeCalcApp\Calculator\Config\Params\Item\FreeWeeklyTransactionAmount;
use FeeCalcApp\Calculator\Config\Params\Item\ParameterItemInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ParametersFactory
{
    public function __construct(private ValidatorInterface $validator, private LoggerInterface $logger)
    {
    }

    public function getParamItem(string $name, string|int|float|null|bool $value): ParameterItemInterface
    {
        $paramItem = $this->createParamItem($name, $value);

        $constrainViolationList = $this->validator->validate($paramItem);
        if (count($constrainViolationList) > 0) {
            foreach ($constrainViolationList as $constraintViolation) {
                $this->logger->critical($constraintViolation->getMessage(), [
                    'value' => (string) $value,
                    'prop_name' => $name,
                ]);
            }
            throw new InvalidArgumentException(sprintf('Invalid config provided for fee calculator prop %s config', $name));
        }

        return $paramItem;
    }

    private function createParamItem(string $name, string|int|float|null|bool $value): ParameterItemInterface
    {
        switch ($name) {
            case FeeRateParameter::PARAM_NAME:
                return new FeeRateParameter($value);
            case FreeWeeklyTransactionAmount::PARAM_NAME:
                return new FreeWeeklyTransactionAmount($value);
        }

        throw new InvalidArgumentException(sprintf('Unknown parameter "%s" was provided in the config', $name));
    }
}
