<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

require_logged_in_for_api();

$action = $_GET['action'] ?? '';

if (!is_string($action) || $action === '') {
    send_json([
        'success' => false,
        'message' => 'Keine Aktion angegeben.',
    ], 400);
}

function get_current_user_or_fail(array $data): array
{
    $userId = current_user_id();

    if ($userId === null) {
        send_json([
            'success' => false,
            'message' => 'Nicht angemeldet.',
        ], 401);
    }

    $user = find_user_by_id($data, $userId);

    if ($user === null) {
        send_json([
            'success' => false,
            'message' => 'Benutzerkonto wurde nicht gefunden.',
        ], 401);
    }

    ensure_active_user($user);
    return $user;
}

function visible_lists_for_user(array $data, array $user): array
{
    $visible = [];

    foreach ($data['lists'] as $list) {
        if (user_can_view_list($user, $list)) {
            $visible[] = $list;
        }
    }

    return $visible;
}

function public_users(array $data): array
{
    return array_map('public_user', $data['users']);
}

function active_public_users(array $data): array
{
    return array_values(array_filter(public_users($data), function (array $user): bool {
        return $user['active'] === true;
    }));
}

function prepare_response_data(array $data, array $user): array
{
    $visibleLists = visible_lists_for_user($data, $user);
    $activeListId = $data['activeLists'][$user['id']] ?? null;

    $activeVisible = false;

    foreach ($visibleLists as $list) {
        if ($list['id'] === $activeListId) {
            $activeVisible = true;
            break;
        }
    }

    if (!$activeVisible) {
        $activeListId = count($visibleLists) > 0 ? $visibleLists[0]['id'] : null;
    }

    $payload = [
        'currentUser' => public_user($user),
        'lists' => $visibleLists,
        'activeListId' => $activeListId,
        'users' => public_users($data),
        'activeUsers' => active_public_users($data),
        'isAdmin' => is_admin($user),
    ];

    return $payload;
}

function save_and_send(AppStorageInterface $storageAdapter, array $data, array $user, string $message): never
{
    save_app_data($storageAdapter, $data);

    send_json([
        'success' => true,
        'message' => $message,
        'data' => prepare_response_data($data, $user),
    ]);
}

$data = load_app_data($storageAdapter);
$currentUser = get_current_user_or_fail($data);

if ($action === 'load') {
    require_method('GET');

    send_json([
        'success' => true,
        'data' => prepare_response_data($data, $currentUser),
    ]);
}

require_method('POST');
require_csrf_token();

$input = get_json_input();

