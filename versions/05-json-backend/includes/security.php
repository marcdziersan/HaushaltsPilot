<?php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');

$secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secureCookie,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

const DATA_DIR = __DIR__ . '/../data';
const DATA_FILE = DATA_DIR . '/lists.json';

const MAX_LIST_NAME_LENGTH = 40;
const MAX_ITEM_NAME_LENGTH = 80;
const MAX_AMOUNT_LENGTH = 40;

const ALLOWED_CATEGORIES = [
    'Lebensmittel',
    'Getränke',
    'Haushalt',
    'Drogerie',
    'Schule',
    'Medikamente',
    'Sonstiges',
];

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function send_json(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

function get_csrf_token(): string
{
    if (
        empty($_SESSION['csrf_token']) ||
        !is_string($_SESSION['csrf_token'])
    ) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function require_csrf_token(): void
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $requestToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if (
        !is_string($sessionToken) ||
        !is_string($requestToken) ||
        $sessionToken === '' ||
        $requestToken === '' ||
        !hash_equals($sessionToken, $requestToken)
    ) {
        send_json([
            'success' => false,
            'message' => 'Ungültiger CSRF-Token.',
        ], 403);
    }
}

function require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
        send_json([
            'success' => false,
            'message' => 'Ungültige HTTP-Methode.',
        ], 405);
    }
}

function get_json_input(): array
{
    $rawBody = file_get_contents('php://input');

    if ($rawBody === false || trim($rawBody) === '') {
        return [];
    }

    $data = json_decode($rawBody, true);

    if (!is_array($data)) {
        send_json([
            'success' => false,
            'message' => 'Ungültige JSON-Anfrage.',
        ], 400);
    }

    return $data;
}

function ensure_data_file_exists(): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }

    if (!file_exists(DATA_FILE)) {
        write_data(create_default_data());
    }
}

function create_id(string $prefix): string
{
    return $prefix . '_' . bin2hex(random_bytes(8));
}

function create_default_data(): array
{
    $listId = create_id('list');

    return [
        'lists' => [
            [
                'id' => $listId,
                'name' => 'Einkauf',
                'items' => [],
            ],
        ],
        'activeListId' => $listId,
    ];
}

function read_data(): array
{
    ensure_data_file_exists();

    $json = file_get_contents(DATA_FILE);

    if ($json === false || trim($json) === '') {
        $data = create_default_data();
        write_data($data);
        return $data;
    }

    $data = json_decode($json, true);

    if (!is_valid_data($data)) {
        $data = create_default_data();
        write_data($data);
        return $data;
    }

    return $data;
}

function write_data(array $data): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }

    $json = json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($json === false) {
        send_json([
            'success' => false,
            'message' => 'Daten konnten nicht serialisiert werden.',
        ], 500);
    }

    $result = file_put_contents(DATA_FILE, $json, LOCK_EX);

    if ($result === false) {
        send_json([
            'success' => false,
            'message' => 'Daten konnten nicht gespeichert werden.',
        ], 500);
    }
}

function is_valid_data(mixed $data): bool
{
    if (
        !is_array($data) ||
        !isset($data['lists'], $data['activeListId']) ||
        !is_array($data['lists']) ||
        !is_string($data['activeListId']) ||
        count($data['lists']) === 0
    ) {
        return false;
    }

    foreach ($data['lists'] as $list) {
        if (!is_valid_list($list)) {
            return false;
        }
    }

    foreach ($data['lists'] as $list) {
        if ($list['id'] === $data['activeListId']) {
            return true;
        }
    }

    return false;
}

function is_valid_list(mixed $list): bool
{
    if (
        !is_array($list) ||
        !isset($list['id'], $list['name'], $list['items']) ||
        !is_string($list['id']) ||
        !is_string($list['name']) ||
        !is_array($list['items'])
    ) {
        return false;
    }

    foreach ($list['items'] as $item) {
        if (!is_valid_item($item)) {
            return false;
        }
    }

    return true;
}

function is_valid_item(mixed $item): bool
{
    return (
        is_array($item) &&
        isset($item['id'], $item['name'], $item['amount'], $item['category'], $item['done']) &&
        is_string($item['id']) &&
        is_string($item['name']) &&
        is_string($item['amount']) &&
        is_string($item['category']) &&
        is_bool($item['done'])
    );
}

function clean_text(mixed $value, int $maxLength): string
{
    if (!is_string($value)) {
        send_json([
            'success' => false,
            'message' => 'Ungültiger Textwert.',
        ], 400);
    }

    $value = trim($value);
    $value = preg_replace('/\s+/', ' ', $value) ?? '';

    if (mb_strlen($value) > $maxLength) {
        send_json([
            'success' => false,
            'message' => 'Eingabe ist zu lang.',
        ], 400);
    }

    return $value;
}

function require_non_empty(string $value, string $message): void
{
    if ($value === '') {
        send_json([
            'success' => false,
            'message' => $message,
        ], 400);
    }
}

function clean_category(mixed $value): string
{
    if (!is_string($value) || !in_array($value, ALLOWED_CATEGORIES, true)) {
        send_json([
            'success' => false,
            'message' => 'Ungültige Kategorie.',
        ], 400);
    }

    return $value;
}

function find_list_index(array $data, string $listId): ?int
{
    foreach ($data['lists'] as $index => $list) {
        if ($list['id'] === $listId) {
            return $index;
        }
    }

    return null;
}

function find_item_index(array $list, string $itemId): ?int
{
    foreach ($list['items'] as $index => $item) {
        if ($item['id'] === $itemId) {
            return $index;
        }
    }

    return null;
}