<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Config\Params;

use FeeCalcApp\Calculator\Config\Params\Exception\MissingConfigParameterException;
use FeeCalcApp\Calculator\Config\Params\Item\ParameterItemInterface;

class ParamBag
{
    public function __construct(
        private array $parameterItems = []
    ) {
        foreach ($parameterItems as $paramItem) {
            $this->addParamItem($paramItem);
        }
    }

    public function getParams(): array
    {
        return $this->parameterItems;
    }

    /**
     * @return mixed
     */
    public function getParam(string $name): ParameterItemInterface
    {
        if (!isset($this->parameterItems[$name])) {
            throw new MissingConfigParameterException(sprintf('Missing parameter name "%s"', $name));
        }

        return $this->parameterItems[$name];
    }

    private function addParamItem(ParameterItemInterface $parameterItem): void
    {
        $this->parameterItems[$parameterItem->getName()] = $parameterItem;
    }
}
