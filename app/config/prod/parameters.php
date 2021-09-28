<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function(ContainerConfigurator $configurator) {

    $configurator->parameters()
        ->set('logs_date_format', 'Y-m-d H:i:s')
        ->set('currency_api_url', 'http://api.currencylayer.com/live')
        ->set('currency_api_key', '13cd8431d835173a67e1a98c6cbdd6d0')
        ->set('log_file', './var/log/logs.txt');
};
