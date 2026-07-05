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


function thread_belongs_to_channel(array $thread, ?string $channel): bool
{
    if ($channel === null) {
        return true;
    }

    if ($channel === 'chat') {
        return $thread['threadType'] === MESSAGE_THREAD_CHAT;
    }

    if ($channel === 'family') {
        return $thread['threadType'] === MESSAGE_THREAD_FAMILY;
    }

    if ($channel === 'message') {
        return in_array($thread['threadType'], [MESSAGE_THREAD_PERSONAL, MESSAGE_THREAD_LEGACY_DIRECT], true);
    }

    return true;
}

function visible_message_threads_for_user(array $data, array $user, ?string $channel = null): array
{
    $visible = [];

    foreach ($data['messageThreads'] as $thread) {
        if (!thread_belongs_to_channel($thread, $channel)) {
            continue;
        }

        if (user_can_view_message_thread($user, $thread)) {
            $visible[] = $thread;
        }
    }

    usort($visible, function (array $left, array $right): int {
        return strcmp($right['updatedAt'], $left['updatedAt']);
    });

    return $visible;
}

function visible_messages_for_user(array $data, array $user, ?string $channel = null): array
{
    $visibleThreadIds = array_map(
        static fn(array $thread): string => $thread['id'],
        visible_message_threads_for_user($data, $user, $channel)
    );

    $messages = [];

    foreach ($data['messages'] as $message) {
        if (!in_array($message['threadId'], $visibleThreadIds, true)) {
            continue;
        }

        if (!is_admin($user) && in_array($user['id'], $message['deletedFor'], true)) {
            continue;
        }

        $messages[] = $message;
    }

    return $messages;
}

function messages_for_thread(array $data, string $threadId, array $user): array
{
    $messages = [];

    foreach ($data['messages'] as $message) {
        if ($message['threadId'] !== $threadId) {
            continue;
        }

        if (!is_admin($user) && in_array($user['id'], $message['deletedFor'], true)) {
            continue;
        }

        $messages[] = $message;
    }

    return $messages;
}

function find_thread_by_id(array $data, string $threadId): ?array
{
    foreach ($data['messageThreads'] as $thread) {
        if ($thread['id'] === $threadId) {
            return $thread;
        }
    }

    return null;
}

function message_delivery_state(array $data, array $message, array $viewer): array
{
    if ($message['senderId'] !== $viewer['id']) {
        return [
            'state' => 'received',
            'icon' => '',
            'label' => '',
        ];
    }

    $thread = find_thread_by_id($data, $message['threadId']);

    if ($thread === null) {
        return [
            'state' => 'sent',
            'icon' => '✓',
            'label' => 'gesendet',
        ];
    }

    if (($thread['threadType'] ?? '') === MESSAGE_THREAD_FAMILY) {
        $otherParticipantIds = array_values(array_filter(
            $thread['participantIds'],
            static fn(string $participantId): bool => $participantId !== $viewer['id']
        ));

        if (count($otherParticipantIds) === 0) {
            return [
                'state' => 'sent',
                'icon' => '✓',
                'label' => 'gesendet',
            ];
        }

        $readCount = 0;
        foreach ($otherParticipantIds as $participantId) {
            $readAt = $thread['lastReadAt'][$participantId] ?? '';
            if ($readAt !== '' && $readAt >= $message['createdAt']) {
                $readCount++;
            }
        }

        if ($readCount === count($otherParticipantIds)) {
            return [
                'state' => 'read',
                'icon' => '✓✓',
                'label' => 'von allen gelesen',
            ];
        }

        if ($readCount > 0) {
            return [
                'state' => 'partly_read',
                'icon' => '✓✓',
                'label' => $readCount . '/' . count($otherParticipantIds) . ' gelesen',
            ];
        }

        return [
            'state' => 'delivered',
            'icon' => '✓',
            'label' => 'zugestellt',
        ];
    }

    $recipientId = $message['recipientId'];

    if ($recipientId === '') {
        return [
            'state' => 'sent',
            'icon' => '✓',
            'label' => 'gesendet',
        ];
    }

    $recipientReadAt = $thread['lastReadAt'][$recipientId] ?? '';

    if ($recipientReadAt !== '' && $recipientReadAt >= $message['createdAt']) {
        return [
            'state' => 'read',
            'icon' => '✓✓',
            'label' => 'gelesen',
        ];
    }

    return [
        'state' => 'delivered',
        'icon' => '✓',
        'label' => 'zugestellt',
    ];
}

