<?php
declare(strict_types=1);

require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/storage.php';

$configFile = __DIR__ . '/config.php';
$dataDir = __DIR__ . '/data';
$message = '';
$error = '';

function ensure_data_directory(string $dataDir): void
{
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }

    $htaccess = $dataDir . '/.htaccess';

    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Options -Indexes\nRequire all denied\nDeny from all\n", LOCK_EX);
    }
}

function write_config_file(string $configFile, array $config): void
{
    $storage = $config['storage'];
    $appKey = $config['app_key'];

    $content = "<?php\n";
    $content .= "declare(strict_types=1);\n\n";
    $content .= "return [\n";
    $content .= "    'installed' => true,\n";
    $content .= "    'storage' => " . var_export($storage, true) . ",\n";
    $content .= "    'app_key' => " . var_export($appKey, true) . ",\n";
    $content .= "    'json_file' => __DIR__ . '/data/lists.json',\n";
    $content .= "    'sqlite_file' => __DIR__ . '/data/app.sqlite',\n";
    $content .= "    'mysql' => [\n";
    $content .= "        'host' => " . var_export($config['mysql']['host'], true) . ",\n";
    $content .= "        'port' => " . var_export((int) $config['mysql']['port'], true) . ",\n";
    $content .= "        'database' => " . var_export($config['mysql']['database'], true) . ",\n";
    $content .= "        'username' => " . var_export($config['mysql']['username'], true) . ",\n";
    $content .= "        'password' => " . var_export($config['mysql']['password'], true) . ",\n";
    $content .= "        'charset' => 'utf8mb4',\n";
    $content .= "    ],\n";
    $content .= "];\n";

    if (file_put_contents($configFile, $content, LOCK_EX) === false) {
        throw new RuntimeException('config.php konnte nicht geschrieben werden.');
    }
}

function install_json_storage(string $dataDir): void
{
    $jsonFile = $dataDir . '/lists.json';
    $storage = new JsonStorage($jsonFile);
    $storage->save(create_default_data());
}

function install_sqlite_storage(string $dataDir): void
{
    $sqliteFile = $dataDir . '/app.sqlite';

    $pdo = new PDO('sqlite:' . $sqliteFile);
    StorageFactory::configurePdo($pdo);
    install_storage_schema($pdo, 'sqlite');

    $storage = new PdoStorage($pdo, 'sqlite');
    $storage->save(create_default_data());
}

function install_mysql_storage(array $mysql): void
{
    $database = (string) $mysql['database'];

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
        throw new RuntimeException('Der Datenbankname darf nur Buchstaben, Zahlen und Unterstriche enthalten.');
    }

    $serverDsn = sprintf(
        'mysql:host=%s;port=%d;charset=utf8mb4',
        $mysql['host'],
        (int) $mysql['port']
    );

    $serverPdo = new PDO($serverDsn, $mysql['username'], $mysql['password']);
    StorageFactory::configurePdo($serverPdo);

    $quotedDatabase = '`' . str_replace('`', '``', $database) . '`';

    $serverPdo->exec(
        'CREATE DATABASE IF NOT EXISTS ' . $quotedDatabase .
        ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );

    $databaseDsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $mysql['host'],
        (int) $mysql['port'],
        $database
    );

    $pdo = new PDO($databaseDsn, $mysql['username'], $mysql['password']);
    StorageFactory::configurePdo($pdo);
    install_storage_schema($pdo, 'mysql');

    $storage = new PdoStorage($pdo, 'mysql');
    $storage->save(create_default_data());
}

