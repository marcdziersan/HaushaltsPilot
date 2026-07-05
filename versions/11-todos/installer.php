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
    $content = "<?php\n";
    $content .= "declare(strict_types=1);\n\n";
    $content .= "return [\n";
    $content .= "    'installed' => true,\n";
    $content .= "    'storage' => " . var_export($config['storage'], true) . ",\n";
    $content .= "    'app_key' => " . var_export($config['app_key'], true) . ",\n";
    $content .= "    'allow_registration' => " . var_export((bool) $config['allow_registration'], true) . ",\n";
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

function install_json_storage(string $dataDir, array $data, bool $resetStorage): void
{
    $jsonFile = $dataDir . '/lists.json';

    if ($resetStorage && file_exists($jsonFile) && !unlink($jsonFile)) {
        throw new RuntimeException('Vorhandene JSON-Datendatei konnte nicht gelöscht werden.');
    }

    $storage = new JsonStorage($jsonFile);
    $storage->save($data);
}

function install_sqlite_storage(string $dataDir, array $data, bool $resetStorage): void
{
    $sqliteFile = $dataDir . '/app.sqlite';

    if ($resetStorage && file_exists($sqliteFile) && !unlink($sqliteFile)) {
        throw new RuntimeException('Vorhandene SQLite-Datenbank konnte nicht gelöscht werden.');
    }

    $pdo = new PDO('sqlite:' . $sqliteFile);
    StorageFactory::configurePdo($pdo);
    install_storage_schema($pdo, 'sqlite');

    $storage = new PdoStorage($pdo, 'sqlite');
    $storage->save($data);
}

