<?php
declare(strict_types=1);

return [
    'installed' => true,
    'storage' => 'mysql',
    'app_key' => '8d74ad6868b178eb3e0f71daabbae0d2a623a51b2a3abb0555b32247a375fb4f',
    'allow_registration' => true,
    'json_file' => __DIR__ . '/data/lists.json',
    'sqlite_file' => __DIR__ . '/data/app.sqlite',
    'mysql' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'haushaltspilot',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
];
