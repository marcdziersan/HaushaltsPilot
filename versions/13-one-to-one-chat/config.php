<?php
declare(strict_types=1);

return [
    'installed' => true,
    'storage' => 'mysql',
    'app_key' => 'fe58e95b0adc9829f1d928e35145786ca8071b1d255e8e0d1152bf8874f8a52b',
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
