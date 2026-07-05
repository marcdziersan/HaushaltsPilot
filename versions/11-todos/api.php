<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

require_logged_in_for_api();

$action = $_GET['action'] ?? '';

if (!is_string($action) || $action === '') {
    send_json(['success' => false, 'message' => 'Keine Aktion angegeben.'], 400);
}

function get_current_user_or_fail(array $data): array
{
    $userId = current_user_id();

    if ($userId === null) {
        send_json(['success' => false, 'message' => 'Nicht angemeldet.'], 401);
    }

    $user = find_user_by_id($data, $userId);

    if ($user === null) {
        send_json(['success' => false, 'message' => 'Benutzerkonto wurde nicht gefunden.'], 401);
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

function visible_todos_for_user(array $data, array $user): array
{
    $visible = [];

    foreach ($data['todos'] as $todo) {
        if (
            ($todo['scope'] === TODO_SCOPE_PRIVATE && $todo['ownerId'] === $user['id']) ||
            ($todo['scope'] === TODO_SCOPE_FAMILY && $user['familyId'] !== '' && $todo['familyId'] === $user['familyId'])
        ) {
            $visible[] = $todo;
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

function public_families(array $data): array
{
    return array_map('public_family', $data['families']);
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

    return [
        'currentUser' => public_user($user),
        'currentFamily' => $user['familyId'] !== '' ? public_family(find_family_by_id($data, $user['familyId']) ?? []) : null,
        'lists' => $visibleLists,
        'activeListId' => $activeListId,
        'todos' => visible_todos_for_user($data, $user),
        'adminTodos' => is_admin($user) ? $data['todos'] : [],
        'users' => public_users($data),
        'activeUsers' => active_public_users($data),
        'families' => public_families($data),
        'isAdmin' => is_admin($user),
    ];
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

function family_name_exists(array $data, string $name, string $ignoreFamilyId = ''): bool
{
    foreach ($data['families'] as $family) {
        if ($family['id'] !== $ignoreFamilyId && mb_strtolower($family['name']) === mb_strtolower($name)) {
            return true;
        }
    }

    return false;
}

function update_owned_lists_family(array &$data, string $ownerId, string $familyId): void
{
    foreach ($data['lists'] as &$list) {
        if ($list['ownerId'] === $ownerId) {
            $list['familyId'] = $familyId;

            if ($familyId === '') {
                $list['isShared'] = false;
            }
        }
    }
    unset($list);
}

function update_owned_todos_family(array &$data, string $ownerId, string $familyId): void
{
    foreach ($data['todos'] as &$todo) {
        if ($todo['ownerId'] === $ownerId) {
            $todo['familyId'] = $familyId;

            if ($familyId === '' && $todo['scope'] === TODO_SCOPE_FAMILY) {
                $todo['scope'] = TODO_SCOPE_PRIVATE;
            }
        }
    }
    unset($todo);
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
        $listType = clean_list_type($input['listType'] ?? LIST_TYPE_SHOPPING);
        require_non_empty($name, 'Bitte gib einen Listennamen ein.');

        $ownerId = $currentUser['id'];
        $owner = $currentUser;

        if (is_admin($currentUser) && isset($input['ownerId'])) {
            $requestedOwnerId = clean_text($input['ownerId'], 80);
            require_non_empty($requestedOwnerId, 'Kein Besitzer angegeben.');

            $requestedOwner = find_user_by_id($data, $requestedOwnerId);

            if ($requestedOwner === null || $requestedOwner['active'] !== true) {
                send_json(['success' => false, 'message' => 'Der gewählte Besitzer wurde nicht gefunden oder ist deaktiviert.'], 404);
            }

            $ownerId = $requestedOwnerId;
            $owner = $requestedOwner;
        }

        if ($isShared && $owner['familyId'] === '') {
            send_json(['success' => false, 'message' => 'Gemeinschaftslisten benötigen einen Haushalt. Weise den Besitzer zuerst einem Haushalt zu.'], 400);
        }

        $newList = [
            'id' => create_id('list'),
            'ownerId' => $ownerId,
            'familyId' => $owner['familyId'],
            'name' => $name,
            'listType' => $listType,
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
            send_json(['success' => false, 'message' => 'Liste wurde nicht gefunden oder ist nicht freigegeben.'], 404);
        }

        $data['activeLists'][$currentUser['id']] = $listId;

        save_and_send($storageAdapter, $data, $currentUser, 'Aktive Liste wurde geändert.');

    case 'delete_list':
        $listId = clean_text($input['listId'] ?? '', 80);
        require_non_empty($listId, 'Keine Liste angegeben.');

        if (count($data['lists']) <= 1) {
            send_json(['success' => false, 'message' => 'Die letzte Liste kann nicht gelöscht werden.'], 400);
        }

        $listIndex = find_list_index($data, $listId);

        if ($listIndex === null || !user_can_manage_list_settings($currentUser, $data['lists'][$listIndex])) {
            send_json(['success' => false, 'message' => 'Du darfst diese Liste nicht löschen. Nur Besitzer oder Admins dürfen Listen löschen.'], 403);
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
            send_json(['success' => false, 'message' => 'Du darfst diese Liste nicht bearbeiten.'], 403);
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
            send_json(['success' => false, 'message' => 'Du darfst diese Liste nicht bearbeiten.'], 403);
        }

        $itemIndex = find_item_index($data['lists'][$listIndex], $itemId);

        if ($itemIndex === null) {
            send_json(['success' => false, 'message' => 'Artikel wurde nicht gefunden.'], 404);
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
            send_json(['success' => false, 'message' => 'Du darfst diese Liste nicht bearbeiten.'], 403);
        }

        $itemIndex = find_item_index($data['lists'][$listIndex], $itemId);

        if ($itemIndex === null) {
            send_json(['success' => false, 'message' => 'Artikel wurde nicht gefunden.'], 404);
        }

        array_splice($data['lists'][$listIndex]['items'], $itemIndex, 1);

        save_and_send($storageAdapter, $data, $currentUser, 'Artikel wurde gelöscht.');

    case 'update_list_visibility':
        $listId = clean_text($input['listId'] ?? '', 80);
        $isShared = clean_bool($input['isShared'] ?? false);
        require_non_empty($listId, 'Keine Liste angegeben.');

        $listIndex = find_list_index($data, $listId);

        if ($listIndex === null || !user_can_manage_list_settings($currentUser, $data['lists'][$listIndex])) {
            send_json(['success' => false, 'message' => 'Du darfst die Sichtbarkeit dieser Liste nicht ändern. Nur Besitzer oder Admins dürfen das.'], 403);
        }

        if ($isShared && $data['lists'][$listIndex]['familyId'] === '') {
            send_json(['success' => false, 'message' => 'Diese Liste kann nicht geteilt werden, weil sie keinem Haushalt zugeordnet ist.'], 400);
        }

        $data['lists'][$listIndex]['isShared'] = $isShared;

        save_and_send($storageAdapter, $data, $currentUser, $isShared ? 'Liste wurde als Haushaltsliste freigegeben.' : 'Liste wurde auf privat gesetzt.');

    case 'update_list_type':
        $listId = clean_text($input['listId'] ?? '', 80);
        $listType = clean_list_type($input['listType'] ?? '');
        require_non_empty($listId, 'Keine Liste angegeben.');

        $listIndex = find_list_index($data, $listId);

        if ($listIndex === null || !user_can_manage_list_settings($currentUser, $data['lists'][$listIndex])) {
            send_json(['success' => false, 'message' => 'Du darfst den Typ dieser Liste nicht ändern. Nur Besitzer oder Admins dürfen das.'], 403);
        }

        $data['lists'][$listIndex]['listType'] = $listType;

        save_and_send($storageAdapter, $data, $currentUser, 'Listentyp wurde aktualisiert.');

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
        $data['lists'][$listIndex]['familyId'] = $owner['familyId'];

        if ($owner['familyId'] === '') {
            $data['lists'][$listIndex]['isShared'] = false;
        }

        save_and_send($storageAdapter, $data, $currentUser, 'Listenbesitzer wurde aktualisiert.');

    case 'create_todo':
        $title = clean_text($input['title'] ?? '', MAX_TODO_TITLE_LENGTH);
        $scope = clean_todo_scope($input['scope'] ?? TODO_SCOPE_PRIVATE);
        $dueAt = clean_due_date($input['dueAt'] ?? '');
        require_non_empty($title, 'Bitte gib eine Aufgabe ein.');

        if ($scope === TODO_SCOPE_FAMILY && $currentUser['familyId'] === '') {
            send_json(['success' => false, 'message' => 'Familienaufgaben benötigen einen Haushalt. Weise dich zuerst einem Haushalt zu.'], 400);
        }

        $data['todos'][] = [
            'id' => create_id('todo'),
            'ownerId' => $currentUser['id'],
            'familyId' => $scope === TODO_SCOPE_FAMILY ? $currentUser['familyId'] : $currentUser['familyId'],
            'scope' => $scope,
            'title' => $title,
            'status' => TODO_STATUS_OPEN,
            'dueAt' => $dueAt,
            'createdAt' => now_string(),
        ];

        save_and_send($storageAdapter, $data, $currentUser, 'Aufgabe wurde erstellt.');

    case 'toggle_todo':
        $todoId = clean_text($input['todoId'] ?? '', 80);
        require_non_empty($todoId, 'Keine Aufgabe angegeben.');

        $todoIndex = find_todo_index($data, $todoId);

        if ($todoIndex === null || !user_can_manage_todo($currentUser, $data['todos'][$todoIndex])) {
            send_json(['success' => false, 'message' => 'Du darfst diese Aufgabe nicht bearbeiten.'], 403);
        }

        $data['todos'][$todoIndex]['status'] = $data['todos'][$todoIndex]['status'] === TODO_STATUS_DONE ? TODO_STATUS_OPEN : TODO_STATUS_DONE;

        save_and_send($storageAdapter, $data, $currentUser, 'Aufgabenstatus wurde geändert.');

    case 'delete_todo':
        $todoId = clean_text($input['todoId'] ?? '', 80);
        require_non_empty($todoId, 'Keine Aufgabe angegeben.');

        $todoIndex = find_todo_index($data, $todoId);

        if ($todoIndex === null || !user_can_manage_todo($currentUser, $data['todos'][$todoIndex])) {
            send_json(['success' => false, 'message' => 'Du darfst diese Aufgabe nicht löschen.'], 403);
        }

        array_splice($data['todos'], $todoIndex, 1);

        save_and_send($storageAdapter, $data, $currentUser, 'Aufgabe wurde gelöscht.');

    case 'admin_create_family':
        if (!is_admin($currentUser)) {
            send_json(['success' => false, 'message' => 'Nur Admins dürfen Haushalte erstellen.'], 403);
        }

        $name = clean_text($input['name'] ?? '', MAX_FAMILY_NAME_LENGTH);
        require_non_empty($name, 'Bitte gib einen Haushaltsnamen ein.');

        if (family_name_exists($data, $name)) {
            send_json(['success' => false, 'message' => 'Ein Haushalt mit diesem Namen existiert bereits.'], 409);
        }

        $data['families'][] = [
            'id' => create_id('family'),
            'name' => $name,
            'createdBy' => $currentUser['id'],
            'createdAt' => now_string(),
        ];

        save_and_send($storageAdapter, $data, $currentUser, 'Haushalt wurde erstellt.');

    case 'admin_update_family_name':
        if (!is_admin($currentUser)) {
            send_json(['success' => false, 'message' => 'Nur Admins dürfen Haushalte bearbeiten.'], 403);
        }

        $familyId = clean_text($input['familyId'] ?? '', 80);
        $name = clean_text($input['name'] ?? '', MAX_FAMILY_NAME_LENGTH);
        require_non_empty($familyId, 'Kein Haushalt angegeben.');
        require_non_empty($name, 'Bitte gib einen Haushaltsnamen ein.');

        $familyIndex = find_family_index($data, $familyId);

        if ($familyIndex === null) {
            send_json(['success' => false, 'message' => 'Haushalt wurde nicht gefunden.'], 404);
        }

        if (family_name_exists($data, $name, $familyId)) {
            send_json(['success' => false, 'message' => 'Ein Haushalt mit diesem Namen existiert bereits.'], 409);
        }

        $data['families'][$familyIndex]['name'] = $name;

        save_and_send($storageAdapter, $data, $currentUser, 'Haushalt wurde umbenannt.');

    case 'admin_delete_family':
        if (!is_admin($currentUser)) {
            send_json(['success' => false, 'message' => 'Nur Admins dürfen Haushalte löschen.'], 403);
        }

        $familyId = clean_text($input['familyId'] ?? '', 80);
        require_non_empty($familyId, 'Kein Haushalt angegeben.');

        $familyIndex = find_family_index($data, $familyId);

        if ($familyIndex === null) {
            send_json(['success' => false, 'message' => 'Haushalt wurde nicht gefunden.'], 404);
        }

        array_splice($data['families'], $familyIndex, 1);

        foreach ($data['users'] as &$user) {
            if ($user['familyId'] === $familyId) {
                $user['familyId'] = '';
            }
        }
        unset($user);

        foreach ($data['lists'] as &$list) {
            if ($list['familyId'] === $familyId) {
                $list['familyId'] = '';
                $list['isShared'] = false;
            }
        }
        unset($list);

        foreach ($data['todos'] as &$todo) {
            if ($todo['familyId'] === $familyId) {
                $todo['familyId'] = '';
                if ($todo['scope'] === TODO_SCOPE_FAMILY) {
                    $todo['scope'] = TODO_SCOPE_PRIVATE;
                }
            }
        }
        unset($todo);

        save_and_send($storageAdapter, $data, $currentUser, 'Haushalt wurde gelöscht. Zugeordnete Nutzer sind jetzt ohne Haushalt.');

    case 'admin_update_user_family':
        if (!is_admin($currentUser)) {
            send_json(['success' => false, 'message' => 'Nur Admins dürfen Haushaltszuordnungen ändern.'], 403);
        }

        $userId = clean_text($input['userId'] ?? '', 80);
        $familyId = clean_optional_id($input['familyId'] ?? '');
        require_non_empty($userId, 'Kein Benutzer angegeben.');

        $userIndex = find_user_index($data, $userId);

        if ($userIndex === null) {
            send_json(['success' => false, 'message' => 'Benutzer wurde nicht gefunden.'], 404);
        }

        if ($familyId !== '' && find_family_index($data, $familyId) === null) {
            send_json(['success' => false, 'message' => 'Haushalt wurde nicht gefunden.'], 404);
        }

        $data['users'][$userIndex]['familyId'] = $familyId;
        update_owned_lists_family($data, $userId, $familyId);
        update_owned_todos_family($data, $userId, $familyId);

        save_and_send($storageAdapter, $data, $currentUser, 'Haushaltszuordnung wurde aktualisiert.');

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

        $data['todos'] = array_values(array_filter($data['todos'], function (array $todo) use ($userId): bool {
            return $todo['ownerId'] !== $userId;
        }));

        if (count($data['lists']) === 0) {
            $listId = create_id('list');
            $data['lists'][] = [
                'id' => $listId,
                'ownerId' => $currentUser['id'],
                'familyId' => $currentUser['familyId'],
                'name' => 'Einkauf',
                'listType' => LIST_TYPE_SHOPPING,
                'isShared' => false,
                'items' => [],
                'createdAt' => now_string(),
            ];
            $data['activeLists'][$currentUser['id']] = $listId;
        }

        save_and_send($storageAdapter, $data, $currentUser, 'Benutzer wurde gelöscht.');

    default:
        send_json(['success' => false, 'message' => 'Unbekannte Aktion.'], 404);
}