function public_message_for_user(array $data, array $message, array $viewer): array
{
    $delivery = message_delivery_state($data, $message, $viewer);

    return [
        'id' => $message['id'],
        'threadId' => $message['threadId'],
        'senderId' => $message['senderId'],
        'recipientId' => $message['recipientId'],
        'body' => $message['body'],
        'createdAt' => $message['createdAt'],
        'deletedFor' => $message['deletedFor'],
        'deliveryState' => $delivery['state'],
        'deliveryIcon' => $delivery['icon'],
        'deliveryLabel' => $delivery['label'],
    ];
}

function timestamp_is_recent(string $timestamp, int $maxAgeSeconds = 12): bool
{
    if ($timestamp === '') {
        return false;
    }

    $time = strtotime($timestamp);

    if ($time === false) {
        return false;
    }

    return $time >= time() - $maxAgeSeconds;
}

function prune_typing_indicators(array &$data): void
{
    $data['typingIndicators'] = array_values(array_filter(
        $data['typingIndicators'] ?? [],
        static fn(array $indicator): bool => timestamp_is_recent($indicator['updatedAt'] ?? '', 12)
    ));
}

function visible_typing_indicators_for_user(array $data, array $user): array
{
    $visible = [];

    foreach ($data['typingIndicators'] ?? [] as $indicator) {
        if (($indicator['userId'] ?? '') === $user['id']) {
            continue;
        }

        if (!timestamp_is_recent($indicator['updatedAt'] ?? '', 12)) {
            continue;
        }

        $threadId = $indicator['threadId'] ?? '';
        $recipientId = $indicator['recipientId'] ?? '';
        $isVisible = false;

        if ($threadId !== '') {
            $thread = find_thread_by_id($data, $threadId);
            $isVisible = $thread !== null && user_is_message_participant($user, $thread);
        } elseif ($recipientId === $user['id']) {
            $isVisible = true;
        }

        if (!$isVisible) {
            continue;
        }

        $typingUser = find_user_by_id($data, $indicator['userId']);

        if ($typingUser === null || $typingUser['active'] !== true) {
            continue;
        }

        $visible[] = [
            'userId' => $indicator['userId'],
            'displayName' => $typingUser['displayName'],
            'threadId' => $threadId,
            'recipientId' => $recipientId,
            'channel' => $indicator['channel'],
            'updatedAt' => $indicator['updatedAt'],
        ];
    }

    return $visible;
}

function message_thread_unread_count(array $data, array $thread, array $user): int
{
    if (!user_is_message_participant($user, $thread)) {
        return 0;
    }

    $lastReadAt = $thread['lastReadAt'][$user['id']] ?? '';
    $count = 0;

    foreach ($data['messages'] as $message) {
        if ($message['threadId'] !== $thread['id'] || $message['senderId'] === $user['id']) {
            continue;
        }

        if (in_array($user['id'], $message['deletedFor'], true)) {
            continue;
        }

        if ($lastReadAt === '' || $message['createdAt'] > $lastReadAt) {
            $count++;
        }
    }

    return $count;
}

function last_message_for_thread(array $data, array $thread, array $user): ?array
{
    $messages = messages_for_thread($data, $thread['id'], $user);

    if (count($messages) === 0) {
        return null;
    }

    return $messages[count($messages) - 1];
}

function public_message_thread(array $data, array $thread, array $user): array
{
    $otherParticipantIds = array_values(array_filter(
        $thread['participantIds'],
        static fn(string $participantId): bool => $participantId !== $user['id']
    ));

    $lastMessage = last_message_for_thread($data, $thread, $user);

    return [
        'id' => $thread['id'],
        'threadType' => $thread['threadType'],
        'participantIds' => $thread['participantIds'],
        'familyId' => $thread['familyId'] ?? '',
        'title' => $thread['title'] ?? '',
        'otherParticipantId' => $otherParticipantIds[0] ?? '',
        'participantCount' => count($thread['participantIds']),
        'unreadCount' => message_thread_unread_count($data, $thread, $user),
        'lastMessage' => $lastMessage,
        'createdBy' => $thread['createdBy'],
        'createdAt' => $thread['createdAt'],
        'updatedAt' => $thread['updatedAt'],
    ];
}

function find_personal_thread_index(array $data, string $firstUserId, string $secondUserId, string $threadType = MESSAGE_THREAD_PERSONAL): ?int
{
    $wanted = [$firstUserId, $secondUserId];
    sort($wanted);

    foreach ($data['messageThreads'] as $index => $thread) {
        if ($thread['threadType'] !== $threadType) {
            continue;
        }

        $participants = $thread['participantIds'];
        sort($participants);

        if ($participants === $wanted) {
            return $index;
        }
    }

    return null;
}

