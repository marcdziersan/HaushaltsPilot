<?php
declare(strict_types=1);

require __DIR__ . '/security.php';

$configFile = __DIR__ . '/../config.php';
$scriptName = basename($_SERVER['SCRIPT_NAME'] ?? '');
$isApiRequest = $scriptName === 'api.php';
$isAuthRequest = $scriptName === 'auth.php';

if (!file_exists($configFile)) {
    if ($isApiRequest || $isAuthRequest) {
        send_json([
            'success' => false,
            'message' => 'Die Anwendung ist noch nicht installiert.',
        ], 503);
    }

    redirect('installer.php');
}

$config = require $configFile;

if (!is_array($config) || ($config['installed'] ?? false) !== true) {
    if ($isApiRequest || $isAuthRequest) {
        send_json([
            'success' => false,
            'message' => 'Die Konfiguration ist ungültig.',
        ], 500);
    }

    redirect('installer.php');
}

require __DIR__ . '/storage.php';

try {
    $storageAdapter = StorageFactory::create($config);
} catch (Throwable $exception) {
    if ($isApiRequest || $isAuthRequest) {
        send_json([
            'success' => false,
            'message' => 'Speicher konnte nicht initialisiert werden.',
        ], 500);
    }

    http_response_code(500);
    echo 'Speicher konnte nicht initialisiert werden.';
    exit;
}

function load_app_data(AppStorageInterface $storageAdapter): array
{
    try {
        return $storageAdapter->load();
    } catch (Throwable $exception) {
        send_json([
            'success' => false,
            'message' => $exception->getMessage(),
        ], 500);
    }
}

function save_app_data(AppStorageInterface $storageAdapter, array $data): void
{
    try {
        $storageAdapter->save($data);
    } catch (Throwable $exception) {
        send_json([
            'success' => false,
            'message' => $exception->getMessage(),
        ], 500);
    }
}
