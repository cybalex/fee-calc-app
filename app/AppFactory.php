<?php

declare(strict_types=1);

class AppFactory
{
    public function create(string $env): AppInterface
    {
        if ($env === 'test') {
            return new AppTest();
        }

        return new App();
    }
}