function post_value(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_csrf_token();

        if (file_exists($configFile) && post_value('allow_overwrite') !== 'yes') {
            throw new RuntimeException('Die Anwendung ist bereits installiert. Aktiviere bewusst das Überschreiben, wenn du neu installieren möchtest.');
        }

        $storage = post_value('storage', 'json');

        if (!in_array($storage, ['json', 'sqlite', 'mysql'], true)) {
            throw new RuntimeException('Ungültiger Speichertyp.');
        }

        ensure_data_directory($dataDir);

        $mysql = [
            'host' => post_value('mysql_host', '127.0.0.1'),
            'port' => (int) post_value('mysql_port', '3306'),
            'database' => post_value('mysql_database', 'haushaltspilot'),
            'username' => post_value('mysql_username', 'root'),
            'password' => post_value('mysql_password', ''),
            'charset' => 'utf8mb4',
        ];

        $config = [
            'storage' => $storage,
            'app_key' => bin2hex(random_bytes(32)),
            'mysql' => $mysql,
        ];

        if ($storage === 'json') {
            install_json_storage($dataDir);
        }

        if ($storage === 'sqlite') {
            install_sqlite_storage($dataDir);
        }

        if ($storage === 'mysql') {
            install_mysql_storage($mysql);
        }

        write_config_file($configFile, $config);

        $message = 'Installation abgeschlossen. Die Anwendung ist einsatzbereit.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$csrfToken = get_csrf_token();
$isInstalled = file_exists($configFile);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e('HaushaltsPilot Installer') ?></title>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --danger: #dc2626;
            --success: #16a34a;
            --border: #d1d5db;
            --soft: #f9fafb;
            --shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
            --radius: 18px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #e0ecff, #f8fafc);
            color: var(--text);
            padding: 20px;
        }

        .installer {
            width: 100%;
            max-width: 780px;
            margin: 0 auto;
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
        }

        h1 { margin: 0 0 8px; }
        p { color: var(--muted); line-height: 1.5; }

        .notice, .success, .error {
            border-radius: 14px;
            padding: 12px 14px;
            margin: 14px 0;
            line-height: 1.45;
        }

        .notice { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; }
        .success { background: #ecfdf5; border: 1px solid #bbf7d0; color: #166534; }
        .error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        fieldset {
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px;
            margin: 18px 0;
            background: var(--soft);
        }

        legend { font-weight: bold; }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .field { margin-bottom: 14px; }

        input, select {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 1rem;
            background: white;
        }

        .radio-grid {
            display: grid;
            gap: 10px;
        }

        .radio-card {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
            align-items: start;
            background: white;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px;
        }

        .radio-card strong { display: block; margin-bottom: 3px; }
        .radio-card span { color: var(--muted); font-size: 0.9rem; line-height: 1.35; }

        button {
            border: none;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 1rem;
            cursor: pointer;
            background: var(--primary);
            color: white;
            font-weight: bold;
        }

        button:hover { background: var(--primary-dark); }

        .danger-check {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
            align-items: start;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 14px;
            padding: 12px;
        }

        .danger-check input { width: auto; margin-top: 3px; }

        .link-row { margin-top: 18px; }
        a { color: var(--primary); font-weight: bold; }

        @media (max-width: 560px) {
            body { padding: 12px; }
            .installer { padding: 18px; }
        }
    </style>
</head>
<body>
    <main class="installer">
        <h1>HaushaltsPilot Installer</h1>
        <p>
            Teil 06: Wähle, ob die Anwendung ihre Daten in JSON, SQLite oder MySQL/MariaDB über PDO speichern soll.
        </p>

        <div class="notice">
            JSON und SQLite legen Dateien im Ordner <strong>data/</strong> an. MySQL/MariaDB erstellt bei passenden Rechten die Datenbank und Tabellen automatisch.
        </div>

        <?php if ($message !== ''): ?>
            <div class="success"><?= e($message) ?></div>
            <p class="link-row"><a href="index.php">Zur Anwendung wechseln</a></p>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="installer.php">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <?php if ($isInstalled): ?>
                <div class="danger-check">
                    <input type="checkbox" id="allow_overwrite" name="allow_overwrite" value="yes">
                    <label for="allow_overwrite">
                        Die Anwendung ist bereits installiert. Ich möchte die Konfiguration und die Startdaten bewusst neu schreiben.
                    </label>
                </div>
            <?php endif; ?>

            <fieldset>
                <legend>Speicher auswählen</legend>

                <div class="radio-grid">
                    <label class="radio-card">
                        <input type="radio" name="storage" value="json" checked>
                        <span>
                            <strong>JSON-Datei</strong>
                            Einfachster Einstieg. Speichert in <code>data/lists.json</code>. Gut zum Lernen, nicht ideal für viele Benutzer.
                        </span>
                    </label>

                    <label class="radio-card">
                        <input type="radio" name="storage" value="sqlite">
                        <span>
                            <strong>SQLite über PDO</strong>
                            Speichert in <code>data/app.sqlite</code>. Sehr gut für lokale Demos, GitHub und kleine Projekte.
                        </span>
                    </label>

                    <label class="radio-card">
                        <input type="radio" name="storage" value="mysql">
                        <span>
                            <strong>MySQL/MariaDB über PDO</strong>
                            Für Webhosting und spätere Login-/Familienfunktionen. Datenbank und Tabellen werden automatisch angelegt, wenn der Datenbanknutzer die Rechte hat.
                        </span>
                    </label>
                </div>
            </fieldset>

            <fieldset>
                <legend>MySQL/MariaDB-Zugangsdaten</legend>

                <div class="field">
                    <label for="mysql_host">Host</label>
                    <input type="text" id="mysql_host" name="mysql_host" value="127.0.0.1" autocomplete="off">
                </div>

                <div class="field">
                    <label for="mysql_port">Port</label>
                    <input type="number" id="mysql_port" name="mysql_port" value="3306" min="1" max="65535">
                </div>

                <div class="field">
                    <label for="mysql_database">Datenbankname</label>
                    <input type="text" id="mysql_database" name="mysql_database" value="haushaltspilot" autocomplete="off">
                </div>

                <div class="field">
                    <label for="mysql_username">Benutzername</label>
                    <input type="text" id="mysql_username" name="mysql_username" value="root" autocomplete="off">
                </div>

                <div class="field">
                    <label for="mysql_password">Passwort</label>
                    <input type="password" id="mysql_password" name="mysql_password" value="" autocomplete="new-password">
                </div>
            </fieldset>

            <button type="submit">Installation starten</button>
        </form>
    </main>
</body>
</html>
