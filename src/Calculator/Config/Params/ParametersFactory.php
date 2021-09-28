<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Config\Params;

use FeeCalcApp\Calculator\Config\Params\Exception\MissingConfigParameterException;
use FeeCalcApp\Calculator\Config\Params\Item\FeeRateParameter;
use FeeCalcApp\Calculator\Config\Params\Item\FreeWeeklyTransactionAmount;
use FeeCalcApp\Calculator\Config\Params\Item\ParameterItemInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ParametersFactory
{
    private ValidatorInterface $validator;
    private LoggerInterface $logger;

    public function __construct(ValidatorInterface $validator, LoggerInterface $logger)
    {
        $this->validator = $validator;
        $this->logger = $logger;
    }

    public function getParamItem(string $name, $value): ParameterItemInterface
    {
        $constrainViolationList = $this->validator->validate($value, $this->getConstraints($name));
        if (count($constrainViolationList) > 0) {
            foreach ($constrainViolationList as $constraintViolation) {
                $this->logger->critical($constraintViolation->getMessage(), [
                    'value' => (string) $value,
                    'prop_name' => $name,
                ]);
            }
        }

        switch ($name) {
            case FeeRateParameter::PARAM_NAME:
                return new FeeRateParameter($value);
            case FreeWeeklyTransactionAmount::PARAM_NAME:
                return new FreeWeeklyTransactionAmount($value);
        }

        throw new InvalidArgumentException(sprintf('Unknown parameter "%s" was provided in the config', $name));
    }

    private function getConstraints(string $propName): array
    {
        if (!isset($this->getPropConstraintMap()[$propName])) {
            throw new MissingConfigParameterException(sprintf('Could not find "%s" config param in %s::getPropConstraintMap', $propName, __CLASS__));
        }

        return $this->getPropConstraintMap()[$propName];
    }

    private function getPropConstraintMap(): array
    {
        return [
            FeeRateParameter::PARAM_NAME => [
                new NotNull(),
                new Regex('/^(0|[1-9]\d*)(.\d+)?$/', 'Amount in wrong format was provided'),
            ],
            FreeWeeklyTransactionAmount::PARAM_NAME => [
                new NotNull(),
                new Regex('/^(0|[1-9]\d*)(.\d+)?$/', 'Amount in wrong format was provided'),
            ],
        ];
    }
}
