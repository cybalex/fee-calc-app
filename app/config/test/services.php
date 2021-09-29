<?php

use FeeCalcApp\Calculator\Config\FilterProvider;
use FeeCalcApp\Calculator\Filter\FilterCreator;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateClientInterface;
use FeeCalcApp\Stub\ExchangeRateClientStub;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function(ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $services->set(ExchangeRateClientStub::class, ExchangeRateClientStub::class);

    $services->alias(ExchangeRateClientInterface::class, ExchangeRateClientStub::class);

    $services
        ->set(FilterProvider::class, FilterProvider::class)
        ->arg(0, service(FilterCreator::class));
};
