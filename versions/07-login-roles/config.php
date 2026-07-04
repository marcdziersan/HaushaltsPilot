<?php
declare(strict_types=1);

return [
    'installed' => false,
    'storage' => 'mysql',
    'app_key' => 'b8fc9f47a02d7f6fedd0fd46dd7af7eb62f005fca1745728647c9a7f4a3818fc',
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
