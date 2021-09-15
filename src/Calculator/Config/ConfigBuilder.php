<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Config;

use InvalidArgumentException;

class ConfigBuilder implements ConfigBuilderInterface
{
    private array $rawConfig;
    private ?array $config = null;

    public function __construct(array $rawConfig)
    {
        $this->rawConfig = $rawConfig;
    }

    public function getConfig(): array
    {
        if (!$this->config) {
            $this->config = $this->mergeParentConfigs($this->rawConfig);
        }

        return $this->config;
    }

    private function mergeParentConfigs(array $config): array
    {
        foreach ($config as $configName => $strategyConfig) {
            if (isset($strategyConfig['extends'])) {
                $parentConfigName = $strategyConfig['extends'];

                $config[$configName] = $this->mergeParentConfig(
                    $strategyConfig,
                    $parentConfigName,
                    $config
                );
            }
        }

        return $config;
    }

    private function mergeParentConfig(array $childConfig, string $parentConfigName, array $config): array
    {
        if (!isset($config[$parentConfigName])) {
            throw new InvalidArgumentException(sprintf('No fee calculation strategy config name "%s" found', $parentConfigName));
        }

        $parentConfig = $config[$parentConfigName];

        if (isset($parentConfig['extends'])) {
            $parentConfig = $this->mergeParentConfig($parentConfig, $parentConfig['extends'], $config);
        }

        foreach (['props', 'requirements'] as $key) {
            $childConfig[$key] = array_merge(
                $parentConfig[$key] ?? [],
                $childConfig[$key] ?? []
            );
        }

        unset($childConfig['extends']);

        return $childConfig;
    }
}