function install_mysql_storage(array $mysql, array $data, bool $resetStorage): void
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

    if ($resetStorage) {
        $serverPdo->exec('DROP DATABASE IF EXISTS ' . $quotedDatabase);
    }

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
    $storage->save($data);
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

        $adminUsername = clean_username(post_value('admin_username', 'admin'));
        $adminDisplayName = clean_text(post_value('admin_display_name', 'Admin'), MAX_DISPLAY_NAME_LENGTH);
        $adminPassword = clean_password(post_value('admin_password', ''));
        $allowRegistration = post_value('allow_registration', 'yes') === 'yes';
        $resetStorage = post_value('reset_storage', 'yes') === 'yes';

        ensure_data_directory($dataDir);

        $mysql = [
            'host' => post_value('mysql_host', '127.0.0.1'),
            'port' => (int) post_value('mysql_port', '3306'),
            'database' => post_value('mysql_database', 'haushaltspilot'),
            'username' => post_value('mysql_username', 'root'),
            'password' => post_value('mysql_password', ''),
            'charset' => 'utf8mb4',
        ];

        $installData = create_install_data($adminUsername, $adminDisplayName, $adminPassword);

        if ($storage === 'json') {
            install_json_storage($dataDir, $installData, $resetStorage);
        }

        if ($storage === 'sqlite') {
            install_sqlite_storage($dataDir, $installData, $resetStorage);
        }

        if ($storage === 'mysql') {
            install_mysql_storage($mysql, $installData, $resetStorage);
        }

        $config = [
            'storage' => $storage,
            'app_key' => bin2hex(random_bytes(32)),
            'allow_registration' => $allowRegistration,
            'mysql' => $mysql,
        ];

        write_config_file($configFile, $config);

        $message = 'Installation abgeschlossen. Du kannst dich jetzt mit dem Admin-Konto anmelden.';
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
    <title><?= e('HaushaltsPilot Teil 11 Installer') ?></title>
    <style>
        :root { --card:#fff; --text:#1f2937; --muted:#6b7280; --primary:#2563eb; --primary-dark:#1d4ed8; --danger:#dc2626; --success:#16a34a; --border:#d1d5db; --soft:#f9fafb; --shadow:0 18px 40px rgba(15,23,42,.12); --radius:18px; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; font-family:Arial,Helvetica,sans-serif; background:linear-gradient(135deg,#e0ecff,#f8fafc); color:var(--text); padding:20px; }
        .installer { width:100%; max-width:820px; margin:0 auto; background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow); padding:24px; }
        h1 { margin:0 0 8px; } p { color:var(--muted); line-height:1.5; }
        .notice,.success,.error { border-radius:14px; padding:12px 14px; margin:14px 0; line-height:1.45; }
        .notice { background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; }
        .success { background:#ecfdf5; border:1px solid #bbf7d0; color:#166534; }
        .error { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; }
        fieldset { border:1px solid var(--border); border-radius:16px; padding:16px; margin:18px 0; background:var(--soft); }
        legend,label { font-weight:bold; } .field { margin-bottom:14px; }
        input,select { width:100%; border:1px solid var(--border); border-radius:12px; padding:12px 14px; font-size:1rem; background:white; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .radio-grid { display:grid; gap:10px; }
        .radio-card { display:flex; gap:10px; align-items:flex-start; background:white; border:1px solid var(--border); border-radius:14px; padding:12px; }
        .radio-card input { width:auto; margin-top:3px; }
        button,.button-link { display:inline-block; border:0; border-radius:12px; padding:12px 14px; background:var(--primary); color:white; font-weight:bold; cursor:pointer; text-decoration:none; }
        button:hover,.button-link:hover { background:var(--primary-dark); }
        small { color:var(--muted); display:block; margin-top:4px; line-height:1.4; }
        @media(max-width:700px){ .grid{grid-template-columns:1fr;} .installer{padding:18px;} }
    </style>
</head>
<body>
    <main class="installer">
        <h1>HaushaltsPilot Teil 11 Installer</h1>
        <p>Teil 11 installiert Speicher, Datenmodell, Admin-Konto, Benutzerrollen, Haushalte/Familien, Listen, persönliche Todos und gemeinsame Familienaufgaben.</p>

        <?php if ($isInstalled): ?>
            <div class="notice">Es existiert bereits eine <code>config.php</code>. Eine Neuinstallation ist nur möglich, wenn du das Überschreiben ausdrücklich aktivierst. Der Speicher-Reset ist standardmäßig aktiv.</div>
        <?php endif; ?>

        <?php if ($message !== ''): ?>
            <div class="success"><?= e($message) ?></div>
            <p><a class="button-link" href="login.php">Zur Anmeldung</a></p>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="installer.php">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <fieldset>
                <legend>Speicher auswählen</legend>
                <div class="radio-grid">
                    <label class="radio-card"><input type="radio" name="storage" value="json" checked><span>JSON-Datei<small>Einfachster Modus für Demo und Lernen. Datei liegt in <code>data/lists.json</code>.</small></span></label>
                    <label class="radio-card"><input type="radio" name="storage" value="sqlite"><span>SQLite über PDO<small>Guter lokaler Datenbankmodus. Datei liegt in <code>data/app.sqlite</code>.</small></span></label>
                    <label class="radio-card"><input type="radio" name="storage" value="mysql"><span>MySQL/MariaDB über PDO<small>Für WAMP/XAMPP/Webhosting mit Datenbankserver.</small></span></label>
                </div>
            </fieldset>

            <fieldset>
                <legend>Admin-Konto</legend>
                <div class="grid">
                    <div class="field"><label for="admin_username">Admin-Benutzername</label><input id="admin_username" name="admin_username" value="admin" maxlength="30" required></div>
                    <div class="field"><label for="admin_display_name">Anzeigename</label><input id="admin_display_name" name="admin_display_name" value="Administrator" maxlength="60" required></div>
                </div>
                <div class="field"><label for="admin_password">Admin-Passwort</label><input id="admin_password" name="admin_password" type="password" minlength="8" required><small>Mindestens 8 Zeichen. Es wird mit <code>password_hash()</code> gespeichert.</small></div>
                <label class="radio-card"><input type="checkbox" name="allow_registration" value="yes" checked><span>Öffentliche Registrierung erlauben<small>Neue Konten erhalten automatisch die Rolle <code>user</code> und starten ohne Haushalt. Der Admin ordnet sie später einem Haushalt zu.</small></span></label>
            </fieldset>

            <fieldset>
                <legend>MySQL/MariaDB</legend>
                <div class="grid">
                    <div class="field"><label for="mysql_host">Host</label><input id="mysql_host" name="mysql_host" value="127.0.0.1"></div>
                    <div class="field"><label for="mysql_port">Port</label><input id="mysql_port" name="mysql_port" value="3306"></div>
                    <div class="field"><label for="mysql_database">Datenbank</label><input id="mysql_database" name="mysql_database" value="haushaltspilot"></div>
                    <div class="field"><label for="mysql_username">Benutzer</label><input id="mysql_username" name="mysql_username" value="root"></div>
                    <div class="field"><label for="mysql_password">Passwort</label><input id="mysql_password" name="mysql_password" type="password"></div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Speicher neu erstellen</legend>
                <label class="radio-card"><input type="checkbox" name="reset_storage" value="yes" checked><span>Vorhandenen Speicher löschen und neu erstellen<small>Standardmethode für diese Tutorial-Reihe. JSON-Datei wird gelöscht, SQLite-Datei wird gelöscht, MySQL/MariaDB-Datenbank wird per <code>DROP DATABASE IF EXISTS</code> entfernt und danach frisch erstellt.</small></span></label>
            </fieldset>


            <?php if ($isInstalled): ?>
                <fieldset>
                    <legend>Neuinstallation bestätigen</legend>
                    <label class="radio-card"><input type="checkbox" name="allow_overwrite" value="yes"><span>Bestehende Installation überschreiben<small>Erforderlich, wenn bereits eine config.php existiert. Zusammen mit dem aktivierten Speicher-Reset wird die gewählte Datenhaltung frisch aufgebaut.</small></span></label>
                </fieldset>
            <?php endif; ?>

            <button type="submit">Installieren</button>
        </form>
    </main>
</body>
</html>
