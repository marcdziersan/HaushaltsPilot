<?php
declare(strict_types=1);

return [
    'installed' => true,
    'storage' => 'mysql',
    'app_key' => '7aa55129655fa7407b1f9c137e496221b6de8abc2bdf47b0e77d1126b31e45b0',
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
