<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

if (current_user_id() !== null) {
    redirect('index.php');
}

$csrfToken = get_csrf_token();
$error = $_SESSION['flash_error'] ?? '';
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
$allowRegistration = ($config['allow_registration'] ?? true) === true;
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e('HaushaltsPilot – Anmeldung') ?></title>
    <style>
        :root { --card:#fff; --text:#1f2937; --muted:#6b7280; --primary:#2563eb; --primary-dark:#1d4ed8; --border:#d1d5db; --soft:#f9fafb; --danger:#dc2626; --success:#16a34a; --shadow:0 18px 40px rgba(15,23,42,.12); --radius:18px; }
        *{box-sizing:border-box;} body{margin:0;min-height:100vh;font-family:Arial,Helvetica,sans-serif;background:linear-gradient(135deg,#e0ecff,#f8fafc);color:var(--text);padding:20px;display:flex;align-items:center;justify-content:center;}
        .box{width:100%;max-width:920px;background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:24px;} h1{margin:0 0 8px;text-align:center;} p{color:var(--muted);line-height:1.5;text-align:center;}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:20px;} .panel{background:var(--soft);border:1px solid var(--border);border-radius:16px;padding:18px;} .panel h2{margin:0 0 14px;}
        label{display:block;font-weight:bold;margin-bottom:6px;} .field{margin-bottom:14px;} input{width:100%;border:1px solid var(--border);border-radius:12px;padding:12px 14px;font-size:1rem;background:white;}
        button{width:100%;border:0;border-radius:12px;padding:12px 14px;background:var(--primary);color:white;font-weight:bold;cursor:pointer;} button:hover{background:var(--primary-dark);} small{display:block;color:var(--muted);margin-top:4px;line-height:1.4;}
        .error,.success{border-radius:14px;padding:12px 14px;margin:14px 0;line-height:1.45;} .error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;} .success{background:#ecfdf5;border:1px solid #bbf7d0;color:#166534;}
        @media(max-width:760px){body{align-items:flex-start}.grid{grid-template-columns:1fr}.box{padding:18px;}}
    </style>
</head>
<body>
    <main class="box">
        <h1>HaushaltsPilot</h1>
        <p>Teil 07: Anmeldung, Registrierung, Sessions und Rollenbasis.</p>

        <?php if (is_string($error) && $error !== ''): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
        <?php if (is_string($success) && $success !== ''): ?><div class="success"><?= e($success) ?></div><?php endif; ?>

        <section class="grid">
            <form class="panel" method="post" action="auth.php">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="action" value="login">
                <h2>Anmelden</h2>
                <div class="field"><label for="login_username">Benutzername</label><input id="login_username" name="username" autocomplete="username" required></div>
                <div class="field"><label for="login_password">Passwort</label><input id="login_password" name="password" type="password" autocomplete="current-password" required></div>
                <button type="submit">Einloggen</button>
            </form>

            <form class="panel" method="post" action="auth.php">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="action" value="register">
                <h2>Registrieren</h2>
                <?php if ($allowRegistration): ?>
                    <div class="field"><label for="reg_username">Benutzername</label><input id="reg_username" name="username" maxlength="30" autocomplete="username" required><small>3–30 Zeichen: Buchstaben, Zahlen, Punkt, Unterstrich, Bindestrich.</small></div>
                    <div class="field"><label for="reg_display_name">Anzeigename</label><input id="reg_display_name" name="display_name" maxlength="60"></div>
                    <div class="field"><label for="reg_password">Passwort</label><input id="reg_password" name="password" type="password" minlength="8" autocomplete="new-password" required></div>
                    <div class="field"><label for="reg_password_repeat">Passwort wiederholen</label><input id="reg_password_repeat" name="password_repeat" type="password" minlength="8" autocomplete="new-password" required></div>
                    <button type="submit">Konto erstellen</button>
                <?php else: ?>
                    <p>Die öffentliche Registrierung ist deaktiviert.</p>
                <?php endif; ?>
            </form>
        </section>
    </main>
</body>
</html>
