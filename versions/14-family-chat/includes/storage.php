<?php
declare(strict_types=1);

interface AppStorageInterface
{
    public function load(): array;

    public function save(array $data): void;
}

final class JsonStorage implements AppStorageInterface
{
    public function __construct(private string $filePath)
    {
        $this->ensureDirectoryExists();
    }

    public function load(): array
    {
        if (!file_exists($this->filePath)) {
            throw new RuntimeException('JSON-Datendatei fehlt. Starte den Installer erneut.');
        }

        $json = file_get_contents($this->filePath);

        if ($json === false || trim($json) === '') {
            throw new RuntimeException('JSON-Datendatei ist leer oder nicht lesbar.');
        }

        $data = json_decode($json, true);

        if (is_array($data)) {
            $data = normalize_app_data($data);
        }

        if (!is_valid_data($data)) {
            throw new RuntimeException('JSON-Datendatei enthält eine ungültige Struktur.');
        }

        return $data;
    }

    public function save(array $data): void
    {
        if (!is_valid_data($data)) {
            throw new RuntimeException('Ungültige Datenstruktur. Speicherung abgebrochen.');
        }

        $this->ensureDirectoryExists();

        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new RuntimeException('Daten konnten nicht serialisiert werden.');
        }

        if (file_put_contents($this->filePath, $json, LOCK_EX) === false) {
            throw new RuntimeException('JSON-Datei konnte nicht geschrieben werden.');
        }
    }

    private function ensureDirectoryExists(): void
    {
        $directory = dirname($this->filePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}

final class PdoStorage implements AppStorageInterface
{
    public function __construct(private PDO $pdo, private string $driver)
    {
        if ($this->driver === 'sqlite') {
            $this->pdo->exec('PRAGMA foreign_keys = ON');
        }

        install_storage_schema($this->pdo, $this->driver);
    }

    public function load(): array
    {
        $familyRows = $this->pdo
            ->query('SELECT id, name, created_by, created_at FROM families ORDER BY name ASC')
            ->fetchAll();

        $families = [];

        foreach ($familyRows as $familyRow) {
            $families[] = [
                'id' => (string) $familyRow['id'],
                'name' => (string) $familyRow['name'],
                'createdBy' => (string) $familyRow['created_by'],
                'createdAt' => (string) $familyRow['created_at'],
            ];
        }

        $userRows = $this->pdo
            ->query('SELECT id, username, display_name, password_hash, role, active, family_id, created_at FROM users ORDER BY username ASC')
            ->fetchAll();

        if (count($userRows) === 0) {
            throw new RuntimeException('Keine Benutzer vorhanden. Starte den Installer erneut.');
        }

        $users = [];

        foreach ($userRows as $userRow) {
            $users[] = [
                'id' => (string) $userRow['id'],
                'username' => (string) $userRow['username'],
                'displayName' => (string) $userRow['display_name'],
                'passwordHash' => (string) $userRow['password_hash'],
                'role' => (string) $userRow['role'],
                'active' => (bool) $userRow['active'],
                'familyId' => (string) ($userRow['family_id'] ?? ''),
                'createdAt' => (string) $userRow['created_at'],
            ];
        }

        $listRows = $this->pdo
            ->query('SELECT id, owner_id, family_id, name, list_type, is_shared, created_at FROM lists ORDER BY sort_order ASC, name ASC')
            ->fetchAll();

        $lists = [];

        $itemStatement = $this->pdo->prepare(
            'SELECT id, name, amount, category, done, created_by, created_at
             FROM items
             WHERE list_id = :list_id
             ORDER BY sort_order ASC, name ASC'
        );

        foreach ($listRows as $listRow) {
            $itemStatement->execute([':list_id' => $listRow['id']]);
            $items = [];

            foreach ($itemStatement->fetchAll() as $itemRow) {
                $items[] = [
                    'id' => (string) $itemRow['id'],
                    'name' => (string) $itemRow['name'],
                    'amount' => (string) $itemRow['amount'],
                    'category' => (string) $itemRow['category'],
                    'done' => (bool) $itemRow['done'],
                    'createdBy' => (string) $itemRow['created_by'],
                    'createdAt' => (string) $itemRow['created_at'],
                ];
            }

            $lists[] = [
                'id' => (string) $listRow['id'],
                'ownerId' => (string) $listRow['owner_id'],
                'familyId' => (string) ($listRow['family_id'] ?? ''),
                'name' => (string) $listRow['name'],
                'listType' => (string) ($listRow['list_type'] ?? LIST_TYPE_SHOPPING),
                'isShared' => (bool) $listRow['is_shared'],
                'items' => $items,
                'createdAt' => (string) $listRow['created_at'],
            ];
        }

        $activeLists = [];
        $settingsStatement = $this->pdo->query(
            "SELECT user_id, setting_value FROM user_settings WHERE setting_key = 'active_list_id'"
        );

        foreach ($settingsStatement->fetchAll() as $settingRow) {
            $activeLists[(string) $settingRow['user_id']] = (string) $settingRow['setting_value'];
        }

        $todoRows = $this->pdo
            ->query('SELECT id, owner_id, family_id, scope, title, status, priority, assigned_to, due_at, reminder_at, calendar_date, comments_json, created_at, updated_at FROM todos ORDER BY status ASC, priority DESC, due_at ASC, created_at DESC')
            ->fetchAll();

        $todos = [];

        foreach ($todoRows as $todoRow) {
            $commentsJson = (string) ($todoRow['comments_json'] ?? '[]');
            $comments = json_decode($commentsJson, true);

            if (!is_array($comments)) {
                $comments = [];
            }

            $todos[] = [
                'id' => (string) $todoRow['id'],
                'ownerId' => (string) $todoRow['owner_id'],
                'familyId' => (string) ($todoRow['family_id'] ?? ''),
                'scope' => (string) $todoRow['scope'],
                'title' => (string) $todoRow['title'],
                'status' => (string) $todoRow['status'],
                'priority' => (string) ($todoRow['priority'] ?? TODO_PRIORITY_NORMAL),
                'assignedTo' => (string) ($todoRow['assigned_to'] ?? ''),
                'dueAt' => (string) ($todoRow['due_at'] ?? ''),
                'reminderAt' => (string) ($todoRow['reminder_at'] ?? ''),
                'calendarDate' => (string) ($todoRow['calendar_date'] ?? ''),
                'comments' => $comments,
                'createdAt' => (string) $todoRow['created_at'],
                'updatedAt' => (string) ($todoRow['updated_at'] ?? $todoRow['created_at']),
            ];
        }

        $threadRows = $this->pdo
            ->query('SELECT id, family_id, title, thread_type, created_by, created_at, updated_at FROM message_threads ORDER BY updated_at DESC, created_at DESC')
            ->fetchAll();

        $messageThreads = [];
        $memberStatement = $this->pdo->prepare('SELECT user_id, last_read_at FROM thread_members WHERE thread_id = :thread_id ORDER BY user_id ASC');

        foreach ($threadRows as $threadRow) {
            $memberStatement->execute([':thread_id' => $threadRow['id']]);
            $participantIds = [];
            $lastReadAt = [];

            foreach ($memberStatement->fetchAll() as $memberRow) {
                $participantIds[] = (string) $memberRow['user_id'];
                $lastReadAt[(string) $memberRow['user_id']] = (string) ($memberRow['last_read_at'] ?? '');
            }

            $messageThreads[] = [
                'id' => (string) $threadRow['id'],
                'threadType' => (string) ($threadRow['thread_type'] ?? MESSAGE_THREAD_PERSONAL),
                'familyId' => (string) ($threadRow['family_id'] ?? ''),
                'title' => (string) ($threadRow['title'] ?? ''),
                'participantIds' => $participantIds,
                'lastReadAt' => $lastReadAt,
                'createdBy' => (string) $threadRow['created_by'],
                'createdAt' => (string) $threadRow['created_at'],
                'updatedAt' => (string) ($threadRow['updated_at'] ?? $threadRow['created_at']),
            ];
        }

        $messageRows = $this->pdo
            ->query('SELECT id, thread_id, sender_id, recipient_id, body, created_at, deleted_for_json FROM messages ORDER BY created_at ASC')
            ->fetchAll();

        $messages = [];

        foreach ($messageRows as $messageRow) {
            $deletedForJson = (string) ($messageRow['deleted_for_json'] ?? '[]');
            $deletedFor = json_decode($deletedForJson, true);

            if (!is_array($deletedFor)) {
                $deletedFor = [];
            }

            $messages[] = [
                'id' => (string) $messageRow['id'],
                'threadId' => (string) $messageRow['thread_id'],
                'senderId' => (string) $messageRow['sender_id'],
                'recipientId' => (string) ($messageRow['recipient_id'] ?? ''),
                'body' => (string) $messageRow['body'],
                'createdAt' => (string) $messageRow['created_at'],
                'deletedFor' => $deletedFor,
            ];
        }

        $typingIndicators = [];
        $typingRows = $this->pdo
            ->query('SELECT user_id, thread_id, recipient_id, channel, updated_at FROM typing_indicators ORDER BY updated_at DESC')
            ->fetchAll();

        foreach ($typingRows as $typingRow) {
            $typingIndicators[] = [
                'userId' => (string) $typingRow['user_id'],
                'threadId' => (string) ($typingRow['thread_id'] ?? ''),
                'recipientId' => (string) ($typingRow['recipient_id'] ?? ''),
                'channel' => (string) $typingRow['channel'],
                'updatedAt' => (string) $typingRow['updated_at'],
            ];
        }

        $data = [
            'families' => $families,
            'users' => $users,
            'lists' => $lists,
            'activeLists' => $activeLists,
            'todos' => $todos,
            'messageThreads' => $messageThreads,
            'messages' => $messages,
            'typingIndicators' => $typingIndicators,
        ];

        if (!is_valid_data($data)) {
            throw new RuntimeException('Datenbank enthält eine ungültige Struktur.');
        }

        return $data;
    }

    public function save(array $data): void
    {
        if (!is_valid_data($data)) {
            throw new RuntimeException('Ungültige Datenstruktur. Speicherung abgebrochen.');
        }

        try {
            $this->pdo->beginTransaction();

            $this->pdo->exec('DELETE FROM typing_indicators');
            $this->pdo->exec('DELETE FROM messages');
            $this->pdo->exec('DELETE FROM thread_members');
            $this->pdo->exec('DELETE FROM message_threads');
            $this->pdo->exec('DELETE FROM todos');
            $this->pdo->exec('DELETE FROM calendar_events');
            $this->pdo->exec('DELETE FROM items');
            $this->pdo->exec('DELETE FROM lists');
            $this->pdo->exec('DELETE FROM user_settings');
            $this->pdo->exec('DELETE FROM users');
            $this->pdo->exec('DELETE FROM families');

            $familyInsert = $this->pdo->prepare(
                'INSERT INTO families (id, name, created_by, created_at)
                 VALUES (:id, :name, :created_by, :created_at)'
            );

            foreach ($data['families'] as $family) {
                $familyInsert->execute([
                    ':id' => $family['id'],
                    ':name' => $family['name'],
                    ':created_by' => $family['createdBy'],
                    ':created_at' => $family['createdAt'],
                ]);
            }

            $userInsert = $this->pdo->prepare(
                'INSERT INTO users (id, username, display_name, password_hash, role, active, family_id, created_at)
                 VALUES (:id, :username, :display_name, :password_hash, :role, :active, :family_id, :created_at)'
            );

            foreach ($data['users'] as $user) {
                $userInsert->execute([
                    ':id' => $user['id'],
                    ':username' => $user['username'],
                    ':display_name' => $user['displayName'],
                    ':password_hash' => $user['passwordHash'],
                    ':role' => $user['role'],
                    ':active' => $user['active'] ? 1 : 0,
                    ':family_id' => $user['familyId'],
                    ':created_at' => $user['createdAt'],
                ]);
            }

            $listInsert = $this->pdo->prepare(
                'INSERT INTO lists (id, owner_id, family_id, name, list_type, is_shared, sort_order, created_at)
                 VALUES (:id, :owner_id, :family_id, :name, :list_type, :is_shared, :sort_order, :created_at)'
            );

            $itemInsert = $this->pdo->prepare(
                'INSERT INTO items (id, list_id, name, amount, category, done, created_by, sort_order, created_at)
                 VALUES (:id, :list_id, :name, :amount, :category, :done, :created_by, :sort_order, :created_at)'
            );

            foreach ($data['lists'] as $listIndex => $list) {
                $listInsert->execute([
                    ':id' => $list['id'],
                    ':owner_id' => $list['ownerId'],
                    ':family_id' => $list['familyId'],
                    ':name' => $list['name'],
                    ':list_type' => $list['listType'],
                    ':is_shared' => $list['isShared'] ? 1 : 0,
                    ':sort_order' => $listIndex,
                    ':created_at' => $list['createdAt'],
                ]);

                foreach ($list['items'] as $itemIndex => $item) {
                    $itemInsert->execute([
                        ':id' => $item['id'],
                        ':list_id' => $list['id'],
                        ':name' => $item['name'],
                        ':amount' => $item['amount'],
                        ':category' => $item['category'],
                        ':done' => $item['done'] ? 1 : 0,
                        ':created_by' => $item['createdBy'],
                        ':sort_order' => $itemIndex,
                        ':created_at' => $item['createdAt'],
                    ]);
                }
            }

            $settingInsert = $this->pdo->prepare(
                'INSERT INTO user_settings (user_id, setting_key, setting_value)
                 VALUES (:user_id, :setting_key, :setting_value)'
            );

            foreach ($data['activeLists'] as $userId => $listId) {
                $settingInsert->execute([
                    ':user_id' => $userId,
                    ':setting_key' => 'active_list_id',
                    ':setting_value' => $listId,
                ]);
            }

            $todoInsert = $this->pdo->prepare(
                'INSERT INTO todos (id, owner_id, family_id, scope, title, status, priority, assigned_to, due_at, reminder_at, calendar_date, comments_json, created_at, updated_at)
                 VALUES (:id, :owner_id, :family_id, :scope, :title, :status, :priority, :assigned_to, :due_at, :reminder_at, :calendar_date, :comments_json, :created_at, :updated_at)'
            );

            foreach ($data['todos'] as $todo) {
                $commentsJson = json_encode($todo['comments'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                if ($commentsJson === false) {
                    $commentsJson = '[]';
                }

                $todoInsert->execute([
                    ':id' => $todo['id'],
                    ':owner_id' => $todo['ownerId'],
                    ':family_id' => $todo['familyId'],
                    ':scope' => $todo['scope'],
                    ':title' => $todo['title'],
                    ':status' => $todo['status'],
                    ':priority' => $todo['priority'],
                    ':assigned_to' => $todo['assignedTo'],
                    ':due_at' => $todo['dueAt'] !== '' ? $todo['dueAt'] : null,
                    ':reminder_at' => $todo['reminderAt'] !== '' ? $todo['reminderAt'] : null,
                    ':calendar_date' => $todo['calendarDate'] !== '' ? $todo['calendarDate'] : null,
                    ':comments_json' => $commentsJson,
                    ':created_at' => $todo['createdAt'],
                    ':updated_at' => $todo['updatedAt'],
                ]);
            }


            $threadInsert = $this->pdo->prepare(
                'INSERT INTO message_threads (id, family_id, title, thread_type, created_by, created_at, updated_at)
                 VALUES (:id, :family_id, :title, :thread_type, :created_by, :created_at, :updated_at)'
            );
            $memberInsert = $this->pdo->prepare(
                'INSERT INTO thread_members (thread_id, user_id, last_read_at)
                 VALUES (:thread_id, :user_id, :last_read_at)'
            );
            $messageInsert = $this->pdo->prepare(
                'INSERT INTO messages (id, thread_id, sender_id, recipient_id, body, created_at, deleted_for_json)
                 VALUES (:id, :thread_id, :sender_id, :recipient_id, :body, :created_at, :deleted_for_json)'
            );

            foreach ($data['messageThreads'] as $thread) {
                $threadInsert->execute([
                    ':id' => $thread['id'],
                    ':family_id' => $thread['familyId'] ?? '',
                    ':title' => $thread['title'] ?? '',
                    ':thread_type' => $thread['threadType'],
                    ':created_by' => $thread['createdBy'],
                    ':created_at' => $thread['createdAt'],
                    ':updated_at' => $thread['updatedAt'],
                ]);

                foreach ($thread['participantIds'] as $participantId) {
                    $memberInsert->execute([
                        ':thread_id' => $thread['id'],
                        ':user_id' => $participantId,
                        ':last_read_at' => ($thread['lastReadAt'][$participantId] ?? '') !== '' ? $thread['lastReadAt'][$participantId] : null,
                    ]);
                }
            }

            foreach ($data['messages'] as $message) {
                $deletedForJson = json_encode($message['deletedFor'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                if ($deletedForJson === false) {
                    $deletedForJson = '[]';
                }

                $messageInsert->execute([
                    ':id' => $message['id'],
                    ':thread_id' => $message['threadId'],
                    ':sender_id' => $message['senderId'],
                    ':recipient_id' => $message['recipientId'],
                    ':body' => $message['body'],
                    ':created_at' => $message['createdAt'],
                    ':deleted_for_json' => $deletedForJson,
                ]);
            }

            $typingInsert = $this->pdo->prepare(
                'INSERT INTO typing_indicators (user_id, thread_id, recipient_id, channel, updated_at)
                 VALUES (:user_id, :thread_id, :recipient_id, :channel, :updated_at)'
            );

            foreach ($data['typingIndicators'] as $indicator) {
                $typingInsert->execute([
                    ':user_id' => $indicator['userId'],
                    ':thread_id' => $indicator['threadId'],
                    ':recipient_id' => $indicator['recipientId'],
                    ':channel' => $indicator['channel'],
                    ':updated_at' => $indicator['updatedAt'],
                ]);
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw new RuntimeException('Datenbank konnte nicht gespeichert werden.');
        }
    }
}

final class StorageFactory
{
    public static function create(array $config): AppStorageInterface
    {
        $storage = $config['storage'] ?? '';

        if ($storage === 'json') {
            return new JsonStorage((string) $config['json_file']);
        }

        if ($storage === 'sqlite') {
            $sqliteFile = (string) $config['sqlite_file'];
            $directory = dirname($sqliteFile);

            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $pdo = new PDO('sqlite:' . $sqliteFile);
            self::configurePdo($pdo);

            return new PdoStorage($pdo, 'sqlite');
        }

        if ($storage === 'mysql') {
            $mysql = $config['mysql'] ?? [];
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $mysql['host'] ?? '127.0.0.1',
                (int) ($mysql['port'] ?? 3306),
                $mysql['database'] ?? '',
                $mysql['charset'] ?? 'utf8mb4'
            );

            $pdo = new PDO(
                $dsn,
                (string) ($mysql['username'] ?? ''),
                (string) ($mysql['password'] ?? '')
            );

            self::configurePdo($pdo);

            return new PdoStorage($pdo, 'mysql');
        }

        throw new RuntimeException('Unbekannter Speicher-Typ in der Konfiguration.');
    }

    public static function configurePdo(PDO $pdo): void
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }
}

function install_storage_schema(PDO $pdo, string $driver): void
{
    if ($driver === 'sqlite') {
        $pdo->exec('PRAGMA foreign_keys = ON');

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS families (
                id TEXT PRIMARY KEY,
                name TEXT NOT NULL,
                created_by TEXT NOT NULL,
                created_at TEXT NOT NULL
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS users (
                id TEXT PRIMARY KEY,
                username TEXT NOT NULL UNIQUE,
                display_name TEXT NOT NULL,
                password_hash TEXT NOT NULL,
                role TEXT NOT NULL CHECK(role IN (\'admin\', \'user\')),
                active INTEGER NOT NULL DEFAULT 1,
                family_id TEXT NOT NULL DEFAULT \'\',
                created_at TEXT NOT NULL
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS lists (
                id TEXT PRIMARY KEY,
                owner_id TEXT NOT NULL,
                family_id TEXT NOT NULL DEFAULT \'\',
                name TEXT NOT NULL,
                list_type TEXT NOT NULL DEFAULT \'shopping\',
                is_shared INTEGER NOT NULL DEFAULT 0,
                sort_order INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL,
                FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS items (
                id TEXT PRIMARY KEY,
                list_id TEXT NOT NULL,
                name TEXT NOT NULL,
                amount TEXT NOT NULL DEFAULT \'\',
                category TEXT NOT NULL,
                done INTEGER NOT NULL DEFAULT 0,
                created_by TEXT NOT NULL,
                sort_order INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL,
                FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS user_settings (
                user_id TEXT NOT NULL,
                setting_key TEXT NOT NULL,
                setting_value TEXT NOT NULL,
                PRIMARY KEY (user_id, setting_key),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )'
        );

        install_future_schema($pdo, 'sqlite');
        return;
    }

    if ($driver === 'mysql') {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS families (
                id VARCHAR(80) NOT NULL PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                created_by VARCHAR(80) NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_families_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS users (
                id VARCHAR(80) NOT NULL PRIMARY KEY,
                username VARCHAR(60) NOT NULL UNIQUE,
                display_name VARCHAR(120) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM(\'admin\', \'user\') NOT NULL DEFAULT \'user\',
                active TINYINT(1) NOT NULL DEFAULT 1,
                family_id VARCHAR(80) NOT NULL DEFAULT \'\',
                created_at DATETIME NOT NULL,
                INDEX idx_users_role (role),
                INDEX idx_users_active (active),
                INDEX idx_users_family_id (family_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS lists (
                id VARCHAR(80) NOT NULL PRIMARY KEY,
                owner_id VARCHAR(80) NOT NULL,
                family_id VARCHAR(80) NOT NULL DEFAULT \'\',
                name VARCHAR(120) NOT NULL,
                list_type ENUM(\'shopping\', \'household\', \'other\') NOT NULL DEFAULT \'shopping\',
                is_shared TINYINT(1) NOT NULL DEFAULT 0,
                sort_order INT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL,
                INDEX idx_lists_owner_id (owner_id),
                INDEX idx_lists_family_id (family_id),
                INDEX idx_lists_shared (is_shared),
                INDEX idx_lists_sort_order (sort_order),
                CONSTRAINT fk_lists_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS items (
                id VARCHAR(80) NOT NULL PRIMARY KEY,
                list_id VARCHAR(80) NOT NULL,
                name VARCHAR(160) NOT NULL,
                amount VARCHAR(80) NOT NULL DEFAULT \'\',
                category VARCHAR(80) NOT NULL,
                done TINYINT(1) NOT NULL DEFAULT 0,
                created_by VARCHAR(80) NOT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL,
                INDEX idx_items_list_id (list_id),
                INDEX idx_items_created_by (created_by),
                INDEX idx_items_sort_order (sort_order),
                CONSTRAINT fk_items_list FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE,
                CONSTRAINT fk_items_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS user_settings (
                user_id VARCHAR(80) NOT NULL,
                setting_key VARCHAR(80) NOT NULL,
                setting_value TEXT NOT NULL,
                PRIMARY KEY (user_id, setting_key),
                CONSTRAINT fk_user_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        install_future_schema($pdo, 'mysql');
        return;
    }

    throw new RuntimeException('Unbekannter Datenbank-Treiber.');
}

function try_add_schema_column(PDO $pdo, string $sql): void
{
    try {
        $pdo->exec($sql);
    } catch (Throwable $exception) {
        // Spalte existiert bereits oder der Treiber meldet einen harmlosen Migrationskonflikt.
    }
}

function install_future_schema(PDO $pdo, string $driver): void
{
    if ($driver === 'sqlite') {
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS message_threads (
    id TEXT PRIMARY KEY,
    family_id TEXT NOT NULL DEFAULT '',
    title TEXT NOT NULL DEFAULT '',
    thread_type TEXT NOT NULL DEFAULT 'personal_message',
    created_by TEXT NOT NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL DEFAULT '',
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
)
SQL);
        try_add_schema_column($pdo, "ALTER TABLE message_threads ADD COLUMN updated_at TEXT NOT NULL DEFAULT ''");
        try_add_schema_column($pdo, "ALTER TABLE message_threads ADD COLUMN family_id TEXT NOT NULL DEFAULT ''");
        try_add_schema_column($pdo, "ALTER TABLE message_threads ADD COLUMN title TEXT NOT NULL DEFAULT ''");

        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS thread_members (
    thread_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    last_read_at TEXT NOT NULL DEFAULT '',
    PRIMARY KEY (thread_id, user_id),
    FOREIGN KEY (thread_id) REFERENCES message_threads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)
SQL);
        try_add_schema_column($pdo, "ALTER TABLE thread_members ADD COLUMN last_read_at TEXT NOT NULL DEFAULT ''");

        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS messages (
    id TEXT PRIMARY KEY,
    thread_id TEXT NOT NULL,
    sender_id TEXT NOT NULL,
    recipient_id TEXT NOT NULL DEFAULT '',
    body TEXT NOT NULL,
    created_at TEXT NOT NULL,
    deleted_for_json TEXT NOT NULL DEFAULT '[]',
    FOREIGN KEY (thread_id) REFERENCES message_threads(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
)
SQL);
        try_add_schema_column($pdo, "ALTER TABLE messages ADD COLUMN recipient_id TEXT NOT NULL DEFAULT ''");
        try_add_schema_column($pdo, "ALTER TABLE messages ADD COLUMN deleted_for_json TEXT NOT NULL DEFAULT '[]'");

        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS typing_indicators (
    user_id TEXT NOT NULL,
    thread_id TEXT NOT NULL DEFAULT '',
    recipient_id TEXT NOT NULL DEFAULT '',
    channel TEXT NOT NULL DEFAULT 'message',
    updated_at TEXT NOT NULL,
    PRIMARY KEY (user_id, channel, thread_id, recipient_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)
SQL);

        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS todos (
    id TEXT PRIMARY KEY,
    owner_id TEXT NOT NULL,
    family_id TEXT NOT NULL DEFAULT '',
    scope TEXT NOT NULL DEFAULT 'private',
    title TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'open',
    priority TEXT NOT NULL DEFAULT 'normal',
    assigned_to TEXT NOT NULL DEFAULT '',
    due_at TEXT NULL,
    reminder_at TEXT NULL,
    calendar_date TEXT NULL,
    comments_json TEXT NOT NULL DEFAULT '[]',
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL DEFAULT '',
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
)
SQL);

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS calendar_events (
                id TEXT PRIMARY KEY,
                owner_id TEXT NOT NULL,
                family_id TEXT NOT NULL DEFAULT \'\',
                scope TEXT NOT NULL DEFAULT \'private\',
                title TEXT NOT NULL,
                starts_at TEXT NOT NULL,
                ends_at TEXT NULL,
                created_at TEXT NOT NULL,
                FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
            )'
        );

        return;
    }

    if ($driver === 'mysql') {
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS message_threads (
    id VARCHAR(80) NOT NULL PRIMARY KEY,
    family_id VARCHAR(80) NOT NULL DEFAULT '',
    title VARCHAR(160) NOT NULL DEFAULT '',
    thread_type ENUM('direct', 'personal_message', 'one_to_one_chat', 'family_chat') NOT NULL DEFAULT 'personal_message',
    created_by VARCHAR(80) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_message_threads_family_id (family_id),
    INDEX idx_message_threads_created_by (created_by),
    CONSTRAINT fk_message_threads_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS thread_members (
    thread_id VARCHAR(80) NOT NULL,
    user_id VARCHAR(80) NOT NULL,
    last_read_at DATETIME NULL,
    PRIMARY KEY (thread_id, user_id),
    CONSTRAINT fk_thread_members_thread FOREIGN KEY (thread_id) REFERENCES message_threads(id) ON DELETE CASCADE,
    CONSTRAINT fk_thread_members_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS messages (
    id VARCHAR(80) NOT NULL PRIMARY KEY,
    thread_id VARCHAR(80) NOT NULL,
    sender_id VARCHAR(80) NOT NULL,
    recipient_id VARCHAR(80) NOT NULL DEFAULT '',
    body TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    deleted_for_json TEXT NOT NULL,
    INDEX idx_messages_thread_id (thread_id),
    INDEX idx_messages_sender_id (sender_id),
    INDEX idx_messages_recipient_id (recipient_id),
    CONSTRAINT fk_messages_thread FOREIGN KEY (thread_id) REFERENCES message_threads(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
        try_add_schema_column($pdo, 'ALTER TABLE message_threads ADD COLUMN updated_at DATETIME NULL');
        try_add_schema_column($pdo, "ALTER TABLE message_threads ADD COLUMN family_id VARCHAR(80) NOT NULL DEFAULT ''");
        try_add_schema_column($pdo, "ALTER TABLE message_threads ADD COLUMN title VARCHAR(160) NOT NULL DEFAULT ''");
        try_add_schema_column($pdo, "ALTER TABLE message_threads MODIFY thread_type ENUM('direct', 'personal_message', 'one_to_one_chat', 'family_chat') NOT NULL DEFAULT 'personal_message'");
        try_add_schema_column($pdo, 'ALTER TABLE thread_members ADD COLUMN last_read_at DATETIME NULL');
        try_add_schema_column($pdo, "ALTER TABLE messages ADD COLUMN recipient_id VARCHAR(80) NOT NULL DEFAULT ''");
        try_add_schema_column($pdo, 'ALTER TABLE messages ADD COLUMN deleted_for_json TEXT NOT NULL');

        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS typing_indicators (
    user_id VARCHAR(80) NOT NULL,
    thread_id VARCHAR(80) NOT NULL DEFAULT '',
    recipient_id VARCHAR(80) NOT NULL DEFAULT '',
    channel ENUM('message', 'chat', 'family') NOT NULL DEFAULT 'message',
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (user_id, channel, thread_id, recipient_id),
    INDEX idx_typing_indicators_recipient_id (recipient_id),
    INDEX idx_typing_indicators_thread_id (thread_id),
    CONSTRAINT fk_typing_indicators_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS todos (
    id VARCHAR(80) NOT NULL PRIMARY KEY,
    owner_id VARCHAR(80) NOT NULL,
    family_id VARCHAR(80) NOT NULL DEFAULT '',
    scope ENUM('private', 'family') NOT NULL DEFAULT 'private',
    title VARCHAR(180) NOT NULL,
    status ENUM('open', 'done') NOT NULL DEFAULT 'open',
    priority ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
    assigned_to VARCHAR(80) NOT NULL DEFAULT '',
    due_at DATETIME NULL,
    reminder_at DATETIME NULL,
    calendar_date DATETIME NULL,
    comments_json TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_todos_owner_id (owner_id),
    INDEX idx_todos_assigned_to (assigned_to),
    INDEX idx_todos_priority (priority),
    INDEX idx_todos_family_id (family_id),
    INDEX idx_todos_scope (scope),
    CONSTRAINT fk_todos_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS calendar_events (
                id VARCHAR(80) NOT NULL PRIMARY KEY,
                owner_id VARCHAR(80) NOT NULL,
                family_id VARCHAR(80) NOT NULL DEFAULT \'\',
                scope ENUM(\'private\', \'family\') NOT NULL DEFAULT \'private\',
                title VARCHAR(180) NOT NULL,
                starts_at DATETIME NOT NULL,
                ends_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_calendar_events_owner_id (owner_id),
                INDEX idx_calendar_events_family_id (family_id),
                INDEX idx_calendar_events_scope (scope),
                CONSTRAINT fk_calendar_events_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }
}

function create_install_data(string $adminUsername, string $adminDisplayName, string $adminPassword): array
{
    $adminId = create_id('user');
    $familyId = create_id('family');
    $listId = create_id('list');
    $now = now_string();

    return [
        'families' => [
            [
                'id' => $familyId,
                'name' => 'Mein Haushalt',
                'createdBy' => $adminId,
                'createdAt' => $now,
            ],
        ],
        'users' => [
            [
                'id' => $adminId,
                'username' => $adminUsername,
                'displayName' => $adminDisplayName !== '' ? $adminDisplayName : $adminUsername,
                'passwordHash' => password_hash($adminPassword, PASSWORD_DEFAULT),
                'role' => ROLE_ADMIN,
                'active' => true,
                'familyId' => $familyId,
                'createdAt' => $now,
            ],
        ],
        'lists' => [
            [
                'id' => $listId,
                'ownerId' => $adminId,
                'familyId' => $familyId,
                'name' => 'Einkauf',
                'listType' => LIST_TYPE_SHOPPING,
                'isShared' => false,
                'items' => [],
                'createdAt' => $now,
            ],
        ],
        'activeLists' => [
            $adminId => $listId,
        ],
        'todos' => [
            [
                'id' => create_id('todo'),
                'ownerId' => $adminId,
                'familyId' => $familyId,
                'scope' => TODO_SCOPE_PRIVATE,
                'title' => 'Teil 14 testen: Familienchat mit Haushaltsmitgliedern prüfen',
                'status' => TODO_STATUS_OPEN,
                'priority' => TODO_PRIORITY_NORMAL,
                'assignedTo' => $adminId,
                'dueAt' => '',
                'reminderAt' => '',
                'calendarDate' => '',
                'comments' => [],
                'createdAt' => $now,
                'updatedAt' => $now,
            ],
            [
                'id' => create_id('todo'),
                'ownerId' => $adminId,
                'familyId' => $familyId,
                'scope' => TODO_SCOPE_FAMILY,
                'title' => 'Familienaufgabe anlegen und gemeinsam erledigen',
                'status' => TODO_STATUS_OPEN,
                'priority' => TODO_PRIORITY_NORMAL,
                'assignedTo' => $adminId,
                'dueAt' => '',
                'reminderAt' => '',
                'calendarDate' => '',
                'comments' => [],
                'createdAt' => $now,
                'updatedAt' => $now,
            ],
        ],
        'messageThreads' => [],
        'messages' => [],
        'typingIndicators' => [],
    ];
}
