<?php

declare(strict_types=1);

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class App implements AppInterface
{
    public function buildContainer(array $definitions = []): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new PhpFileLoader($containerBuilder, new FileLocator(__DIR__));

        $configPaths = $this->getConfigs();

        array_walk($configPaths, function(string $configPath) use ($loader) {
            $loader->load($configPath);
        });

        foreach ($definitions as $serviceName => $service) {
            $containerBuilder->set($serviceName, $service);
        }

        return $containerBuilder;
    }

    public function getConfigs(): array
    {
        return array_map(function(string $item) {
            return $this->getConfigDir() . $item;
        }, [
            'parameters.php',
            'fee_calculators_config.php',
            'services.php'
        ]);
    }

    public function getConfigDir(): string
    {
        return __DIR__ . '/config/prod/';
    }
}