switch ($action) {
    case 'create_list':
        $name = clean_text($input['name'] ?? '', MAX_LIST_NAME_LENGTH);
        $isShared = clean_bool($input['isShared'] ?? false);
        require_non_empty($name, 'Bitte gib einen Listennamen ein.');

        $ownerId = $currentUser['id'];

        if (is_admin($currentUser) && isset($input['ownerId'])) {
            $requestedOwnerId = clean_text($input['ownerId'], 80);
            require_non_empty($requestedOwnerId, 'Kein Besitzer angegeben.');

            $owner = find_user_by_id($data, $requestedOwnerId);

            if ($owner === null || $owner['active'] !== true) {
                send_json([
                    'success' => false,
                    'message' => 'Der gewählte Besitzer wurde nicht gefunden oder ist deaktiviert.',
                ], 404);
            }

            $ownerId = $requestedOwnerId;
        }

        $newList = [
            'id' => create_id('list'),
            'ownerId' => $ownerId,
            'name' => $name,
            'isShared' => $isShared,
            'items' => [],
            'createdAt' => now_string(),
        ];

        $data['lists'][] = $newList;
        $data['activeLists'][$currentUser['id']] = $newList['id'];

        save_and_send($storageAdapter, $data, $currentUser, 'Liste wurde erstellt.');

    case 'set_active_list':
        $listId = clean_text($input['listId'] ?? '', 80);
        require_non_empty($listId, 'Keine Liste angegeben.');

        $listIndex = find_list_index($data, $listId);

        if ($listIndex === null || !user_can_view_list($currentUser, $data['lists'][$listIndex])) {
            send_json([
                'success' => false,
                'message' => 'Liste wurde nicht gefunden oder ist nicht freigegeben.',
            ], 404);
        }

        $data['activeLists'][$currentUser['id']] = $listId;

        save_and_send($storageAdapter, $data, $currentUser, 'Aktive Liste wurde geändert.');

    case 'delete_list':
        $listId = clean_text($input['listId'] ?? '', 80);
        require_non_empty($listId, 'Keine Liste angegeben.');

        if (count($data['lists']) <= 1) {
            send_json([
                'success' => false,
                'message' => 'Die letzte Liste kann nicht gelöscht werden.',
            ], 400);
        }

        $listIndex = find_list_index($data, $listId);

        if ($listIndex === null || !user_can_manage_list_settings($currentUser, $data['lists'][$listIndex])) {
            send_json([
                'success' => false,
                'message' => 'Du darfst diese Liste nicht löschen. Nur Besitzer oder Admins dürfen Listen löschen.',
            ], 403);
        }

        array_splice($data['lists'], $listIndex, 1);

        foreach ($data['activeLists'] as $userId => $activeListId) {
            if ($activeListId === $listId) {
                unset($data['activeLists'][$userId]);
            }
        }

        save_and_send($storageAdapter, $data, $currentUser, 'Liste wurde gelöscht.');

    case 'add_item':
        $listId = clean_text($input['listId'] ?? '', 80);
        $name = clean_text($input['name'] ?? '', MAX_ITEM_NAME_LENGTH);
        $amount = clean_text($input['amount'] ?? '', MAX_AMOUNT_LENGTH);
        $category = clean_category($input['category'] ?? '');

        require_non_empty($listId, 'Keine Liste angegeben.');
        require_non_empty($name, 'Bitte gib einen Artikelnamen ein.');

        $listIndex = find_list_index($data, $listId);

        if ($listIndex === null || !user_can_manage_list_items($currentUser, $data['lists'][$listIndex])) {
            send_json([
                'success' => false,
                'message' => 'Du darfst diese Liste nicht bearbeiten.',
            ], 403);
        }

        $data['lists'][$listIndex]['items'][] = [
            'id' => create_id('item'),
            'name' => $name,
            'amount' => $amount,
            'category' => $category,
            'done' => false,
            'createdBy' => $currentUser['id'],
            'createdAt' => now_string(),
        ];

        save_and_send($storageAdapter, $data, $currentUser, 'Artikel wurde hinzugefügt.');

    case 'toggle_item':
        $listId = clean_text($input['listId'] ?? '', 80);
        $itemId = clean_text($input['itemId'] ?? '', 80);

        require_non_empty($listId, 'Keine Liste angegeben.');
        require_non_empty($itemId, 'Kein Artikel angegeben.');

        $listIndex = find_list_index($data, $listId);

        if ($listIndex === null || !user_can_manage_list_items($currentUser, $data['lists'][$listIndex])) {
            send_json([
                'success' => false,
                'message' => 'Du darfst diese Liste nicht bearbeiten.',
            ], 403);
        }

        $itemIndex = find_item_index($data['lists'][$listIndex], $itemId);

        if ($itemIndex === null) {
            send_json([
                'success' => false,
                'message' => 'Artikel wurde nicht gefunden.',
            ], 404);
        }

        $data['lists'][$listIndex]['items'][$itemIndex]['done'] = !$data['lists'][$listIndex]['items'][$itemIndex]['done'];

        save_and_send($storageAdapter, $data, $currentUser, 'Artikelstatus wurde geändert.');

    case 'delete_item':
        $listId = clean_text($input['listId'] ?? '', 80);
        $itemId = clean_text($input['itemId'] ?? '', 80);

        require_non_empty($listId, 'Keine Liste angegeben.');
        require_non_empty($itemId, 'Kein Artikel angegeben.');

        $listIndex = find_list_index($data, $listId);

        if ($listIndex === null || !user_can_manage_list_items($currentUser, $data['lists'][$listIndex])) {
            send_json([
                'success' => false,
                'message' => 'Du darfst diese Liste nicht bearbeiten.',
            ], 403);
        }

        $itemIndex = find_item_index($data['lists'][$listIndex], $itemId);

        if ($itemIndex === null) {
            send_json([
                'success' => false,
                'message' => 'Artikel wurde nicht gefunden.',
            ], 404);
        }

        array_splice($data['lists'][$listIndex]['items'], $itemIndex, 1);

        save_and_send($storageAdapter, $data, $currentUser, 'Artikel wurde gelöscht.');

    case 'update_list_visibility':
        $listId = clean_text($input['listId'] ?? '', 80);
        $isShared = clean_bool($input['isShared'] ?? false);
        require_non_empty($listId, 'Keine Liste angegeben.');

        $listIndex = find_list_index($data, $listId);

        if ($listIndex === null || !user_can_manage_list_settings($currentUser, $data['lists'][$listIndex])) {
            send_json([
                'success' => false,
                'message' => 'Du darfst die Sichtbarkeit dieser Liste nicht ändern. Nur Besitzer oder Admins dürfen das.',
            ], 403);
        }

        $data['lists'][$listIndex]['isShared'] = $isShared;

        save_and_send(
            $storageAdapter,
            $data,
            $currentUser,
            $isShared ? 'Liste wurde als Gemeinschaftsliste freigegeben.' : 'Liste wurde auf privat gesetzt.'
        );

    case 'admin_update_list_owner':
        if (!is_admin($currentUser)) {
            send_json(['success' => false, 'message' => 'Nur Admins dürfen Listen einem anderen Benutzer zuweisen.'], 403);
        }

        $listId = clean_text($input['listId'] ?? '', 80);
        $ownerId = clean_text($input['ownerId'] ?? '', 80);
        require_non_empty($listId, 'Keine Liste angegeben.');
        require_non_empty($ownerId, 'Kein neuer Besitzer angegeben.');

        $listIndex = find_list_index($data, $listId);

        if ($listIndex === null) {
            send_json(['success' => false, 'message' => 'Liste wurde nicht gefunden.'], 404);
        }

        $owner = find_user_by_id($data, $ownerId);

        if ($owner === null || $owner['active'] !== true) {
            send_json(['success' => false, 'message' => 'Der neue Besitzer wurde nicht gefunden oder ist deaktiviert.'], 404);
        }

        $data['lists'][$listIndex]['ownerId'] = $ownerId;

        save_and_send($storageAdapter, $data, $currentUser, 'Listenbesitzer wurde aktualisiert.');

    case 'admin_update_user_role':
        if (!is_admin($currentUser)) {
            send_json(['success' => false, 'message' => 'Nur Admins dürfen Rollen ändern.'], 403);
        }

        $userId = clean_text($input['userId'] ?? '', 80);
        $role = clean_role($input['role'] ?? '');
        require_non_empty($userId, 'Kein Benutzer angegeben.');

        $userIndex = find_user_index($data, $userId);

        if ($userIndex === null) {
            send_json(['success' => false, 'message' => 'Benutzer wurde nicht gefunden.'], 404);
        }

        if ($data['users'][$userIndex]['id'] === $currentUser['id'] && $role !== ROLE_ADMIN) {
            send_json(['success' => false, 'message' => 'Du kannst dir nicht selbst die Admin-Rolle entziehen.'], 400);
        }

        if ($data['users'][$userIndex]['role'] === ROLE_ADMIN && $role === ROLE_USER && count_active_admins($data) <= 1) {
            send_json(['success' => false, 'message' => 'Der letzte aktive Admin darf nicht herabgestuft werden.'], 400);
        }

        $data['users'][$userIndex]['role'] = $role;

        save_and_send($storageAdapter, $data, $currentUser, 'Rolle wurde aktualisiert.');

    case 'admin_toggle_user_active':
        if (!is_admin($currentUser)) {
            send_json(['success' => false, 'message' => 'Nur Admins dürfen Konten aktivieren oder deaktivieren.'], 403);
        }

        $userId = clean_text($input['userId'] ?? '', 80);
        require_non_empty($userId, 'Kein Benutzer angegeben.');

        $userIndex = find_user_index($data, $userId);

        if ($userIndex === null) {
            send_json(['success' => false, 'message' => 'Benutzer wurde nicht gefunden.'], 404);
        }

        if ($data['users'][$userIndex]['id'] === $currentUser['id']) {
            send_json(['success' => false, 'message' => 'Du kannst dein eigenes Konto nicht deaktivieren.'], 400);
        }

        if ($data['users'][$userIndex]['role'] === ROLE_ADMIN && $data['users'][$userIndex]['active'] === true && count_active_admins($data) <= 1) {
            send_json(['success' => false, 'message' => 'Der letzte aktive Admin darf nicht deaktiviert werden.'], 400);
        }

        $data['users'][$userIndex]['active'] = !$data['users'][$userIndex]['active'];

        save_and_send($storageAdapter, $data, $currentUser, 'Benutzerstatus wurde geändert.');

    case 'admin_delete_user':
        if (!is_admin($currentUser)) {
            send_json(['success' => false, 'message' => 'Nur Admins dürfen Benutzer löschen.'], 403);
        }

        $userId = clean_text($input['userId'] ?? '', 80);
        require_non_empty($userId, 'Kein Benutzer angegeben.');

        if ($userId === $currentUser['id']) {
            send_json(['success' => false, 'message' => 'Du kannst dich nicht selbst löschen.'], 400);
        }

        $userIndex = find_user_index($data, $userId);

        if ($userIndex === null) {
            send_json(['success' => false, 'message' => 'Benutzer wurde nicht gefunden.'], 404);
        }

        if ($data['users'][$userIndex]['role'] === ROLE_ADMIN && count_active_admins($data) <= 1) {
            send_json(['success' => false, 'message' => 'Der letzte aktive Admin darf nicht gelöscht werden.'], 400);
        }

        array_splice($data['users'], $userIndex, 1);
        unset($data['activeLists'][$userId]);

        $data['lists'] = array_values(array_filter($data['lists'], function (array $list) use ($userId): bool {
            return $list['ownerId'] !== $userId;
        }));

        if (count($data['lists']) === 0) {
            $admin = $currentUser;
            $listId = create_id('list');
            $data['lists'][] = [
                'id' => $listId,
                'ownerId' => $admin['id'],
                'name' => 'Einkauf',
                'isShared' => false,
                'items' => [],
                'createdAt' => now_string(),
            ];
            $data['activeLists'][$admin['id']] = $listId;
        }

        save_and_send($storageAdapter, $data, $currentUser, 'Benutzer wurde gelöscht.');

    default:
        send_json([
            'success' => false,
            'message' => 'Unbekannte Aktion.',
        ], 404);
}
