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
        $this->ensureFileExists();
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

        $directory = dirname($this->filePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new RuntimeException('Daten konnten nicht serialisiert werden.');
        }

        $result = file_put_contents($this->filePath, $json, LOCK_EX);

        if ($result === false) {
            throw new RuntimeException('JSON-Datei konnte nicht geschrieben werden.');
        }
    }

    private function ensureFileExists(): void
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
        $userRows = $this->pdo
            ->query('SELECT id, username, display_name, password_hash, role, active, created_at FROM users ORDER BY username ASC')
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
                'createdAt' => (string) $userRow['created_at'],
            ];
        }

        $listRows = $this->pdo
            ->query('SELECT id, owner_id, name, is_shared, created_at FROM lists ORDER BY sort_order ASC, name ASC')
            ->fetchAll();

        $lists = [];

        $itemStatement = $this->pdo->prepare(
            'SELECT id, name, amount, category, done, created_by, created_at
             FROM items
             WHERE list_id = :list_id
             ORDER BY sort_order ASC, name ASC'
        );

        foreach ($listRows as $listRow) {
            $itemStatement->execute([
                ':list_id' => $listRow['id'],
            ]);

            $itemRows = $itemStatement->fetchAll();
            $items = [];

            foreach ($itemRows as $itemRow) {
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
                'name' => (string) $listRow['name'],
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

        $data = [
            'users' => $users,
            'lists' => $lists,
            'activeLists' => $activeLists,
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

            $this->pdo->exec('DELETE FROM items');
            $this->pdo->exec('DELETE FROM lists');
            $this->pdo->exec('DELETE FROM user_settings');
            $this->pdo->exec('DELETE FROM users');

            $userInsert = $this->pdo->prepare(
                'INSERT INTO users (id, username, display_name, password_hash, role, active, created_at)
                 VALUES (:id, :username, :display_name, :password_hash, :role, :active, :created_at)'
            );

            foreach ($data['users'] as $user) {
                $userInsert->execute([
                    ':id' => $user['id'],
                    ':username' => $user['username'],
                    ':display_name' => $user['displayName'],
                    ':password_hash' => $user['passwordHash'],
                    ':role' => $user['role'],
                    ':active' => $user['active'] ? 1 : 0,
                    ':created_at' => $user['createdAt'],
                ]);
            }

            $listInsert = $this->pdo->prepare(
                'INSERT INTO lists (id, owner_id, name, is_shared, sort_order, created_at)
                 VALUES (:id, :owner_id, :name, :is_shared, :sort_order, :created_at)'
            );

            $itemInsert = $this->pdo->prepare(
                'INSERT INTO items (id, list_id, name, amount, category, done, created_by, sort_order, created_at)
                 VALUES (:id, :list_id, :name, :amount, :category, :done, :created_by, :sort_order, :created_at)'
            );

            foreach ($data['lists'] as $listIndex => $list) {
                $listInsert->execute([
                    ':id' => $list['id'],
                    ':owner_id' => $list['ownerId'],
                    ':name' => $list['name'],
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
            'CREATE TABLE IF NOT EXISTS users (
                id TEXT PRIMARY KEY,
                username TEXT NOT NULL UNIQUE,
                display_name TEXT NOT NULL,
                password_hash TEXT NOT NULL,
                role TEXT NOT NULL CHECK(role IN (\'admin\', \'user\')),
                active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS lists (
                id TEXT PRIMARY KEY,
                owner_id TEXT NOT NULL,
                name TEXT NOT NULL,
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
            'CREATE TABLE IF NOT EXISTS users (
                id VARCHAR(80) NOT NULL PRIMARY KEY,
                username VARCHAR(60) NOT NULL UNIQUE,
                display_name VARCHAR(120) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM(\'admin\', \'user\') NOT NULL DEFAULT \'user\',
                active TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL,
                INDEX idx_users_role (role),
                INDEX idx_users_active (active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS lists (
                id VARCHAR(80) NOT NULL PRIMARY KEY,
                owner_id VARCHAR(80) NOT NULL,
                name VARCHAR(120) NOT NULL,
                is_shared TINYINT(1) NOT NULL DEFAULT 0,
                sort_order INT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL,
                INDEX idx_lists_owner_id (owner_id),
                INDEX idx_lists_shared (is_shared),
                INDEX idx_lists_sort_order (sort_order),
                CONSTRAINT fk_lists_owner
                    FOREIGN KEY (owner_id) REFERENCES users(id)
                    ON DELETE CASCADE
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
                CONSTRAINT fk_items_list
                    FOREIGN KEY (list_id) REFERENCES lists(id)
                    ON DELETE CASCADE,
                CONSTRAINT fk_items_created_by
                    FOREIGN KEY (created_by) REFERENCES users(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS user_settings (
                user_id VARCHAR(80) NOT NULL,
                setting_key VARCHAR(80) NOT NULL,
                setting_value TEXT NOT NULL,
                PRIMARY KEY (user_id, setting_key),
                CONSTRAINT fk_user_settings_user
                    FOREIGN KEY (user_id) REFERENCES users(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        install_future_schema($pdo, 'mysql');
        return;
    }

    throw new RuntimeException('Unbekannter Datenbank-Treiber.');
}

function install_future_schema(PDO $pdo, string $driver): void
{
    if ($driver === 'sqlite') {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS message_threads (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL DEFAULT \'\',
                thread_type TEXT NOT NULL DEFAULT \'direct\',
                created_by TEXT NOT NULL,
                created_at TEXT NOT NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS thread_members (
                thread_id TEXT NOT NULL,
                user_id TEXT NOT NULL,
                PRIMARY KEY (thread_id, user_id),
                FOREIGN KEY (thread_id) REFERENCES message_threads(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS messages (
                id TEXT PRIMARY KEY,
                thread_id TEXT NOT NULL,
                sender_id TEXT NOT NULL,
                body TEXT NOT NULL,
                created_at TEXT NOT NULL,
                FOREIGN KEY (thread_id) REFERENCES message_threads(id) ON DELETE CASCADE,
                FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS todos (
                id TEXT PRIMARY KEY,
                owner_id TEXT NOT NULL,
                scope TEXT NOT NULL DEFAULT \'private\',
                title TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT \'open\',
                due_at TEXT NULL,
                created_at TEXT NOT NULL,
                FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS calendar_events (
                id TEXT PRIMARY KEY,
                owner_id TEXT NOT NULL,
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
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS message_threads (
                id VARCHAR(80) NOT NULL PRIMARY KEY,
                title VARCHAR(160) NOT NULL DEFAULT \'\',
                thread_type ENUM(\'direct\', \'family\') NOT NULL DEFAULT \'direct\',
                created_by VARCHAR(80) NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_message_threads_created_by (created_by),
                CONSTRAINT fk_message_threads_created_by
                    FOREIGN KEY (created_by) REFERENCES users(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS thread_members (
                thread_id VARCHAR(80) NOT NULL,
                user_id VARCHAR(80) NOT NULL,
                PRIMARY KEY (thread_id, user_id),
                CONSTRAINT fk_thread_members_thread
                    FOREIGN KEY (thread_id) REFERENCES message_threads(id)
                    ON DELETE CASCADE,
                CONSTRAINT fk_thread_members_user
                    FOREIGN KEY (user_id) REFERENCES users(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS messages (
                id VARCHAR(80) NOT NULL PRIMARY KEY,
                thread_id VARCHAR(80) NOT NULL,
                sender_id VARCHAR(80) NOT NULL,
                body TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_messages_thread_id (thread_id),
                INDEX idx_messages_sender_id (sender_id),
                CONSTRAINT fk_messages_thread
                    FOREIGN KEY (thread_id) REFERENCES message_threads(id)
                    ON DELETE CASCADE,
                CONSTRAINT fk_messages_sender
                    FOREIGN KEY (sender_id) REFERENCES users(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS todos (
                id VARCHAR(80) NOT NULL PRIMARY KEY,
                owner_id VARCHAR(80) NOT NULL,
                scope ENUM(\'private\', \'shared\') NOT NULL DEFAULT \'private\',
                title VARCHAR(180) NOT NULL,
                status ENUM(\'open\', \'done\') NOT NULL DEFAULT \'open\',
                due_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_todos_owner_id (owner_id),
                INDEX idx_todos_scope (scope),
                CONSTRAINT fk_todos_owner
                    FOREIGN KEY (owner_id) REFERENCES users(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS calendar_events (
                id VARCHAR(80) NOT NULL PRIMARY KEY,
                owner_id VARCHAR(80) NOT NULL,
                scope ENUM(\'private\', \'shared\') NOT NULL DEFAULT \'private\',
                title VARCHAR(180) NOT NULL,
                starts_at DATETIME NOT NULL,
                ends_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_calendar_events_owner_id (owner_id),
                INDEX idx_calendar_events_scope (scope),
                CONSTRAINT fk_calendar_events_owner
                    FOREIGN KEY (owner_id) REFERENCES users(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }
}

function create_install_data(string $adminUsername, string $adminDisplayName, string $adminPassword): array
{
    $adminId = create_id('user');
    $listId = create_id('list');
    $now = now_string();

    return [
        'users' => [
            [
                'id' => $adminId,
                'username' => $adminUsername,
                'displayName' => $adminDisplayName !== '' ? $adminDisplayName : $adminUsername,
                'passwordHash' => password_hash($adminPassword, PASSWORD_DEFAULT),
                'role' => ROLE_ADMIN,
                'active' => true,
                'createdAt' => $now,
            ],
        ],
        'lists' => [
            [
                'id' => $listId,
                'ownerId' => $adminId,
                'name' => 'Einkauf',
                'isShared' => false,
                'items' => [],
                'createdAt' => $now,
            ],
        ],
        'activeLists' => [
            $adminId => $listId,
        ],
    ];
}
