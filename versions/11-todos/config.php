<?php
declare(strict_types=1);

return [
    'installed' => true,
    'storage' => 'mysql',
    'app_key' => '3779b6d9f5cd5ec5391c2ab3e1665524627eb6b50763af3fe841bbcecb919305',
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
