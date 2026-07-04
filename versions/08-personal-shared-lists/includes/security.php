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

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

const ROLE_ADMIN = 'admin';
const ROLE_USER = 'user';

const MAX_USERNAME_LENGTH = 30;
const MAX_DISPLAY_NAME_LENGTH = 60;
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

function redirect(string $target): never
{
    header('Location: ' . $target);
    exit;
}

function send_json(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');

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
    $requestToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');

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

function create_id(string $prefix): string
{
    return $prefix . '_' . bin2hex(random_bytes(8));
}

function now_string(): string
{
    return date('Y-m-d H:i:s');
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

function clean_username(mixed $value): string
{
    $username = clean_text($value, MAX_USERNAME_LENGTH);

    if ($username === '') {
        send_json([
            'success' => false,
            'message' => 'Bitte gib einen Benutzernamen ein.',
        ], 400);
    }

    if (mb_strlen($username) < 3) {
        send_json([
            'success' => false,
            'message' => 'Der Benutzername muss mindestens 3 Zeichen lang sein.',
        ], 400);
    }

    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
        send_json([
            'success' => false,
            'message' => 'Benutzername darf nur Buchstaben, Zahlen, Punkt, Unterstrich und Bindestrich enthalten.',
        ], 400);
    }

    return mb_strtolower($username);
}

function clean_password(mixed $value): string
{
    if (!is_string($value)) {
        send_json([
            'success' => false,
            'message' => 'Ungültiges Passwort.',
        ], 400);
    }

    if (mb_strlen($value) < 8) {
        send_json([
            'success' => false,
            'message' => 'Das Passwort muss mindestens 8 Zeichen lang sein.',
        ], 400);
    }

    if (mb_strlen($value) > 255) {
        send_json([
            'success' => false,
            'message' => 'Das Passwort ist zu lang.',
        ], 400);
    }

    return $value;
}

function clean_role(mixed $value): string
{
    if (!is_string($value) || !in_array($value, [ROLE_ADMIN, ROLE_USER], true)) {
        send_json([
            'success' => false,
            'message' => 'Ungültige Rolle.',
        ], 400);
    }

    return $value;
}

function clean_bool(mixed $value): bool
{
    if (is_bool($value)) {
        return $value;
    }

    if ($value === 1 || $value === '1' || $value === 'true') {
        return true;
    }

    if ($value === 0 || $value === '0' || $value === 'false') {
        return false;
    }

    send_json([
        'success' => false,
        'message' => 'Ungültiger Wahrheitswert.',
    ], 400);
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

function is_valid_data(mixed $data): bool
{
    if (
        !is_array($data) ||
        !isset($data['users'], $data['lists'], $data['activeLists']) ||
        !is_array($data['users']) ||
        !is_array($data['lists']) ||
        !is_array($data['activeLists']) ||
        count($data['users']) === 0
    ) {
        return false;
    }

    $hasAdmin = false;
    $userIds = [];

    foreach ($data['users'] as $user) {
        if (!is_valid_user($user)) {
            return false;
        }

        if (in_array($user['id'], $userIds, true)) {
            return false;
        }

        $userIds[] = $user['id'];

        if ($user['role'] === ROLE_ADMIN && $user['active'] === true) {
            $hasAdmin = true;
        }
    }

    if (!$hasAdmin) {
        return false;
    }

    foreach ($data['lists'] as $list) {
        if (!is_valid_list($list) || !in_array($list['ownerId'], $userIds, true)) {
            return false;
        }
    }

    foreach ($data['activeLists'] as $userId => $listId) {
        if (!is_string($userId) || !is_string($listId)) {
            return false;
        }
    }

    return true;
}

function is_valid_user(mixed $user): bool
{
    return (
        is_array($user) &&
        isset($user['id'], $user['username'], $user['displayName'], $user['passwordHash'], $user['role'], $user['active'], $user['createdAt']) &&
        is_string($user['id']) &&
        is_string($user['username']) &&
        is_string($user['displayName']) &&
        is_string($user['passwordHash']) &&
        in_array($user['role'], [ROLE_ADMIN, ROLE_USER], true) &&
        is_bool($user['active']) &&
        is_string($user['createdAt'])
    );
}

function is_valid_list(mixed $list): bool
{
    if (
        !is_array($list) ||
        !isset($list['id'], $list['name'], $list['ownerId'], $list['isShared'], $list['items'], $list['createdAt']) ||
        !is_string($list['id']) ||
        !is_string($list['name']) ||
        !is_string($list['ownerId']) ||
        !is_bool($list['isShared']) ||
        !is_array($list['items']) ||
        !is_string($list['createdAt'])
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
        isset($item['id'], $item['name'], $item['amount'], $item['category'], $item['done'], $item['createdBy'], $item['createdAt']) &&
        is_string($item['id']) &&
        is_string($item['name']) &&
        is_string($item['amount']) &&
        is_string($item['category']) &&
        is_bool($item['done']) &&
        is_string($item['createdBy']) &&
        is_string($item['createdAt'])
    );
}

function public_user(array $user): array
{
    return [
        'id' => $user['id'],
        'username' => $user['username'],
        'displayName' => $user['displayName'],
        'role' => $user['role'],
        'active' => $user['active'],
        'createdAt' => $user['createdAt'],
    ];
}

function is_admin(array $user): bool
{
    return ($user['role'] ?? '') === ROLE_ADMIN;
}

function current_user_id(): ?string
{
    $userId = $_SESSION['user_id'] ?? null;
    return is_string($userId) && $userId !== '' ? $userId : null;
}

function require_logged_in_for_page(): void
{
    if (current_user_id() === null) {
        redirect('login.php');
    }
}

function require_logged_in_for_api(): void
{
    if (current_user_id() === null) {
        send_json([
            'success' => false,
            'message' => 'Nicht angemeldet.',
        ], 401);
    }
}

function find_user_index(array $data, string $userId): ?int
{
    foreach ($data['users'] as $index => $user) {
        if ($user['id'] === $userId) {
            return $index;
        }
    }

    return null;
}

function find_user_by_id(array $data, string $userId): ?array
{
    $index = find_user_index($data, $userId);
    return $index === null ? null : $data['users'][$index];
}

function find_user_by_username(array $data, string $username): ?array
{
    $username = mb_strtolower($username);

    foreach ($data['users'] as $user) {
        if (mb_strtolower($user['username']) === $username) {
            return $user;
        }
    }

    return null;
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

function user_can_view_list(array $user, array $list): bool
{
    return is_admin($user) || $list['ownerId'] === $user['id'] || $list['isShared'] === true;
}

function user_can_manage_list_items(array $user, array $list): bool
{
    return user_can_view_list($user, $list);
}

function user_can_manage_list_settings(array $user, array $list): bool
{
    return is_admin($user) || $list['ownerId'] === $user['id'];
}

function ensure_active_user(array $user): void
{
    if (($user['active'] ?? false) !== true) {
        send_json([
            'success' => false,
            'message' => 'Dieses Benutzerkonto ist deaktiviert.',
        ], 403);
    }
}

function count_active_admins(array $data): int
{
    $count = 0;

    foreach ($data['users'] as $user) {
        if ($user['role'] === ROLE_ADMIN && $user['active'] === true) {
            $count++;
        }
    }

    return $count;
}
