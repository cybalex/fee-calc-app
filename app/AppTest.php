<?php

declare(strict_types=1);

class AppTest extends App
{
    protected function getConfig(): string
    {
        return __DIR__ . '/config_test.php';
    }
}
