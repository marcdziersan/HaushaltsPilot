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
        $this->ensureFileExists();

        $json = file_get_contents($this->filePath);

        if ($json === false || trim($json) === '') {
            $data = create_default_data();
            $this->save($data);
            return $data;
        }

        $data = json_decode($json, true);

        if (!is_valid_data($data)) {
            $data = create_default_data();
            $this->save($data);
            return $data;
        }

        return $data;
    }

    public function save(array $data): void
    {
        if (!is_valid_data($data)) {
            send_json([
                'success' => false,
                'message' => 'Ungültige Datenstruktur. Speicherung abgebrochen.',
            ], 500);
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
            send_json([
                'success' => false,
                'message' => 'Daten konnten nicht serialisiert werden.',
            ], 500);
        }

        $result = file_put_contents($this->filePath, $json, LOCK_EX);

        if ($result === false) {
            send_json([
                'success' => false,
                'message' => 'JSON-Datei konnte nicht geschrieben werden.',
            ], 500);
        }
    }

    private function ensureFileExists(): void
    {
        $directory = dirname($this->filePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (!file_exists($this->filePath)) {
            $this->save(create_default_data());
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
        $activeListId = $this->loadSetting('active_list_id');

        $listRows = $this->pdo
            ->query('SELECT id, name FROM lists ORDER BY sort_order ASC, name ASC')
            ->fetchAll();

        if (count($listRows) === 0) {
            $data = create_default_data();
            $this->save($data);
            return $data;
        }

        $lists = [];

        $itemStatement = $this->pdo->prepare(
            'SELECT id, name, amount, category, done
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
                ];
            }

            $lists[] = [
                'id' => (string) $listRow['id'],
                'name' => (string) $listRow['name'],
                'items' => $items,
            ];
        }

        if (!is_string($activeListId) || !$this->listExistsInArray($lists, $activeListId)) {
            $activeListId = $lists[0]['id'];
            $this->saveSetting('active_list_id', $activeListId);
        }

        $data = [
            'lists' => $lists,
            'activeListId' => $activeListId,
        ];

        if (!is_valid_data($data)) {
            $data = create_default_data();
            $this->save($data);
        }

        return $data;
    }

    public function save(array $data): void
    {
        if (!is_valid_data($data)) {
            send_json([
                'success' => false,
                'message' => 'Ungültige Datenstruktur. Speicherung abgebrochen.',
            ], 500);
        }

        $now = date('Y-m-d H:i:s');

        try {
            $this->pdo->beginTransaction();

            $this->pdo->exec('DELETE FROM items');
            $this->pdo->exec('DELETE FROM lists');
            $this->pdo->exec('DELETE FROM app_settings');

            $listInsert = $this->pdo->prepare(
                'INSERT INTO lists (id, name, sort_order, created_at)
                 VALUES (:id, :name, :sort_order, :created_at)'
            );

            $itemInsert = $this->pdo->prepare(
                'INSERT INTO items (id, list_id, name, amount, category, done, sort_order, created_at)
                 VALUES (:id, :list_id, :name, :amount, :category, :done, :sort_order, :created_at)'
            );

            foreach ($data['lists'] as $listIndex => $list) {
                $listInsert->execute([
                    ':id' => $list['id'],
                    ':name' => $list['name'],
                    ':sort_order' => $listIndex,
                    ':created_at' => $now,
                ]);

                foreach ($list['items'] as $itemIndex => $item) {
                    $itemInsert->execute([
                        ':id' => $item['id'],
                        ':list_id' => $list['id'],
                        ':name' => $item['name'],
                        ':amount' => $item['amount'],
                        ':category' => $item['category'],
                        ':done' => $item['done'] ? 1 : 0,
                        ':sort_order' => $itemIndex,
                        ':created_at' => $now,
                    ]);
                }
            }

            $this->insertSetting('active_list_id', $data['activeListId']);

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            send_json([
                'success' => false,
                'message' => 'Datenbank konnte nicht gespeichert werden.',
            ], 500);
        }
    }

    private function loadSetting(string $key): ?string
    {
        $statement = $this->pdo->prepare(
            'SELECT setting_value FROM app_settings WHERE setting_key = :setting_key'
        );

        $statement->execute([
            ':setting_key' => $key,
        ]);

        $value = $statement->fetchColumn();

        return is_string($value) ? $value : null;
    }

    private function saveSetting(string $key, string $value): void
    {
        $this->insertSetting($key, $value);
    }

    private function insertSetting(string $key, string $value): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO app_settings (setting_key, setting_value)
             VALUES (:setting_key, :setting_value)'
        );

        $statement->execute([
            ':setting_key' => $key,
            ':setting_value' => $value,
        ]);
    }

    private function listExistsInArray(array $lists, string $listId): bool
    {
        foreach ($lists as $list) {
            if ($list['id'] === $listId) {
                return true;
            }
        }

        return false;
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

        send_json([
            'success' => false,
            'message' => 'Unbekannter Speicher-Typ in der Konfiguration.',
        ], 500);
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
            'CREATE TABLE IF NOT EXISTS app_settings (
                setting_key TEXT PRIMARY KEY,
                setting_value TEXT NOT NULL
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS lists (
                id TEXT PRIMARY KEY,
                name TEXT NOT NULL,
                sort_order INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL
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
                sort_order INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL,
                FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE
            )'
        );

        return;
    }

    if ($driver === 'mysql') {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS app_settings (
                setting_key VARCHAR(80) NOT NULL PRIMARY KEY,
                setting_value TEXT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS lists (
                id VARCHAR(80) NOT NULL PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL,
                INDEX idx_lists_sort_order (sort_order)
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
                sort_order INT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL,
                INDEX idx_items_list_id (list_id),
                INDEX idx_items_sort_order (sort_order),
                CONSTRAINT fk_items_list
                    FOREIGN KEY (list_id) REFERENCES lists(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        return;
    }

    throw new RuntimeException('Unbekannter Datenbank-Treiber.');
}
