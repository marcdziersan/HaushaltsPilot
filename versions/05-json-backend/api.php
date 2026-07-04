<?php
declare(strict_types=1);

require __DIR__ . '/includes/security.php';

$action = $_GET['action'] ?? '';

if (!is_string($action) || $action === '') {
    send_json([
        'success' => false,
        'message' => 'Keine Aktion angegeben.',
    ], 400);
}

if ($action === 'load') {
    require_method('GET');

    send_json([
        'success' => true,
        'data' => read_data(),
    ]);
}

require_method('POST');
require_csrf_token();

$input = get_json_input();
$data = read_data();

switch ($action) {
    case 'create_list':
        $name = clean_text($input['name'] ?? '', MAX_LIST_NAME_LENGTH);
        require_non_empty($name, 'Bitte gib einen Listennamen ein.');

        foreach ($data['lists'] as $list) {
            if (mb_strtolower($list['name']) === mb_strtolower($name)) {
                send_json([
                    'success' => false,
                    'message' => 'Eine Liste mit diesem Namen gibt es bereits.',
                ], 409);
            }
        }

        $newList = [
            'id' => create_id('list'),
            'name' => $name,
            'items' => [],
        ];

        $data['lists'][] = $newList;
        $data['activeListId'] = $newList['id'];

        write_data($data);

        send_json([
            'success' => true,
            'message' => 'Liste wurde erstellt.',
            'data' => $data,
        ]);

    case 'set_active_list':
        $listId = clean_text($input['listId'] ?? '', 80);
        require_non_empty($listId, 'Keine Liste angegeben.');

        $listIndex = find_list_index($data, $listId);

        if ($listIndex === null) {
            send_json([
                'success' => false,
                'message' => 'Liste wurde nicht gefunden.',
            ], 404);
        }

        $data['activeListId'] = $listId;
        write_data($data);

        send_json([
            'success' => true,
            'message' => 'Aktive Liste wurde geändert.',
            'data' => $data,
        ]);

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

        if ($listIndex === null) {
            send_json([
                'success' => false,
                'message' => 'Liste wurde nicht gefunden.',
            ], 404);
        }

        array_splice($data['lists'], $listIndex, 1);

        if ($data['activeListId'] === $listId) {
            $data['activeListId'] = $data['lists'][0]['id'];
        }

        write_data($data);

        send_json([
            'success' => true,
            'message' => 'Liste wurde gelöscht.',
            'data' => $data,
        ]);

    case 'add_item':
        $listId = clean_text($input['listId'] ?? '', 80);
        $name = clean_text($input['name'] ?? '', MAX_ITEM_NAME_LENGTH);
        $amount = clean_text($input['amount'] ?? '', MAX_AMOUNT_LENGTH);
        $category = clean_category($input['category'] ?? '');

        require_non_empty($listId, 'Keine Liste angegeben.');
        require_non_empty($name, 'Bitte gib einen Artikelnamen ein.');

        $listIndex = find_list_index($data, $listId);

        if ($listIndex === null) {
            send_json([
                'success' => false,
                'message' => 'Liste wurde nicht gefunden.',
            ], 404);
        }

        $data['lists'][$listIndex]['items'][] = [
            'id' => create_id('item'),
            'name' => $name,
            'amount' => $amount,
            'category' => $category,
            'done' => false,
        ];

        write_data($data);

        send_json([
            'success' => true,
            'message' => 'Artikel wurde hinzugefügt.',
            'data' => $data,
        ]);

    case 'toggle_item':
        $listId = clean_text($input['listId'] ?? '', 80);
        $itemId = clean_text($input['itemId'] ?? '', 80);

        require_non_empty($listId, 'Keine Liste angegeben.');
        require_non_empty($itemId, 'Kein Artikel angegeben.');

        $listIndex = find_list_index($data, $listId);

        if ($listIndex === null) {
            send_json([
                'success' => false,
                'message' => 'Liste wurde nicht gefunden.',
            ], 404);
        }

        $itemIndex = find_item_index($data['lists'][$listIndex], $itemId);

        if ($itemIndex === null) {
            send_json([
                'success' => false,
                'message' => 'Artikel wurde nicht gefunden.',
            ], 404);
        }

        $currentStatus = $data['lists'][$listIndex]['items'][$itemIndex]['done'];
        $data['lists'][$listIndex]['items'][$itemIndex]['done'] = !$currentStatus;

        write_data($data);

        send_json([
            'success' => true,
            'message' => 'Artikelstatus wurde geändert.',
            'data' => $data,
        ]);

    case 'delete_item':
        $listId = clean_text($input['listId'] ?? '', 80);
        $itemId = clean_text($input['itemId'] ?? '', 80);

        require_non_empty($listId, 'Keine Liste angegeben.');
        require_non_empty($itemId, 'Kein Artikel angegeben.');

        $listIndex = find_list_index($data, $listId);

        if ($listIndex === null) {
            send_json([
                'success' => false,
                'message' => 'Liste wurde nicht gefunden.',
            ], 404);
        }

        $itemIndex = find_item_index($data['lists'][$listIndex], $itemId);

        if ($itemIndex === null) {
            send_json([
                'success' => false,
                'message' => 'Artikel wurde nicht gefunden.',
            ], 404);
        }

        array_splice($data['lists'][$listIndex]['items'], $itemIndex, 1);

        write_data($data);

        send_json([
            'success' => true,
            'message' => 'Artikel wurde gelöscht.',
            'data' => $data,
        ]);

    case 'reset':
        $data = create_default_data();
        write_data($data);

        send_json([
            'success' => true,
            'message' => 'Alle Daten wurden zurückgesetzt.',
            'data' => $data,
        ]);

    default:
        send_json([
            'success' => false,
            'message' => 'Unbekannte Aktion.',
        ], 404);
}