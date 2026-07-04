<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

function redirect_with_error(string $message): never
{
    $_SESSION['flash_error'] = $message;
    redirect('login.php');
}

function redirect_with_success(string $message): never
{
    $_SESSION['flash_success'] = $message;
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

try {
    require_csrf_token();

    $action = $_POST['action'] ?? '';

    if (!is_string($action)) {
        redirect_with_error('Ungültige Aktion.');
    }

    $data = $storageAdapter->load();

    if ($action === 'login') {
        $username = clean_username($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!is_string($password) || $password === '') {
            redirect_with_error('Bitte gib dein Passwort ein.');
        }

        $user = find_user_by_username($data, $username);

        if ($user === null || !password_verify($password, $user['passwordHash'])) {
            redirect_with_error('Benutzername oder Passwort ist falsch.');
        }

        if ($user['active'] !== true) {
            redirect_with_error('Dieses Benutzerkonto ist deaktiviert.');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];

        redirect('index.php');
    }

    if ($action === 'register') {
        if (($config['allow_registration'] ?? true) !== true) {
            redirect_with_error('Registrierung ist deaktiviert.');
        }

        $username = clean_username($_POST['username'] ?? '');
        $displayName = clean_text($_POST['display_name'] ?? '', MAX_DISPLAY_NAME_LENGTH);
        $password = clean_password($_POST['password'] ?? '');
        $passwordRepeat = $_POST['password_repeat'] ?? '';

        if (!is_string($passwordRepeat) || !hash_equals($password, $passwordRepeat)) {
            redirect_with_error('Die Passwörter stimmen nicht überein.');
        }

        if (find_user_by_username($data, $username) !== null) {
            redirect_with_error('Dieser Benutzername ist bereits vergeben.');
        }

        $userId = create_id('user');
        $listId = create_id('list');
        $now = now_string();

        $data['users'][] = [
            'id' => $userId,
            'username' => $username,
            'displayName' => $displayName !== '' ? $displayName : $username,
            'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => ROLE_USER,
            'active' => true,
            'familyId' => '',
            'createdAt' => $now,
        ];

        $data['lists'][] = [
            'id' => $listId,
            'ownerId' => $userId,
            'familyId' => '',
            'name' => 'Meine Einkaufsliste',
            'isShared' => false,
            'items' => [],
            'createdAt' => $now,
        ];

        $data['activeLists'][$userId] = $listId;

        $storageAdapter->save($data);

        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;

        redirect('index.php');
    }

    if ($action === 'logout') {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        redirect('login.php');
    }

    redirect_with_error('Unbekannte Aktion.');
} catch (Throwable $exception) {
    redirect_with_error($exception->getMessage());
}