function active_family_members(array $data, string $familyId): array
{
    if ($familyId === '') {
        return [];
    }

    return array_values(array_filter(
        $data['users'],
        static fn(array $user): bool => $user['active'] === true && $user['familyId'] === $familyId
    ));
}

function find_family_chat_thread_index(array $data, string $familyId): ?int
{
    foreach ($data['messageThreads'] as $index => $thread) {
        if ($thread['threadType'] === MESSAGE_THREAD_FAMILY && ($thread['familyId'] ?? '') === $familyId) {
            return $index;
        }
    }

    return null;
}

function family_chat_title(array $data, string $familyId): string
{
    $family = find_family_by_id($data, $familyId);
    return 'Familienchat' . ($family !== null ? ' · ' . $family['name'] : '');
}

function ensure_family_chat_thread(array &$data, string $familyId, string $createdBy): ?int
{
    if ($familyId === '') {
        return null;
    }

    $members = active_family_members($data, $familyId);

    if (count($members) < 2) {
        return null;
    }

    $memberIds = array_values(array_map(static fn(array $member): string => $member['id'], $members));
    $threadIndex = find_family_chat_thread_index($data, $familyId);
    $now = now_string();

    if ($threadIndex === null) {
        $lastReadAt = [];
        foreach ($memberIds as $memberId) {
            $lastReadAt[$memberId] = $memberId === $createdBy ? $now : '';
        }

        $data['messageThreads'][] = [
            'id' => create_id('family_thread'),
            'threadType' => MESSAGE_THREAD_FAMILY,
            'familyId' => $familyId,
            'title' => family_chat_title($data, $familyId),
            'participantIds' => $memberIds,
            'lastReadAt' => $lastReadAt,
            'createdBy' => $createdBy,
            'createdAt' => $now,
            'updatedAt' => $now,
        ];

        return count($data['messageThreads']) - 1;
    }

    $changed = false;
    $thread = &$data['messageThreads'][$threadIndex];
    $thread['familyId'] = $familyId;
    $thread['title'] = family_chat_title($data, $familyId);

    $oldParticipantIds = $thread['participantIds'];
    sort($oldParticipantIds);
    $sortedMemberIds = $memberIds;
    sort($sortedMemberIds);

    if ($oldParticipantIds !== $sortedMemberIds) {
        $thread['participantIds'] = $memberIds;
        $changed = true;
    }

    foreach ($memberIds as $memberId) {
        if (!isset($thread['lastReadAt'][$memberId])) {
            $thread['lastReadAt'][$memberId] = '';
            $changed = true;
        }
    }

    foreach (array_keys($thread['lastReadAt']) as $memberId) {
        if (!in_array($memberId, $memberIds, true)) {
            unset($thread['lastReadAt'][$memberId]);
            $changed = true;
        }
    }

    if ($changed) {
        $thread['updatedAt'] = $now;
    }

    unset($thread);
    return $threadIndex;
}

