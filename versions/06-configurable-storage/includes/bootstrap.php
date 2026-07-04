<?php
declare(strict_types=1);

require __DIR__ . '/security.php';

$configFile = __DIR__ . '/../config.php';
$isApiRequest = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'api.php';

if (!file_exists($configFile)) {
    if ($isApiRequest) {
        send_json([
            'success' => false,
            'message' => 'Die Anwendung ist noch nicht installiert.',
        ], 503);
    }

    header('Location: installer.php');
    exit;
}

$config = require $configFile;

if (!is_array($config) || ($config['installed'] ?? false) !== true) {
    if ($isApiRequest) {
        send_json([
            'success' => false,
            'message' => 'Die Konfiguration ist ungültig.',
        ], 500);
    }

    header('Location: installer.php');
    exit;
}

require __DIR__ . '/storage.php';

try {
    $storageAdapter = StorageFactory::create($config);
} catch (Throwable $exception) {
    if ($isApiRequest) {
        send_json([
            'success' => false,
            'message' => 'Speicher konnte nicht initialisiert werden.',
        ], 500);
    }

    http_response_code(500);
    echo 'Speicher konnte nicht initialisiert werden.';
    exit;
}
