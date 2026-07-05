<?php
declare(strict_types=1);

return [
    // Der Installer erzeugt die echte config.php automatisch.
    // Diese Datei dient nur als Vorlage für öffentliche Repositories.
    'installed' => false,
    'storage' => 'json',
    'app_key' => 'CHANGE_ME_GENERATE_WITH_INSTALLER',
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
