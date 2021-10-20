<?php

return [
    'driver'   => 'pdo_mysql',
    'host'     => getenv('MYSQL_HOST') ?? '',
    'user'     => getenv('MYSQL_USER') ?? '',
    'password' => getenv('MYSQL_PASSWORD') ?? '',
    'dbname'   => getenv('MYSQL_DATABASE') ?? '',
];
