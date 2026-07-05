<?php
declare(strict_types=1);

return [
    'installed' => true,
    'storage' => 'mysql',
    'app_key' => '39e7aa01bee5e6691d18a4e9041a94fd55ff1c22f1a5ff1185e43de53a6a90f0',
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