function sync_family_chat_threads(array &$data, array $actor): bool
{
    $before = json_encode($data['messageThreads'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    foreach ($data['families'] as $family) {
        $members = active_family_members($data, $family['id']);
        if (count($members) < 2) {
            continue;
        }

        $creatorId = $actor['familyId'] === $family['id'] ? $actor['id'] : $members[0]['id'];
        ensure_family_chat_thread($data, $family['id'], $creatorId);
    }

    $after = json_encode($data['messageThreads'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return $before !== $after;
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

    $visiblePersonalThreads = visible_message_threads_for_user($data, $user, 'message');
    $publicThreads = array_map(
        static fn(array $thread): array => public_message_thread($data, $thread, $user),
        $visiblePersonalThreads
    );
    $totalUnreadMessages = array_sum(array_map(
        static fn(array $thread): int => $thread['unreadCount'],
        $publicThreads
    ));

    $visibleChatThreads = visible_message_threads_for_user($data, $user, 'chat');
    $publicChatThreads = array_map(
        static fn(array $thread): array => public_message_thread($data, $thread, $user),
        $visibleChatThreads
    );
    $totalUnreadChats = array_sum(array_map(
        static fn(array $thread): int => $thread['unreadCount'],
        $publicChatThreads
    ));

    $visibleFamilyChatThreads = visible_message_threads_for_user($data, $user, 'family');
    $publicFamilyChatThreads = array_map(
        static fn(array $thread): array => public_message_thread($data, $thread, $user),
        $visibleFamilyChatThreads
    );
    $totalUnreadFamilyChats = array_sum(array_map(
        static fn(array $thread): int => $thread['unreadCount'],
        $publicFamilyChatThreads
    ));

    return [
        'currentUser' => public_user($user),
        'currentFamily' => $user['familyId'] !== '' ? public_family(find_family_by_id($data, $user['familyId']) ?? []) : null,
        'lists' => $visibleLists,
        'activeListId' => $activeListId,
        'todos' => visible_todos_for_user($data, $user),
        'adminTodos' => is_admin($user) ? $data['todos'] : [],
        'messageThreads' => $publicThreads,
        'chatThreads' => $publicChatThreads,
        'familyChatThreads' => $publicFamilyChatThreads,
        'messages' => array_map(static fn(array $message): array => public_message_for_user($data, $message, $user), visible_messages_for_user($data, $user)),
        'typingIndicators' => visible_typing_indicators_for_user($data, $user),
        'totalUnreadMessages' => $totalUnreadMessages,
        'totalUnreadChats' => $totalUnreadChats,
        'totalUnreadFamilyChats' => $totalUnreadFamilyChats,
        'adminMessages' => is_admin($user) ? array_map(static fn(array $message): array => public_message_for_user($data, $message, $user), visible_messages_for_user($data, $user, 'message')) : [],
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
                $todo['assignedTo'] = $ownerId;
            }
        }

        if (($todo['assignedTo'] ?? '') === $ownerId && $familyId === '' && $todo['scope'] === TODO_SCOPE_FAMILY) {
            $todo['assignedTo'] = '';
        }
    }
    unset($todo);
}

function clean_todo_comment_body(mixed $value): string
{
    $body = clean_text($value, MAX_TODO_COMMENT_LENGTH);
    require_non_empty($body, 'Bitte gib einen Kommentar ein.');
    return $body;
}

function validate_todo_assignment(array $data, array $todo, string $assignedTo): string
{
    if ($assignedTo === '') {
        return '';
    }

    $assignee = find_user_by_id($data, $assignedTo);

    if ($assignee === null || $assignee['active'] !== true) {
        send_json(['success' => false, 'message' => 'Die zugewiesene Person wurde nicht gefunden oder ist deaktiviert.'], 404);
    }

    if ($todo['scope'] === TODO_SCOPE_PRIVATE && $assignedTo !== $todo['ownerId']) {
        send_json(['success' => false, 'message' => 'Private Aufgaben können nur dem Besitzer der Aufgabe zugewiesen werden.'], 400);
    }

    if ($todo['scope'] === TODO_SCOPE_FAMILY) {
        if ($todo['familyId'] === '') {
            send_json(['success' => false, 'message' => 'Familienaufgaben benötigen einen Haushalt.'], 400);
        }

        if ($assignee['familyId'] !== $todo['familyId']) {
            send_json(['success' => false, 'message' => 'Familienaufgaben können nur Haushaltsmitgliedern zugewiesen werden.'], 400);
        }
    }

    return $assignedTo;
}

function normalize_todo_for_scope(array $actor, string $scope, string $title, string $priority, string $assignedTo, string $dueAt, string $reminderAt, string $calendarDate): array
{
    if ($scope === TODO_SCOPE_FAMILY && $actor['familyId'] === '') {
        send_json(['success' => false, 'message' => 'Familienaufgaben benötigen einen Haushalt. Weise dich zuerst einem Haushalt zu.'], 400);
    }

    if ($scope === TODO_SCOPE_PRIVATE && $assignedTo === '') {
        $assignedTo = $actor['id'];
    }

    $todo = [
        'id' => create_id('todo'),
        'ownerId' => $actor['id'],
        'familyId' => $actor['familyId'],
        'scope' => $scope,
        'title' => $title,
        'status' => TODO_STATUS_OPEN,
        'priority' => $priority,
        'assignedTo' => $assignedTo,
        'dueAt' => $dueAt,
        'reminderAt' => $reminderAt,
        'calendarDate' => $calendarDate,
        'comments' => [],
        'createdAt' => now_string(),
        'updatedAt' => now_string(),
    ];

    return $todo;
}

$data = load_app_data($storageAdapter);
prune_typing_indicators($data);
$currentUser = get_current_user_or_fail($data);
$familyChatSyncChanged = sync_family_chat_threads($data, $currentUser);

if ($action === 'load') {
    if ($familyChatSyncChanged) {
        save_app_data($storageAdapter, $data);
    }
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
        $priority = clean_todo_priority($input['priority'] ?? TODO_PRIORITY_NORMAL);
        $assignedTo = clean_optional_id($input['assignedTo'] ?? '');
        $dueAt = clean_due_date($input['dueAt'] ?? '');
        $reminderAt = clean_due_date($input['reminderAt'] ?? '');
        $calendarDate = clean_due_date($input['calendarDate'] ?? '');
        require_non_empty($title, 'Bitte gib eine Aufgabe ein.');

        $newTodo = normalize_todo_for_scope($currentUser, $scope, $title, $priority, $assignedTo, $dueAt, $reminderAt, $calendarDate);
        $newTodo['assignedTo'] = validate_todo_assignment($data, $newTodo, $assignedTo);

        $data['todos'][] = $newTodo;

        save_and_send($storageAdapter, $data, $currentUser, 'Aufgabe wurde erstellt.');

    case 'update_todo':
        $todoId = clean_text($input['todoId'] ?? '', 80);
        require_non_empty($todoId, 'Keine Aufgabe angegeben.');

        $todoIndex = find_todo_index($data, $todoId);

        if ($todoIndex === null || !user_can_manage_todo($currentUser, $data['todos'][$todoIndex])) {
            send_json(['success' => false, 'message' => 'Du darfst diese Aufgabe nicht bearbeiten.'], 403);
        }

        $title = clean_text($input['title'] ?? $data['todos'][$todoIndex]['title'], MAX_TODO_TITLE_LENGTH);
        $scope = clean_todo_scope($input['scope'] ?? $data['todos'][$todoIndex]['scope']);
        $priority = clean_todo_priority($input['priority'] ?? $data['todos'][$todoIndex]['priority']);
        $assignedTo = clean_optional_id($input['assignedTo'] ?? $data['todos'][$todoIndex]['assignedTo']);
        $dueAt = clean_due_date($input['dueAt'] ?? $data['todos'][$todoIndex]['dueAt']);
        $reminderAt = clean_due_date($input['reminderAt'] ?? $data['todos'][$todoIndex]['reminderAt']);
        $calendarDate = clean_due_date($input['calendarDate'] ?? $data['todos'][$todoIndex]['calendarDate']);
        require_non_empty($title, 'Bitte gib eine Aufgabe ein.');

        $data['todos'][$todoIndex]['title'] = $title;
        $data['todos'][$todoIndex]['scope'] = $scope;
        $data['todos'][$todoIndex]['priority'] = $priority;
        $data['todos'][$todoIndex]['dueAt'] = $dueAt;
        $data['todos'][$todoIndex]['reminderAt'] = $reminderAt;
        $data['todos'][$todoIndex]['calendarDate'] = $calendarDate;

        if ($scope === TODO_SCOPE_PRIVATE) {
            $data['todos'][$todoIndex]['familyId'] = $data['todos'][$todoIndex]['ownerId'] === $currentUser['id'] ? $currentUser['familyId'] : $data['todos'][$todoIndex]['familyId'];
            if ($assignedTo === '') {
                $assignedTo = $data['todos'][$todoIndex]['ownerId'];
            }
        }

        if ($scope === TODO_SCOPE_FAMILY) {
            if ($currentUser['familyId'] === '' && !is_admin($currentUser)) {
                send_json(['success' => false, 'message' => 'Familienaufgaben benötigen einen Haushalt.'], 400);
            }
            if ($data['todos'][$todoIndex]['familyId'] === '') {
                $data['todos'][$todoIndex]['familyId'] = $currentUser['familyId'];
            }
        }

        $data['todos'][$todoIndex]['assignedTo'] = validate_todo_assignment($data, $data['todos'][$todoIndex], $assignedTo);
        $data['todos'][$todoIndex]['updatedAt'] = now_string();

        save_and_send($storageAdapter, $data, $currentUser, 'Aufgabe wurde aktualisiert.');

    case 'toggle_todo':
        $todoId = clean_text($input['todoId'] ?? '', 80);
        require_non_empty($todoId, 'Keine Aufgabe angegeben.');

        $todoIndex = find_todo_index($data, $todoId);

        if ($todoIndex === null || !user_can_manage_todo($currentUser, $data['todos'][$todoIndex])) {
            send_json(['success' => false, 'message' => 'Du darfst diese Aufgabe nicht bearbeiten.'], 403);
        }

        $data['todos'][$todoIndex]['status'] = $data['todos'][$todoIndex]['status'] === TODO_STATUS_DONE ? TODO_STATUS_OPEN : TODO_STATUS_DONE;
        $data['todos'][$todoIndex]['updatedAt'] = now_string();

        save_and_send($storageAdapter, $data, $currentUser, 'Aufgabenstatus wurde geändert.');

    case 'add_todo_comment':
        $todoId = clean_text($input['todoId'] ?? '', 80);
        $body = clean_todo_comment_body($input['body'] ?? '');
        require_non_empty($todoId, 'Keine Aufgabe angegeben.');

        $todoIndex = find_todo_index($data, $todoId);

        if ($todoIndex === null || !user_can_view_todo($currentUser, $data['todos'][$todoIndex])) {
            send_json(['success' => false, 'message' => 'Du darfst diese Aufgabe nicht kommentieren.'], 403);
        }

        $data['todos'][$todoIndex]['comments'][] = [
            'id' => create_id('comment'),
            'authorId' => $currentUser['id'],
            'body' => $body,
            'createdAt' => now_string(),
        ];
        $data['todos'][$todoIndex]['updatedAt'] = now_string();

        save_and_send($storageAdapter, $data, $currentUser, 'Kommentar wurde gespeichert.');

    case 'delete_todo_comment':
        $todoId = clean_text($input['todoId'] ?? '', 80);
        $commentId = clean_text($input['commentId'] ?? '', 80);
        require_non_empty($todoId, 'Keine Aufgabe angegeben.');
        require_non_empty($commentId, 'Kein Kommentar angegeben.');

        $todoIndex = find_todo_index($data, $todoId);

        if ($todoIndex === null || !user_can_view_todo($currentUser, $data['todos'][$todoIndex])) {
            send_json(['success' => false, 'message' => 'Du darfst diese Aufgabe nicht bearbeiten.'], 403);
        }

        $commentIndex = find_todo_comment_index($data['todos'][$todoIndex], $commentId);

        if ($commentIndex === null) {
            send_json(['success' => false, 'message' => 'Kommentar wurde nicht gefunden.'], 404);
        }

        $comment = $data['todos'][$todoIndex]['comments'][$commentIndex];
        if (!is_admin($currentUser) && $comment['authorId'] !== $currentUser['id'] && $data['todos'][$todoIndex]['ownerId'] !== $currentUser['id']) {
            send_json(['success' => false, 'message' => 'Du darfst diesen Kommentar nicht löschen.'], 403);
        }

        array_splice($data['todos'][$todoIndex]['comments'], $commentIndex, 1);
        $data['todos'][$todoIndex]['updatedAt'] = now_string();

        save_and_send($storageAdapter, $data, $currentUser, 'Kommentar wurde gelöscht.');

    case 'delete_todo':
        $todoId = clean_text($input['todoId'] ?? '', 80);
        require_non_empty($todoId, 'Keine Aufgabe angegeben.');

        $todoIndex = find_todo_index($data, $todoId);

        if ($todoIndex === null || !user_can_manage_todo($currentUser, $data['todos'][$todoIndex])) {
            send_json(['success' => false, 'message' => 'Du darfst diese Aufgabe nicht löschen.'], 403);
        }

        array_splice($data['todos'], $todoIndex, 1);

        save_and_send($storageAdapter, $data, $currentUser, 'Aufgabe wurde gelöscht.');

    case 'set_typing':
        $channel = clean_text($input['channel'] ?? '', 20);
        $threadId = clean_optional_id($input['threadId'] ?? '');
        $recipientId = clean_optional_id($input['recipientId'] ?? '');
        $isTyping = clean_bool($input['isTyping'] ?? true);

        if (!in_array($channel, ['message', 'chat', 'family'], true)) {
            send_json(['success' => false, 'message' => 'Ungültiger Kommunikationskanal.'], 400);
        }

        if ($threadId !== '') {
            $threadIndex = find_message_thread_index($data, $threadId);

            if ($threadIndex === null || !user_can_view_message_thread($currentUser, $data['messageThreads'][$threadIndex])) {
                send_json(['success' => false, 'message' => 'Dieser Verlauf wurde nicht gefunden oder ist nicht freigegeben.'], 404);
            }

            $thread = $data['messageThreads'][$threadIndex];
            $expectedThreadType = $channel === 'chat' ? MESSAGE_THREAD_CHAT : ($channel === 'family' ? MESSAGE_THREAD_FAMILY : MESSAGE_THREAD_PERSONAL);

            if ($thread['threadType'] !== $expectedThreadType && !($channel === 'message' && $thread['threadType'] === MESSAGE_THREAD_LEGACY_DIRECT)) {
                send_json(['success' => false, 'message' => 'Der Verlauf passt nicht zum gewählten Kommunikationskanal.'], 400);
            }

            $otherParticipantIds = array_values(array_filter(
                $thread['participantIds'],
                static fn(string $participantId): bool => $participantId !== $currentUser['id']
            ));
            $recipientId = $channel === 'family' ? '' : ($otherParticipantIds[0] ?? $recipientId);
        }

        if ($channel !== 'family') {
            if ($recipientId === '' || $recipientId === $currentUser['id']) {
                send_json(['success' => false, 'message' => 'Kein gültiger Empfänger angegeben.'], 400);
            }

            $recipient = find_user_by_id($data, $recipientId);

            if ($recipient === null || $recipient['active'] !== true) {
                send_json(['success' => false, 'message' => 'Der Empfänger wurde nicht gefunden oder ist deaktiviert.'], 404);
            }
        }

        $data['typingIndicators'] = array_values(array_filter(
            $data['typingIndicators'] ?? [],
            function (array $indicator) use ($currentUser, $threadId, $recipientId, $channel): bool {
                return !(
                    $indicator['userId'] === $currentUser['id'] &&
                    $indicator['threadId'] === $threadId &&
                    $indicator['recipientId'] === $recipientId &&
                    $indicator['channel'] === $channel
                );
            }
        ));

        if ($isTyping) {
            $data['typingIndicators'][] = [
                'userId' => $currentUser['id'],
                'threadId' => $threadId,
                'recipientId' => $recipientId,
                'channel' => $channel,
                'updatedAt' => now_string(),
            ];
        }

        save_and_send($storageAdapter, $data, $currentUser, 'Tippstatus wurde aktualisiert.');

    case 'send_family_chat_message':
        $body = clean_message_body($input['body'] ?? '');

        if ($currentUser['familyId'] === '') {
            send_json(['success' => false, 'message' => 'Du bist keinem Haushalt zugeordnet und kannst keinen Familienchat nutzen.'], 400);
        }

        $members = active_family_members($data, $currentUser['familyId']);

        if (count($members) < 2) {
            send_json(['success' => false, 'message' => 'Für den Familienchat benötigt der Haushalt mindestens zwei aktive Mitglieder.'], 400);
        }

        $threadIndex = ensure_family_chat_thread($data, $currentUser['familyId'], $currentUser['id']);

        if ($threadIndex === null) {
            send_json(['success' => false, 'message' => 'Familienchat konnte nicht vorbereitet werden.'], 400);
        }

        $now = now_string();
        $threadId = $data['messageThreads'][$threadIndex]['id'];
        $data['messageThreads'][$threadIndex]['updatedAt'] = $now;
        $data['messageThreads'][$threadIndex]['lastReadAt'][$currentUser['id']] = $now;

        $data['typingIndicators'] = array_values(array_filter(
            $data['typingIndicators'] ?? [],
            function (array $indicator) use ($currentUser, $threadId): bool {
                return !(
                    $indicator['userId'] === $currentUser['id'] &&
                    $indicator['threadId'] === $threadId &&
                    $indicator['channel'] === 'family'
                );
            }
        ));

        $data['messages'][] = [
            'id' => create_id('msg'),
            'threadId' => $threadId,
            'senderId' => $currentUser['id'],
            'recipientId' => '',
            'body' => $body,
            'createdAt' => $now,
            'deletedFor' => [],
        ];

        save_and_send($storageAdapter, $data, $currentUser, 'Familienchat-Nachricht wurde gesendet.');

    case 'send_chat_message':
    case 'send_message':
        $isChatAction = $action === 'send_chat_message';
        $threadType = $isChatAction ? MESSAGE_THREAD_CHAT : MESSAGE_THREAD_PERSONAL;
        $recipientId = clean_text($input['recipientId'] ?? '', 80);
        $body = clean_message_body($input['body'] ?? '');
        require_non_empty($recipientId, 'Kein Empfänger angegeben.');

        if ($recipientId === $currentUser['id']) {
            send_json(['success' => false, 'message' => 'Du kannst dir nicht selbst eine private Nachricht senden.'], 400);
        }

        $recipient = find_user_by_id($data, $recipientId);

        if ($recipient === null || $recipient['active'] !== true) {
            send_json(['success' => false, 'message' => 'Der Empfänger wurde nicht gefunden oder ist deaktiviert.'], 404);
        }

        $now = now_string();
        $threadIndex = find_personal_thread_index($data, $currentUser['id'], $recipientId, $threadType);

        if ($threadIndex === null) {
            $thread = [
                'id' => create_id('thread'),
                'threadType' => $threadType,
                'participantIds' => [$currentUser['id'], $recipientId],
                'lastReadAt' => [
                    $currentUser['id'] => $now,
                    $recipientId => '',
                ],
                'createdBy' => $currentUser['id'],
                'createdAt' => $now,
                'updatedAt' => $now,
            ];
            $data['messageThreads'][] = $thread;
            $threadIndex = count($data['messageThreads']) - 1;
        }

        $threadId = $data['messageThreads'][$threadIndex]['id'];
        $data['messageThreads'][$threadIndex]['updatedAt'] = $now;
        $data['messageThreads'][$threadIndex]['lastReadAt'][$currentUser['id']] = $now;

        if (!isset($data['messageThreads'][$threadIndex]['lastReadAt'][$recipientId])) {
            $data['messageThreads'][$threadIndex]['lastReadAt'][$recipientId] = '';
        }

        $data['typingIndicators'] = array_values(array_filter(
            $data['typingIndicators'] ?? [],
            function (array $indicator) use ($currentUser, $threadId, $recipientId, $isChatAction): bool {
                return !(
                    $indicator['userId'] === $currentUser['id'] &&
                    $indicator['threadId'] === $threadId &&
                    $indicator['recipientId'] === $recipientId &&
                    $indicator['channel'] === ($isChatAction ? 'chat' : 'message')
                );
            }
        ));

        $data['messages'][] = [
            'id' => create_id('msg'),
            'threadId' => $threadId,
            'senderId' => $currentUser['id'],
            'recipientId' => $recipientId,
            'body' => $body,
            'createdAt' => $now,
            'deletedFor' => [],
        ];

        save_and_send($storageAdapter, $data, $currentUser, $isChatAction ? 'Chatnachricht wurde gesendet.' : 'Nachricht wurde gesendet.');

    case 'mark_thread_read':
        $threadId = clean_text($input['threadId'] ?? '', 80);
        require_non_empty($threadId, 'Kein Nachrichtenverlauf angegeben.');

        $threadIndex = find_message_thread_index($data, $threadId);

        if ($threadIndex === null || !user_can_view_message_thread($currentUser, $data['messageThreads'][$threadIndex])) {
            send_json(['success' => false, 'message' => 'Dieser Nachrichtenverlauf wurde nicht gefunden oder ist nicht freigegeben.'], 404);
        }

        if (user_is_message_participant($currentUser, $data['messageThreads'][$threadIndex])) {
            $data['messageThreads'][$threadIndex]['lastReadAt'][$currentUser['id']] = now_string();
        }

        save_and_send($storageAdapter, $data, $currentUser, 'Nachrichten wurden als gelesen markiert.');

    case 'delete_message':
        $messageId = clean_text($input['messageId'] ?? '', 80);
        require_non_empty($messageId, 'Keine Nachricht angegeben.');

        $messageIndex = find_message_index($data, $messageId);

        if ($messageIndex === null) {
            send_json(['success' => false, 'message' => 'Nachricht wurde nicht gefunden.'], 404);
        }

        $threadIndex = find_message_thread_index($data, $data['messages'][$messageIndex]['threadId']);

        if ($threadIndex === null || !user_can_view_message_thread($currentUser, $data['messageThreads'][$threadIndex])) {
            send_json(['success' => false, 'message' => 'Du darfst diese Nachricht nicht sehen.'], 403);
        }

        if (is_admin($currentUser) || $data['messages'][$messageIndex]['senderId'] === $currentUser['id']) {
            array_splice($data['messages'], $messageIndex, 1);
        } else {
            if (!in_array($currentUser['id'], $data['messages'][$messageIndex]['deletedFor'], true)) {
                $data['messages'][$messageIndex]['deletedFor'][] = $currentUser['id'];
            }
        }

        save_and_send($storageAdapter, $data, $currentUser, 'Nachricht wurde gelöscht.');

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
                    $todo['assignedTo'] = $todo['ownerId'];
                    $todo['updatedAt'] = now_string();
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

        foreach ($data['todos'] as &$todo) {
            if (($todo['assignedTo'] ?? '') === $userId) {
                $todo['assignedTo'] = '';
                $todo['updatedAt'] = now_string();
            }
            $todo['comments'] = array_values(array_filter($todo['comments'], function (array $comment) use ($userId): bool {
                return $comment['authorId'] !== $userId;
            }));
        }
        unset($todo);

        $data['messages'] = array_values(array_filter($data['messages'], function (array $message) use ($userId): bool {
            return $message['senderId'] !== $userId && $message['recipientId'] !== $userId;
        }));

        foreach ($data['messageThreads'] as &$thread) {
            $thread['participantIds'] = array_values(array_filter($thread['participantIds'], static fn(string $participantId): bool => $participantId !== $userId));
            unset($thread['lastReadAt'][$userId]);
        }
        unset($thread);

        $data['messageThreads'] = array_values(array_filter($data['messageThreads'], static fn(array $thread): bool => count($thread['participantIds']) >= 2));

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
