<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_logged_in_for_page();

$csrfToken = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e('HaushaltsPilot – Teil 11 Todos') ?></title>
    <meta name="csrf-token" content="<?= e($csrfToken) ?>">
    <style>
        :root { --card:#fff; --text:#1f2937; --muted:#6b7280; --primary:#2563eb; --primary-dark:#1d4ed8; --success:#16a34a; --danger:#dc2626; --danger-dark:#b91c1c; --warning:#f59e0b; --border:#d1d5db; --soft:#f9fafb; --done:#e5e7eb; --shadow:0 18px 40px rgba(15,23,42,.12); --radius:18px; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; font-family:Arial,Helvetica,sans-serif; background:linear-gradient(135deg,#e0ecff,#f8fafc); color:var(--text); padding:20px; }
        .app { width:100%; max-width:1260px; margin:0 auto; background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow); padding:24px; }
        .topbar { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:20px; }
        h1 { margin:0 0 8px; font-size:1.9rem; }
        p { color:var(--muted); line-height:1.5; margin:0; }
        .userbox { text-align:right; color:var(--muted); font-size:.9rem; white-space:nowrap; }
        .userbox strong { display:block; color:var(--text); margin-bottom:4px; }
        .logout { border:0; border-radius:10px; background:var(--danger); color:white; padding:8px 10px; font-weight:bold; cursor:pointer; }
        .logout:hover { background:var(--danger-dark); }
        .note { margin:0 0 20px; background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; border-radius:14px; padding:12px 14px; font-size:.88rem; line-height:1.45; }
        .layout { display:grid; grid-template-columns:330px 1fr; gap:22px; align-items:start; }
        .panel { background:var(--soft); border:1px solid var(--border); border-radius:16px; padding:16px; }
        .panel h2 { margin:0 0 14px; font-size:1.1rem; }
        .form { display:grid; gap:10px; margin-bottom:14px; }
        .item-form { display:grid; grid-template-columns:1.5fr 1fr 1fr auto; gap:10px; margin-bottom:12px; }
        input,select { width:100%; border:1px solid var(--border); border-radius:12px; padding:12px 14px; font-size:1rem; outline:none; background:white; }
        input:focus,select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.15); }
        button { border:0; border-radius:12px; padding:12px 14px; font-size:.95rem; cursor:pointer; transition:.2s ease; }
        button:disabled { background:#d1d5db !important; color:#6b7280 !important; cursor:not-allowed; }
        .add-btn,.small-btn { background:var(--primary); color:white; font-weight:bold; }
        .add-btn:hover,.small-btn:hover { background:var(--primary-dark); }
        .delete-list-btn,.danger { background:var(--danger); color:white; font-weight:bold; }
        .delete-list-btn:hover,.danger:hover { background:var(--danger-dark); }
        .small-btn,.danger,.warning { padding:7px 9px; border-radius:9px; font-size:.82rem; }
        .warning { background:var(--warning); color:white; font-weight:bold; }
        .scope-row { display:flex; gap:8px; align-items:center; color:var(--muted); font-size:.88rem; }
        .scope-row input { width:auto; }
        .admin-only { display:none; }
        .admin-only.visible { display:block; }
        .list-group { margin-top:14px; }
        .list-group-title { display:flex; justify-content:space-between; align-items:center; gap:8px; color:var(--muted); font-size:.78rem; font-weight:bold; text-transform:uppercase; letter-spacing:.06em; margin:12px 0 8px; }
        .list-buttons { display:grid; gap:8px; }
        .list-button { display:grid; grid-template-columns:1fr auto; gap:8px; width:100%; background:white; border:1px solid var(--border); color:var(--text); text-align:left; }
        .list-button:hover { border-color:var(--primary); }
        .list-button.active { background:var(--primary); border-color:var(--primary); color:white; }
        .list-button span { min-width:0; overflow:hidden; text-overflow:ellipsis; }
        .list-button small { opacity:.85; white-space:nowrap; }
        .current-title { display:flex; justify-content:space-between; align-items:baseline; gap:12px; margin-bottom:14px; }
        .current-title h2 { margin:0; font-size:1.25rem; }
        .current-title span { color:var(--muted); font-size:.9rem; }
        .status-message { min-height:22px; margin-bottom:12px; font-size:.88rem; color:var(--muted); text-align:center; }
        .status-message.error { color:var(--danger); font-weight:bold; }
        .stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
        .stat-card { background:white; border:1px solid var(--border); border-radius:14px; padding:12px; text-align:center; }
        .stat-card span { display:block; color:var(--muted); font-size:.82rem; margin-bottom:4px; }
        .stat-card strong { font-size:1.3rem; }
        .list-settings { display:none; background:white; border:1px solid var(--border); border-radius:14px; padding:12px; margin-bottom:14px; }
        .list-settings.visible { display:block; }
        .settings-header { display:flex; justify-content:space-between; gap:10px; align-items:flex-start; margin-bottom:10px; }
        .settings-header strong { display:block; margin-bottom:4px; }
        .settings-header span { color:var(--muted); font-size:.84rem; }
        .settings-actions { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
        .owner-select-row { display:none; grid-template-columns:1fr auto; gap:8px; margin-top:10px; }
        .owner-select-row.visible { display:grid; }
        .shopping-list { list-style:none; padding:0; margin:0; }
        .shopping-item { display:grid; grid-template-columns:1fr auto; gap:12px; align-items:center; padding:14px; border:1px solid var(--border); border-radius:14px; margin-bottom:10px; background:white; }
        .shopping-item.done { background:var(--done); opacity:.75; }
        .shopping-item.done .item-name { text-decoration:line-through; color:var(--muted); }
        .item-name { display:block; font-weight:bold; word-break:break-word; margin-bottom:5px; }
        .item-meta { display:flex; flex-wrap:wrap; gap:6px; }
        .badge { display:inline-block; border-radius:999px; padding:4px 8px; font-size:.78rem; background:var(--soft); border:1px solid var(--border); color:var(--muted); }
        .item-actions { display:flex; gap:8px; align-items:center; }
        .toggle-btn { background:var(--success); color:white; padding:8px 10px; font-size:.85rem; white-space:nowrap; }
        .delete-btn { background:var(--danger); color:white; padding:8px 10px; font-size:.85rem; white-space:nowrap; }
        .empty-message { text-align:center; color:var(--muted); background:white; border:1px dashed var(--border); border-radius:12px; padding:18px; margin-top:12px; }
        .admin-panel { margin-top:22px; display:none; }
        .admin-panel.visible { display:block; }
        .admin-grid { display:grid; grid-template-columns:1fr; gap:16px; }
        .admin-tabs { display:flex; flex-wrap:wrap; gap:8px; margin:14px 0; }
        .tab-btn { background:white; border:1px solid var(--border); color:var(--text); font-weight:bold; }
        .tab-btn.active { background:var(--primary); border-color:var(--primary); color:white; }
        .tab-panel { display:none; }
        .tab-panel.active { display:block; }
        .inline-field { display:grid; grid-template-columns:1fr auto; gap:8px; align-items:center; margin-top:10px; }

        .table-wrap { overflow-x:auto; background:white; border:1px solid var(--border); border-radius:14px; }
        table { width:100%; border-collapse:collapse; font-size:.88rem; }
        th,td { padding:10px; border-bottom:1px solid var(--border); text-align:left; vertical-align:top; }
        th { background:var(--soft); color:var(--muted); font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; }
        tr:last-child td { border-bottom:0; }
        .inline-actions { display:flex; flex-wrap:wrap; gap:6px; }
        .footer-note { margin-top:22px; font-size:.82rem; color:var(--muted); text-align:center; }
        .todo-panel { margin-top:22px; }
        .todo-form { display:grid; grid-template-columns:2fr 140px 140px 180px; gap:10px; align-items:center; margin-bottom:14px; }
        .todo-advanced { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin:-6px 0 14px; }
        .todo-dashboard { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin:0 0 16px; }
        .dashboard-card { background:white; border:1px solid var(--border); border-radius:14px; padding:12px; }
        .dashboard-card strong { display:block; margin-bottom:4px; }
        .dashboard-card span { color:var(--muted); font-size:.84rem; }
        .todo-board { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .todo-column h3 { margin:0 0 10px; font-size:1rem; }
        .todo-list { list-style:none; padding:0; margin:0; display:grid; gap:12px; }
        .todo-item { display:grid; grid-template-columns:1fr auto; gap:10px; align-items:start; background:white; border:1px solid var(--border); border-radius:14px; padding:12px; }
        .todo-item.done { background:var(--done); opacity:.78; }
        .todo-item.done .todo-title { text-decoration:line-through; color:var(--muted); }
        .todo-title { display:block; font-weight:bold; margin-bottom:6px; word-break:break-word; }
        .todo-actions { display:flex; flex-wrap:wrap; gap:8px; justify-content:flex-end; }
        .todo-edit-form { grid-column:1 / -1; display:grid; grid-template-columns:2fr repeat(3,1fr); gap:8px; border-top:1px solid var(--border); padding-top:10px; }
        .todo-edit-form .wide { grid-column:1 / -1; }
        .comment-box { grid-column:1 / -1; border-top:1px solid var(--border); padding-top:10px; }
        .comment-list { display:grid; gap:6px; margin:0 0 8px; }
        .comment-item { background:var(--soft); border:1px solid var(--border); border-radius:10px; padding:8px; font-size:.86rem; }
        .comment-item small { display:block; color:var(--muted); margin-top:4px; }
        .comment-form { display:grid; grid-template-columns:1fr auto; gap:8px; }
        @media(max-width:900px){ .todo-form,.todo-advanced,.todo-dashboard,.todo-board,.todo-edit-form{grid-template-columns:1fr;} }
        @media(max-width:900px){ .layout{grid-template-columns:1fr;} .item-form{grid-template-columns:1fr;} .topbar{display:block;} .userbox{text-align:left;margin-top:12px;} }
        @media(max-width:560px){ body{padding:12px;} .app{padding:18px;} .stats{grid-template-columns:1fr;} .shopping-item{grid-template-columns:1fr;} .item-actions{width:100%;} .toggle-btn,.delete-btn{flex:1;} .current-title{display:block;} .settings-header{display:block;} }
    </style>
</head>
<body>
    <main class="app">
        <section class="topbar">
            <div>
                <h1>HaushaltsPilot</h1>
                <p>Teil 11: Persönliche Todos und gemeinsame Familienaufgaben ergänzen die bisherigen Einkaufs- und Haushaltslisten.</p>
            </div>
            <div class="userbox">
                <strong id="currentUserLabel">Lade Benutzer...</strong>
                <span id="currentFamilyLabel">Haushalt: wird geladen</span>
                <form method="post" action="auth.php" style="margin-top:8px;">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="action" value="logout">
                    <button class="logout" type="submit">Logout</button>
                </form>
            </div>
        </section>

        <div class="note">Sicherheitsbasis bleibt erhalten: Sessions, CSRF, Rollenprüfung, serverseitige Validierung, sichere Textausgabe und PDO Prepared Statements bei SQLite/MySQL. Neu: Aufgaben werden als eigenes Modul geführt – privat für einzelne Nutzer oder gemeinsam für den Haushalt.</div>

        <section class="layout">
            <aside class="panel">
                <h2>Listen</h2>
                <form class="form" id="listForm">
                    <input type="text" id="listNameInput" placeholder="Neue Liste, z. B. Einkauf" autocomplete="off" maxlength="40">
                    <select id="listTypeInput" aria-label="Listentyp wählen">
                        <option value="shopping">Einkaufsliste</option>
                        <option value="household">Haushaltsliste</option>
                        <option value="other">Sonstige Liste</option>
                    </select>
                    <label class="scope-row"><input type="checkbox" id="listSharedInput"> als Haushaltsliste freigeben</label>
                    <div class="admin-only" id="adminCreateOwnerBox">
                        <select id="listOwnerInput" aria-label="Besitzer wählen"></select>
                    </div>
                    <button class="add-btn" type="submit">Liste erstellen</button>
                </form>

                <div class="list-group"><div class="list-group-title"><span>Meine Listen</span><small id="myListCount">0</small></div><div class="list-buttons" id="myListButtons"></div></div>
                <div class="list-group"><div class="list-group-title"><span>Gemeinsame Einkaufslisten</span><small id="sharedShoppingListCount">0</small></div><div class="list-buttons" id="sharedShoppingListButtons"></div></div>
                <div class="list-group"><div class="list-group-title"><span>Gemeinsame Haushaltslisten</span><small id="sharedHouseholdListCount">0</small></div><div class="list-buttons" id="sharedHouseholdListButtons"></div></div>
                <div class="list-group"><div class="list-group-title"><span>Sonstige Gemeinschaftslisten</span><small id="sharedOtherListCount">0</small></div><div class="list-buttons" id="sharedOtherListButtons"></div></div>
                <div class="list-group admin-only" id="adminOtherListsGroup"><div class="list-group-title"><span>Andere Nutzerlisten</span><small id="otherListCount">0</small></div><div class="list-buttons" id="otherListButtons"></div></div>
            </aside>

            <section class="panel">
                <div class="current-title"><h2 id="currentListTitle">Aktive Liste</h2><span id="currentListInfo">0 Artikel</span></div>

                <div class="list-settings" id="listSettings">
                    <div class="settings-header"><div><strong id="listSettingsTitle">Listeneinstellungen</strong><span id="listSettingsInfo">Besitzer / Haushalt / Sichtbarkeit</span></div></div>
                    <div class="settings-actions">
                        <button class="small-btn" id="toggleVisibilityButton" type="button">Sichtbarkeit ändern</button>
                        <button class="danger" id="deleteListButton" type="button">Liste löschen</button>
                    </div>
                    <div class="inline-field" id="listTypeRow">
                        <select id="activeListTypeSelect" aria-label="Listentyp ändern">
                            <option value="shopping">Einkaufsliste</option>
                            <option value="household">Haushaltsliste</option>
                            <option value="other">Sonstige Liste</option>
                        </select>
                        <button class="small-btn" id="updateListTypeButton" type="button">Typ speichern</button>
                    </div>
                    <div class="owner-select-row" id="ownerSelectRow"><select id="activeListOwnerSelect"></select><button class="small-btn" id="updateOwnerButton" type="button">Besitzer ändern</button></div>
                </div>

                <form class="item-form" id="itemForm">
                    <input type="text" id="itemNameInput" placeholder="Artikel, z. B. Milch" autocomplete="off" maxlength="80">
                    <input type="text" id="itemAmountInput" placeholder="Menge, z. B. 2 Liter" autocomplete="off" maxlength="40">
                    <select id="itemCategoryInput"><option value="Lebensmittel">Lebensmittel</option><option value="Getränke">Getränke</option><option value="Haushalt">Haushalt</option><option value="Drogerie">Drogerie</option><option value="Schule">Schule</option><option value="Medikamente">Medikamente</option><option value="Sonstiges">Sonstiges</option></select>
                    <button class="add-btn" type="submit">Hinzufügen</button>
                </form>

                <div class="status-message" id="statusMessage"></div>
                <section class="stats"><div class="stat-card"><span>Gesamt</span><strong id="totalCount">0</strong></div><div class="stat-card"><span>Offen</span><strong id="openCount">0</strong></div><div class="stat-card"><span>Erledigt</span><strong id="doneCount">0</strong></div></section>
                <ul class="shopping-list" id="shoppingList"></ul>
                <div class="empty-message" id="emptyMessage">Diese Liste ist leer.</div>
            </section>
        </section>

        <section class="panel todo-panel" id="todoPanel">
            <h2>Todos</h2>
            <p>Aufgaben sind ab Teil 11 ein vertieftes Modul: privat oder gemeinsam, mit Priorität, Zuständigkeit, Kommentaren, Fälligkeit, Erinnerung und Kalenderbezug.</p>
            <form class="todo-form" id="todoForm">
                <input type="text" id="todoTitleInput" placeholder="Neue Aufgabe, z. B. Müll rausbringen" autocomplete="off" maxlength="120">
                <input type="date" id="todoDueInput" aria-label="Fälligkeitsdatum">
                <select id="todoPriorityInput" aria-label="Priorität wählen">
                    <option value="low">Niedrig</option>
                    <option value="normal" selected>Normal</option>
                    <option value="high">Hoch</option>
                    <option value="urgent">Dringend</option>
                </select>
                <select id="todoAssignedInput" aria-label="Zuständige Person"></select>
                <label class="scope-row"><input type="checkbox" id="todoFamilyInput"> Familienaufgabe</label>
                <input type="date" id="todoReminderInput" aria-label="Erinnerungsdatum">
                <input type="date" id="todoCalendarInput" aria-label="Kalenderdatum">
                <button class="add-btn" type="submit">Aufgabe erstellen</button>
            </form>
            <section class="stats">
                <div class="stat-card"><span>Aufgaben</span><strong id="todoTotalCount">0</strong></div>
                <div class="stat-card"><span>Offen</span><strong id="todoOpenCount">0</strong></div>
                <div class="stat-card"><span>Erledigt</span><strong id="todoDoneCount">0</strong></div>
            </section>
            <section class="todo-dashboard" id="todoDashboard"></section>
            <div class="todo-board">
                <div class="todo-column"><h3>Meine Aufgaben</h3><ul class="todo-list" id="privateTodoList"></ul><div class="empty-message" id="privateTodoEmpty">Keine privaten Aufgaben vorhanden.</div></div>
                <div class="todo-column"><h3>Familienaufgaben</h3><ul class="todo-list" id="familyTodoList"></ul><div class="empty-message" id="familyTodoEmpty">Keine Familienaufgaben vorhanden.</div></div>
            </div>
        </section>

        <section class="panel admin-panel" id="adminPanel">
            <h2>Administration</h2>
            <p>Die Verwaltung bleibt in Tabs getrennt. Ab Teil 11 kommt eine Todo-Übersicht hinzu, damit Admins Aufgaben modulbezogen prüfen können.</p>

            <div class="admin-tabs" role="tablist" aria-label="Adminbereiche">
                <button class="tab-btn active" type="button" data-admin-tab="families">Haushalte</button>
                <button class="tab-btn" type="button" data-admin-tab="users">Benutzer</button>
                <button class="tab-btn" type="button" data-admin-tab="lists">Listen</button>
                <button class="tab-btn" type="button" data-admin-tab="todos">Todos</button>
            </div>

            <div class="admin-grid">
                <div class="tab-panel active" id="adminTabFamilies">
                    <h3>Haushalte</h3>
                    <form class="form" id="familyForm"><input id="familyNameInput" maxlength="60" placeholder="Neuer Haushalt, z. B. Familie Müller"><button class="add-btn" type="submit">Haushalt erstellen</button></form>
                    <div class="table-wrap"><table><thead><tr><th>Haushalt</th><th>Mitglieder</th><th>Listen</th><th>Aktionen</th></tr></thead><tbody id="familyTableBody"></tbody></table></div>
                </div>
                <div class="tab-panel" id="adminTabUsers">
                    <h3>Benutzer</h3>
                    <div class="table-wrap"><table><thead><tr><th>Benutzer</th><th>Rolle</th><th>Status</th><th>Haushalt</th><th>Aktionen</th></tr></thead><tbody id="userTableBody"></tbody></table></div>
                </div>
                <div class="tab-panel" id="adminTabLists">
                    <h3>Listenübersicht</h3>
                    <select id="adminListFilter"><option value="all">Alle Listen</option><option value="own">Meine Listen</option><option value="shared">Gemeinschaftslisten</option><option value="shopping">Einkaufslisten</option><option value="household">Haushaltslisten</option><option value="private">Private Listen</option></select>
                    <div class="table-wrap" style="margin-top:10px;"><table><thead><tr><th>Liste</th><th>Typ</th><th>Besitzer</th><th>Haushalt</th><th>Status</th><th>Aktionen</th></tr></thead><tbody id="adminListTableBody"></tbody></table></div>
                </div>
                <div class="tab-panel" id="adminTabTodos">
                    <h3>Todo-Übersicht</h3>
                    <select id="adminTodoFilter"><option value="all">Alle Aufgaben</option><option value="private">Private Aufgaben</option><option value="family">Familienaufgaben</option><option value="open">Offen</option><option value="done">Erledigt</option></select>
                    <div class="table-wrap" style="margin-top:10px;"><table><thead><tr><th>Aufgabe</th><th>Bereich</th><th>Priorität</th><th>Zuweisung</th><th>Fällig</th><th>Erinnerung</th><th>Status</th><th>Kommentare</th><th>Aktionen</th></tr></thead><tbody id="adminTodoTableBody"></tbody></table></div>
                </div>
            </div>
        </section>

        <p class="footer-note">Teil 12 kann darauf mit privaten Nachrichten zwischen Nutzern aufbauen.</p>
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const byId = (id) => document.getElementById(id);

        const currentUserLabel = byId('currentUserLabel');
        const currentFamilyLabel = byId('currentFamilyLabel');
        const listForm = byId('listForm');
        const listNameInput = byId('listNameInput');
        const listTypeInput = byId('listTypeInput');
        const listSharedInput = byId('listSharedInput');
        const listOwnerInput = byId('listOwnerInput');
        const adminCreateOwnerBox = byId('adminCreateOwnerBox');
        const myListButtons = byId('myListButtons');
        const sharedShoppingListButtons = byId('sharedShoppingListButtons');
        const sharedHouseholdListButtons = byId('sharedHouseholdListButtons');
        const sharedOtherListButtons = byId('sharedOtherListButtons');
        const otherListButtons = byId('otherListButtons');
        const adminOtherListsGroup = byId('adminOtherListsGroup');
        const myListCount = byId('myListCount');
        const sharedShoppingListCount = byId('sharedShoppingListCount');
        const sharedHouseholdListCount = byId('sharedHouseholdListCount');
        const sharedOtherListCount = byId('sharedOtherListCount');
        const otherListCount = byId('otherListCount');
        const currentListTitle = byId('currentListTitle');
        const currentListInfo = byId('currentListInfo');
        const listSettings = byId('listSettings');
        const listSettingsTitle = byId('listSettingsTitle');
        const listSettingsInfo = byId('listSettingsInfo');
        const toggleVisibilityButton = byId('toggleVisibilityButton');
        const activeListTypeSelect = byId('activeListTypeSelect');
        const updateListTypeButton = byId('updateListTypeButton');
        const deleteListButton = byId('deleteListButton');
        const ownerSelectRow = byId('ownerSelectRow');
        const activeListOwnerSelect = byId('activeListOwnerSelect');
        const updateOwnerButton = byId('updateOwnerButton');
        const itemForm = byId('itemForm');
        const itemNameInput = byId('itemNameInput');
        const itemAmountInput = byId('itemAmountInput');
        const itemCategoryInput = byId('itemCategoryInput');
        const statusMessage = byId('statusMessage');
        const totalCount = byId('totalCount');
        const openCount = byId('openCount');
        const doneCount = byId('doneCount');
        const shoppingList = byId('shoppingList');
        const emptyMessage = byId('emptyMessage');
        const todoForm = byId('todoForm');
        const todoTitleInput = byId('todoTitleInput');
        const todoDueInput = byId('todoDueInput');
        const todoPriorityInput = byId('todoPriorityInput');
        const todoAssignedInput = byId('todoAssignedInput');
        const todoFamilyInput = byId('todoFamilyInput');
        const todoReminderInput = byId('todoReminderInput');
        const todoCalendarInput = byId('todoCalendarInput');
        const privateTodoList = byId('privateTodoList');
        const familyTodoList = byId('familyTodoList');
        const privateTodoEmpty = byId('privateTodoEmpty');
        const familyTodoEmpty = byId('familyTodoEmpty');
        const todoTotalCount = byId('todoTotalCount');
        const todoOpenCount = byId('todoOpenCount');
        const todoDoneCount = byId('todoDoneCount');
        const todoDashboard = byId('todoDashboard');
        const adminPanel = byId('adminPanel');
        const familyForm = byId('familyForm');
        const familyNameInput = byId('familyNameInput');
        const familyTableBody = byId('familyTableBody');
        const userTableBody = byId('userTableBody');
        const adminListFilter = byId('adminListFilter');
        const adminListTableBody = byId('adminListTableBody');
        const adminTodoFilter = byId('adminTodoFilter');
        const adminTodoTableBody = byId('adminTodoTableBody');
        const adminTabButtons = document.querySelectorAll('[data-admin-tab]');
        const adminTabFamilies = byId('adminTabFamilies');
        const adminTabUsers = byId('adminTabUsers');
        const adminTabLists = byId('adminTabLists');
        const adminTabTodos = byId('adminTabTodos');

        let appData = { currentUser:null, currentFamily:null, lists:[], activeListId:null, todos:[], adminTodos:[], users:[], activeUsers:[], families:[], isAdmin:false };

        async function apiGet(action) {
            const response = await fetch('api.php?action=' + encodeURIComponent(action), { method:'GET', credentials:'same-origin', headers:{ Accept:'application/json' } });
            return handleApiResponse(response);
        }
        async function apiPost(action, payload) {
            const response = await fetch('api.php?action=' + encodeURIComponent(action), { method:'POST', credentials:'same-origin', headers:{ Accept:'application/json', 'Content-Type':'application/json', 'X-CSRF-Token':csrfToken }, body:JSON.stringify(payload || {}) });
            return handleApiResponse(response);
        }
        async function handleApiResponse(response) {
            let result;
            try { result = await response.json(); } catch (error) { throw new Error('Serverantwort war kein gültiges JSON.'); }
            if (!response.ok || result.success !== true) throw new Error(result.message || 'Unbekannter Serverfehler.');
            return result;
        }
        async function loadData() {
            try { const result = await apiGet('load'); appData = result.data; renderApp(); }
            catch (error) { showStatus(error.message, true); }
        }
        function getActiveList() { return appData.lists.find((list) => list.id === appData.activeListId); }
        function getUser(userId) { return appData.users.find((user) => user.id === userId) || null; }
        function getUserName(userId) { if (!userId) return 'nicht zugewiesen'; const user = getUser(userId); return user ? user.displayName : 'Unbekannt'; }
        function getFamily(familyId) { return appData.families.find((family) => family.id === familyId) || null; }
        function getFamilyName(familyId) { if (!familyId) return 'kein Haushalt'; const family = getFamily(familyId); return family ? family.name : 'unbekannter Haushalt'; }
        function listTypeLabel(type) { if (type === 'household') return 'Haushaltsliste'; if (type === 'other') return 'Sonstige Liste'; return 'Einkaufsliste'; }
        function todoScopeLabel(scope) { return scope === 'family' ? 'Familienaufgabe' : 'Private Aufgabe'; }
        function todoStatusLabel(status) { return status === 'done' ? 'erledigt' : 'offen'; }
        function priorityLabel(priority) { return { low:'niedrig', normal:'normal', high:'hoch', urgent:'dringend' }[priority] || 'normal'; }
        function formatDate(value) { return value ? value : 'kein Datum'; }
        function canManageListSettings(list) { return appData.isAdmin === true || (appData.currentUser && list.ownerId === appData.currentUser.id); }
        function todayString() { return new Date().toISOString().slice(0, 10); }
        function isOverdue(todo) { return todo.status !== 'done' && todo.dueAt && todo.dueAt < todayString(); }
        function isReminderDue(todo) { return todo.status !== 'done' && todo.reminderAt && todo.reminderAt <= todayString(); }

        function fillUserSelect(select, selectedValue, scope, includeEmpty) {
            select.textContent = '';
            if (includeEmpty) {
                const empty = document.createElement('option');
                empty.value = '';
                empty.textContent = 'nicht zugewiesen';
                select.appendChild(empty);
            }
            const currentFamilyId = appData.currentUser ? appData.currentUser.familyId : '';
            const users = (appData.activeUsers || []).filter((user) => scope === 'family' ? user.familyId === currentFamilyId && currentFamilyId !== '' : appData.currentUser && user.id === appData.currentUser.id);
            users.forEach((user) => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = user.displayName + ' (@' + user.username + ')';
                if (user.id === selectedValue) option.selected = true;
                select.appendChild(option);
            });
        }
        function refreshCreateTodoAssignees() { fillUserSelect(todoAssignedInput, appData.currentUser ? appData.currentUser.id : '', todoFamilyInput.checked ? 'family' : 'private', true); }

        async function createList() {
            const name = listNameInput.value.trim();
            if (name === '') { showStatus('Bitte gib einen Listennamen ein.', true); listNameInput.focus(); return; }
            const payload = { name:name, listType:listTypeInput.value, isShared:listSharedInput.checked };
            if (appData.isAdmin === true && listOwnerInput.value !== '') payload.ownerId = listOwnerInput.value;
            try { const result = await apiPost('create_list', payload); appData = result.data; listNameInput.value=''; listTypeInput.value='shopping'; listSharedInput.checked=false; renderApp(); showStatus(result.message); }
            catch (error) { showStatus(error.message, true); }
        }
        async function selectList(listId) { try { const result = await apiPost('set_active_list', { listId:listId }); appData = result.data; renderApp(); } catch(error){ showStatus(error.message, true); } }
        async function deleteActiveList() { const list = getActiveList(); if (!list) return; if (!confirm('Liste "' + list.name + '" wirklich löschen?')) return; try { const result = await apiPost('delete_list', { listId:list.id }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function updateActiveListVisibility() { const list = getActiveList(); if (!list) return; try { const result = await apiPost('update_list_visibility', { listId:list.id, isShared:!list.isShared }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function updateActiveListType() { const list = getActiveList(); if (!list) return; try { const result = await apiPost('update_list_type', { listId:list.id, listType:activeListTypeSelect.value }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function updateActiveListOwner() { const list = getActiveList(); if (!list || appData.isAdmin !== true) return; try { const result = await apiPost('admin_update_list_owner', { listId:list.id, ownerId:activeListOwnerSelect.value }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function addItem() { const list = getActiveList(); if (!list) return; const name = itemNameInput.value.trim(); if (name === '') { showStatus('Bitte gib einen Artikelnamen ein.', true); itemNameInput.focus(); return; } try { const result = await apiPost('add_item', { listId:list.id, name:name, amount:itemAmountInput.value.trim(), category:itemCategoryInput.value }); appData = result.data; itemNameInput.value=''; itemAmountInput.value=''; itemCategoryInput.value='Lebensmittel'; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function toggleItemDone(itemId) { const list = getActiveList(); if (!list) return; try { const result = await apiPost('toggle_item', { listId:list.id, itemId:itemId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function deleteItem(itemId) { const list = getActiveList(); if (!list) return; try { const result = await apiPost('delete_item', { listId:list.id, itemId:itemId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }

        async function createTodo() {
            const title = todoTitleInput.value.trim();
            if (title === '') { showStatus('Bitte gib eine Aufgabe ein.', true); todoTitleInput.focus(); return; }
            const scope = todoFamilyInput.checked ? 'family' : 'private';
            const payload = { title, scope, priority:todoPriorityInput.value, assignedTo:todoAssignedInput.value, dueAt:todoDueInput.value, reminderAt:todoReminderInput.value, calendarDate:todoCalendarInput.value };
            try {
                const result = await apiPost('create_todo', payload);
                appData = result.data;
                todoTitleInput.value = '';
                todoDueInput.value = '';
                todoReminderInput.value = '';
                todoCalendarInput.value = '';
                todoPriorityInput.value = 'normal';
                todoFamilyInput.checked = false;
                renderApp();
                showStatus(result.message);
            } catch(error) { showStatus(error.message, true); }
        }
        async function updateTodo(todoId, payload) { try { const result = await apiPost('update_todo', Object.assign({ todoId }, payload)); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function toggleTodoDone(todoId) { try { const result = await apiPost('toggle_todo', { todoId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function deleteTodo(todoId) { if (!confirm('Aufgabe wirklich löschen?')) return; try { const result = await apiPost('delete_todo', { todoId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function addTodoComment(todoId, input) { const body = input.value.trim(); if (body === '') { showStatus('Bitte gib einen Kommentar ein.', true); return; } try { const result = await apiPost('add_todo_comment', { todoId, body }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function deleteTodoComment(todoId, commentId) { try { const result = await apiPost('delete_todo_comment', { todoId, commentId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }

        async function adminCreateFamily() { const name = familyNameInput.value.trim(); if (name === '') { showStatus('Bitte gib einen Haushaltsnamen ein.', true); return; } try { const result = await apiPost('admin_create_family', { name:name }); appData = result.data; familyNameInput.value=''; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminRenameFamily(familyId) { const family = getFamily(familyId); if (!family) return; const name = prompt('Neuer Name für Haushalt:', family.name); if (name === null) return; try { const result = await apiPost('admin_update_family_name', { familyId:familyId, name:name.trim() }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminDeleteFamily(familyId) { const family = getFamily(familyId); if (!family) return; if (!confirm('Haushalt "' + family.name + '" wirklich löschen? Zugeordnete Nutzer werden ohne Haushalt gesetzt und Haushaltslisten werden privat.')) return; try { const result = await apiPost('admin_delete_family', { familyId:familyId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminUpdateUserFamily(userId, familyId) { try { const result = await apiPost('admin_update_user_family', { userId:userId, familyId:familyId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminUpdateRole(userId, role) { try { const result = await apiPost('admin_update_user_role', { userId:userId, role:role }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminToggleActive(userId) { try { const result = await apiPost('admin_toggle_user_active', { userId:userId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminDeleteUser(userId) { if (!confirm('Benutzer wirklich löschen? Eigene Listen dieses Benutzers werden entfernt.')) return; try { const result = await apiPost('admin_delete_user', { userId:userId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminUpdateListOwner(listId, ownerId) { try { const result = await apiPost('admin_update_list_owner', { listId:listId, ownerId:ownerId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminUpdateListType(listId, listType) { try { const result = await apiPost('update_list_type', { listId:listId, listType:listType }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminToggleListVisibility(list) { try { const result = await apiPost('update_list_visibility', { listId:list.id, isShared:!list.isShared }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }

        function renderApp() { renderCurrentUser(); renderUserSelects(); renderListGroups(); renderActiveList(); renderTodos(); renderAdminPanel(); }
        function renderCurrentUser() { if (!appData.currentUser) return; currentUserLabel.textContent = appData.currentUser.displayName + ' (' + appData.currentUser.role + ')'; currentFamilyLabel.textContent = 'Haushalt: ' + getFamilyName(appData.currentUser.familyId); }
        function renderUserSelects() {
            adminCreateOwnerBox.classList.toggle('visible', appData.isAdmin === true);
            listOwnerInput.textContent='';
            activeListOwnerSelect.textContent='';
            appData.activeUsers.forEach(function(user){
                const label = user.displayName + ' (@' + user.username + ') – ' + getFamilyName(user.familyId);
                const option = document.createElement('option'); option.value=user.id; option.textContent=label; if (appData.currentUser && user.id === appData.currentUser.id) option.selected=true; listOwnerInput.appendChild(option);
                const option2 = document.createElement('option'); option2.value=user.id; option2.textContent=label; activeListOwnerSelect.appendChild(option2);
            });
            refreshCreateTodoAssignees();
        }
        function renderListGroups() { myListButtons.textContent=''; sharedShoppingListButtons.textContent=''; sharedHouseholdListButtons.textContent=''; sharedOtherListButtons.textContent=''; otherListButtons.textContent=''; const mine=[], sharedShopping=[], sharedHousehold=[], sharedOther=[], other=[]; appData.lists.forEach(function(list){ if (appData.currentUser && list.ownerId === appData.currentUser.id) mine.push(list); else if (list.isShared === true && appData.currentUser && list.familyId === appData.currentUser.familyId && appData.currentUser.familyId !== '') { if (list.listType === 'household') sharedHousehold.push(list); else if (list.listType === 'other') sharedOther.push(list); else sharedShopping.push(list); } else other.push(list); }); renderListButtonGroup(myListButtons, mine); renderListButtonGroup(sharedShoppingListButtons, sharedShopping); renderListButtonGroup(sharedHouseholdListButtons, sharedHousehold); renderListButtonGroup(sharedOtherListButtons, sharedOther); renderListButtonGroup(otherListButtons, other); myListCount.textContent=mine.length; sharedShoppingListCount.textContent=sharedShopping.length; sharedHouseholdListCount.textContent=sharedHousehold.length; sharedOtherListCount.textContent=sharedOther.length; otherListCount.textContent=other.length; adminOtherListsGroup.classList.toggle('visible', appData.isAdmin === true); }
        function renderListButtonGroup(container, lists) { if (lists.length === 0) { const empty = document.createElement('div'); empty.className='empty-message'; empty.textContent='Keine Listen vorhanden.'; container.appendChild(empty); return; } lists.forEach(function(list){ const button = document.createElement('button'); button.className='list-button'; button.type='button'; if (list.id === appData.activeListId) button.classList.add('active'); const name = document.createElement('span'); name.textContent = list.name; const count = document.createElement('small'); count.textContent = listTypeLabel(list.listType) + ' · ' + list.items.length + ' Artikel'; button.appendChild(name); button.appendChild(count); button.addEventListener('click', function(){ selectList(list.id); }); container.appendChild(button); }); }
        function renderActiveList() { const list = getActiveList(); shoppingList.textContent=''; if (!list) { currentListTitle.textContent='Keine Liste'; currentListInfo.textContent='0 Artikel'; listSettings.classList.remove('visible'); updateStats([]); return; } currentListTitle.textContent=list.name; currentListInfo.textContent=list.items.length + ' Artikel'; renderListSettings(list); list.items.forEach(renderItem); updateStats(list.items); }
        function renderListSettings(list) { const canManage = canManageListSettings(list); listSettings.classList.toggle('visible', canManage); listSettingsTitle.textContent=list.name; listSettingsInfo.textContent='Typ: ' + listTypeLabel(list.listType) + ' | Besitzer: ' + getUserName(list.ownerId) + ' | Haushalt: ' + getFamilyName(list.familyId) + ' | ' + (list.isShared ? 'gemeinschaftlich' : 'privat'); toggleVisibilityButton.textContent = list.isShared ? 'Auf privat setzen' : 'Als Gemeinschaftsliste freigeben'; toggleVisibilityButton.disabled = list.familyId === '' && !list.isShared; activeListTypeSelect.value = list.listType || 'shopping'; updateListTypeButton.disabled = !canManage; deleteListButton.disabled = !canManage; ownerSelectRow.classList.toggle('visible', appData.isAdmin === true); activeListOwnerSelect.value = list.ownerId; }
        function renderItem(item) { const li=document.createElement('li'); li.className='shopping-item'; if (item.done) li.classList.add('done'); const main=document.createElement('div'); const name=document.createElement('span'); name.className='item-name'; name.textContent=item.name; const meta=document.createElement('div'); meta.className='item-meta'; const badges=['Menge: ' + (item.amount || 'keine Angabe'), 'Kategorie: ' + item.category, item.done ? 'Status: erledigt' : 'Status: offen', 'Erstellt von: ' + getUserName(item.createdBy)]; badges.forEach(function(text){ const b=document.createElement('span'); b.className='badge'; b.textContent=text; meta.appendChild(b); }); main.appendChild(name); main.appendChild(meta); const actions=document.createElement('div'); actions.className='item-actions'; const toggle=document.createElement('button'); toggle.className='toggle-btn'; toggle.type='button'; toggle.textContent=item.done?'Öffnen':'Erledigt'; toggle.addEventListener('click', function(){ toggleItemDone(item.id); }); const del=document.createElement('button'); del.className='delete-btn'; del.type='button'; del.textContent='Löschen'; del.addEventListener('click', function(){ deleteItem(item.id); }); actions.appendChild(toggle); actions.appendChild(del); li.appendChild(main); li.appendChild(actions); shoppingList.appendChild(li); }
        function updateStats(items) { const total=items.length; const done=items.filter(function(item){ return item.done === true; }).length; totalCount.textContent=total; doneCount.textContent=done; openCount.textContent=total-done; emptyMessage.style.display=total===0?'block':'none'; }

        function renderTodos() {
            privateTodoList.textContent=''; familyTodoList.textContent=''; todoDashboard.textContent='';
            const todos = appData.todos || [];
            const privateTodos = todos.filter((todo) => todo.scope === 'private');
            const familyTodos = todos.filter((todo) => todo.scope === 'family');
            privateTodos.forEach((todo) => renderTodoItem(privateTodoList, todo));
            familyTodos.forEach((todo) => renderTodoItem(familyTodoList, todo));
            privateTodoEmpty.style.display = privateTodos.length === 0 ? 'block' : 'none';
            familyTodoEmpty.style.display = familyTodos.length === 0 ? 'block' : 'none';
            const done = todos.filter((todo) => todo.status === 'done').length;
            const overdue = todos.filter(isOverdue).length;
            const reminders = todos.filter(isReminderDue).length;
            const assignedToMe = todos.filter((todo) => appData.currentUser && todo.assignedTo === appData.currentUser.id && todo.status !== 'done').length;
            todoTotalCount.textContent = todos.length;
            todoDoneCount.textContent = done;
            todoOpenCount.textContent = todos.length - done;
            renderDashboardCard('Überfällig', overdue + ' Aufgabe(n)', overdue > 0 ? 'Bitte prüfen' : 'Alles im Zeitplan');
            renderDashboardCard('Erinnerungen', reminders + ' aktiv', reminders > 0 ? 'Erinnerungsdatum erreicht' : 'Keine fällige Erinnerung');
            renderDashboardCard('Mir zugewiesen', assignedToMe + ' offen', 'Persönliche Arbeitslast');
        }
        function renderDashboardCard(title, value, detail) { const card=document.createElement('div'); card.className='dashboard-card'; const strong=document.createElement('strong'); strong.textContent=title + ': ' + value; const span=document.createElement('span'); span.textContent=detail; card.appendChild(strong); card.appendChild(span); todoDashboard.appendChild(card); }
        function renderTodoItem(container, todo) {
            const li=document.createElement('li'); li.className='todo-item'; if (todo.status === 'done') li.classList.add('done');
            const main=document.createElement('div'); const title=document.createElement('span'); title.className='todo-title'; title.textContent=todo.title;
            const meta=document.createElement('div'); meta.className='item-meta';
            const badges=[todoScopeLabel(todo.scope), 'Status: ' + todoStatusLabel(todo.status), 'Priorität: ' + priorityLabel(todo.priority), 'Zugewiesen: ' + getUserName(todo.assignedTo), 'Fällig: ' + formatDate(todo.dueAt), 'Erinnerung: ' + formatDate(todo.reminderAt), 'Kalender: ' + formatDate(todo.calendarDate), 'Besitzer: ' + getUserName(todo.ownerId), 'Kommentare: ' + (todo.comments || []).length];
            if (todo.scope === 'family') badges.push('Haushalt: ' + getFamilyName(todo.familyId));
            if (isOverdue(todo)) badges.push('überfällig');
            if (isReminderDue(todo)) badges.push('Erinnerung erreicht');
            badges.forEach(function(text){ const b=document.createElement('span'); b.className='badge'; b.textContent=text; meta.appendChild(b); });
            main.appendChild(title); main.appendChild(meta);
            const actions=document.createElement('div'); actions.className='todo-actions';
            const toggle=document.createElement('button'); toggle.className='toggle-btn'; toggle.type='button'; toggle.textContent=todo.status === 'done' ? 'Öffnen' : 'Erledigt'; toggle.addEventListener('click', function(){ toggleTodoDone(todo.id); });
            const edit=document.createElement('button'); edit.className='small-btn'; edit.type='button'; edit.textContent='Bearbeiten';
            const del=document.createElement('button'); del.className='delete-btn'; del.type='button'; del.textContent='Löschen'; del.addEventListener('click', function(){ deleteTodo(todo.id); });
            actions.appendChild(toggle); actions.appendChild(edit); actions.appendChild(del); li.appendChild(main); li.appendChild(actions);
            const editForm = buildTodoEditForm(todo); editForm.style.display='none'; edit.addEventListener('click', function(){ editForm.style.display = editForm.style.display === 'none' ? 'grid' : 'none'; }); li.appendChild(editForm);
            li.appendChild(buildCommentsBox(todo));
            container.appendChild(li);
        }
        function buildTodoEditForm(todo) {
            const form=document.createElement('form'); form.className='todo-edit-form';
            const title=document.createElement('input'); title.value=todo.title; title.maxLength=120;
            const scope=document.createElement('select'); [['private','Privat'],['family','Familie']].forEach(function(pair){ const opt=document.createElement('option'); opt.value=pair[0]; opt.textContent=pair[1]; if (todo.scope===pair[0]) opt.selected=true; scope.appendChild(opt); });
            const priority=document.createElement('select'); [['low','Niedrig'],['normal','Normal'],['high','Hoch'],['urgent','Dringend']].forEach(function(pair){ const opt=document.createElement('option'); opt.value=pair[0]; opt.textContent=pair[1]; if ((todo.priority || 'normal')===pair[0]) opt.selected=true; priority.appendChild(opt); });
            const assigned=document.createElement('select'); fillUserSelect(assigned, todo.assignedTo || '', todo.scope, true); scope.addEventListener('change', function(){ fillUserSelect(assigned, assigned.value, scope.value, true); });
            const due=document.createElement('input'); due.type='date'; due.value=todo.dueAt || '';
            const reminder=document.createElement('input'); reminder.type='date'; reminder.value=todo.reminderAt || '';
            const calendar=document.createElement('input'); calendar.type='date'; calendar.value=todo.calendarDate || '';
            const save=document.createElement('button'); save.className='small-btn'; save.type='submit'; save.textContent='Speichern';
            form.appendChild(title); form.appendChild(scope); form.appendChild(priority); form.appendChild(assigned); form.appendChild(due); form.appendChild(reminder); form.appendChild(calendar); form.appendChild(save);
            form.addEventListener('submit', function(event){ event.preventDefault(); updateTodo(todo.id, { title:title.value.trim(), scope:scope.value, priority:priority.value, assignedTo:assigned.value, dueAt:due.value, reminderAt:reminder.value, calendarDate:calendar.value }); });
            return form;
        }
        function buildCommentsBox(todo) {
            const box=document.createElement('div'); box.className='comment-box';
            const comments=document.createElement('div'); comments.className='comment-list';
            (todo.comments || []).forEach(function(comment){ const item=document.createElement('div'); item.className='comment-item'; const body=document.createElement('div'); body.textContent=comment.body; const meta=document.createElement('small'); meta.textContent=getUserName(comment.authorId) + ' · ' + comment.createdAt; const del=document.createElement('button'); del.className='danger'; del.type='button'; del.textContent='Kommentar löschen'; del.addEventListener('click', function(){ deleteTodoComment(todo.id, comment.id); }); item.appendChild(body); item.appendChild(meta); item.appendChild(del); comments.appendChild(item); });
            const form=document.createElement('form'); form.className='comment-form'; const input=document.createElement('input'); input.placeholder='Kommentar hinzufügen'; input.maxLength=600; const button=document.createElement('button'); button.className='small-btn'; button.type='submit'; button.textContent='Kommentieren'; form.appendChild(input); form.appendChild(button); form.addEventListener('submit', function(event){ event.preventDefault(); addTodoComment(todo.id, input); });
            box.appendChild(comments); box.appendChild(form); return box;
        }

        function renderAdminPanel() { adminPanel.classList.toggle('visible', appData.isAdmin === true); if (appData.isAdmin !== true) return; renderFamilyTable(); renderUserTable(); renderAdminListTable(); renderAdminTodoTable(); }
        function renderFamilyTable() { familyTableBody.textContent=''; appData.families.forEach(function(family){ const row=document.createElement('tr'); const name=document.createElement('td'); name.textContent=family.name; const members=document.createElement('td'); members.textContent=appData.users.filter(function(user){ return user.familyId === family.id; }).length; const lists=document.createElement('td'); lists.textContent=appData.lists.filter(function(list){ return list.familyId === family.id; }).length; const actions=document.createElement('td'); actions.className='inline-actions'; const rename=document.createElement('button'); rename.className='small-btn'; rename.textContent='Umbenennen'; rename.addEventListener('click', function(){ adminRenameFamily(family.id); }); const del=document.createElement('button'); del.className='danger'; del.textContent='Löschen'; del.addEventListener('click', function(){ adminDeleteFamily(family.id); }); actions.appendChild(rename); actions.appendChild(del); row.appendChild(name); row.appendChild(members); row.appendChild(lists); row.appendChild(actions); familyTableBody.appendChild(row); }); }
        function renderUserTable() { userTableBody.textContent=''; appData.users.forEach(function(user){ const row=document.createElement('tr'); const name=document.createElement('td'); name.textContent=user.displayName + ' (@' + user.username + ')'; const role=document.createElement('td'); role.textContent=user.role; const active=document.createElement('td'); active.textContent=user.active ? 'aktiv' : 'deaktiviert'; const familyCell=document.createElement('td'); const select=document.createElement('select'); const emptyOption=document.createElement('option'); emptyOption.value=''; emptyOption.textContent='kein Haushalt'; select.appendChild(emptyOption); appData.families.forEach(function(family){ const opt=document.createElement('option'); opt.value=family.id; opt.textContent=family.name; if (user.familyId === family.id) opt.selected=true; select.appendChild(opt); }); select.addEventListener('change', function(){ adminUpdateUserFamily(user.id, select.value); }); familyCell.appendChild(select); const actions=document.createElement('td'); actions.className='inline-actions'; const roleBtn=document.createElement('button'); roleBtn.className='small-btn'; roleBtn.textContent=user.role==='admin'?'zu Nutzer':'zu Admin'; roleBtn.addEventListener('click', function(){ adminUpdateRole(user.id, user.role==='admin'?'user':'admin'); }); const activeBtn=document.createElement('button'); activeBtn.className='warning'; activeBtn.textContent=user.active?'Deaktivieren':'Aktivieren'; activeBtn.addEventListener('click', function(){ adminToggleActive(user.id); }); const del=document.createElement('button'); del.className='danger'; del.textContent='Löschen'; del.addEventListener('click', function(){ adminDeleteUser(user.id); }); actions.appendChild(roleBtn); actions.appendChild(activeBtn); actions.appendChild(del); row.appendChild(name); row.appendChild(role); row.appendChild(active); row.appendChild(familyCell); row.appendChild(actions); userTableBody.appendChild(row); }); }
        function renderAdminListTable() { adminListTableBody.textContent=''; let lists=appData.lists.slice(); const filter=adminListFilter.value; if (filter==='own') lists=lists.filter(function(list){ return appData.currentUser && list.ownerId===appData.currentUser.id; }); if (filter==='shared') lists=lists.filter(function(list){ return list.isShared; }); if (filter==='shopping') lists=lists.filter(function(list){ return list.listType==='shopping'; }); if (filter==='household') lists=lists.filter(function(list){ return list.listType==='household'; }); if (filter==='private') lists=lists.filter(function(list){ return !list.isShared; }); lists.forEach(function(list){ const row=document.createElement('tr'); const name=document.createElement('td'); name.textContent=list.name; const type=document.createElement('td'); const typeSelect=document.createElement('select'); [['shopping','Einkaufsliste'],['household','Haushaltsliste'],['other','Sonstige Liste']].forEach(function(pair){ const opt=document.createElement('option'); opt.value=pair[0]; opt.textContent=pair[1]; if ((list.listType || 'shopping')===pair[0]) opt.selected=true; typeSelect.appendChild(opt); }); type.appendChild(typeSelect); const owner=document.createElement('td'); const ownerSelect=document.createElement('select'); appData.activeUsers.forEach(function(user){ const opt=document.createElement('option'); opt.value=user.id; opt.textContent=user.displayName + ' – ' + getFamilyName(user.familyId); if (user.id===list.ownerId) opt.selected=true; ownerSelect.appendChild(opt); }); owner.appendChild(ownerSelect); const family=document.createElement('td'); family.textContent=getFamilyName(list.familyId); const status=document.createElement('td'); status.textContent=list.isShared?'gemeinschaftlich':'privat'; const actions=document.createElement('td'); actions.className='inline-actions'; const typeBtn=document.createElement('button'); typeBtn.className='small-btn'; typeBtn.textContent='Typ speichern'; typeBtn.addEventListener('click', function(){ adminUpdateListType(list.id, typeSelect.value); }); const ownerBtn=document.createElement('button'); ownerBtn.className='small-btn'; ownerBtn.textContent='Besitzer speichern'; ownerBtn.addEventListener('click', function(){ adminUpdateListOwner(list.id, ownerSelect.value); }); const scopeBtn=document.createElement('button'); scopeBtn.className='warning'; scopeBtn.textContent=list.isShared?'Privat':'Teilen'; scopeBtn.addEventListener('click', function(){ adminToggleListVisibility(list); }); actions.appendChild(typeBtn); actions.appendChild(ownerBtn); actions.appendChild(scopeBtn); row.appendChild(name); row.appendChild(type); row.appendChild(owner); row.appendChild(family); row.appendChild(status); row.appendChild(actions); adminListTableBody.appendChild(row); }); }
        function renderAdminTodoTable() { adminTodoTableBody.textContent=''; let todos=(appData.adminTodos || appData.todos || []).slice(); const filter=adminTodoFilter.value; if (filter==='private') todos=todos.filter((todo)=>todo.scope==='private'); if (filter==='family') todos=todos.filter((todo)=>todo.scope==='family'); if (filter==='open') todos=todos.filter((todo)=>todo.status==='open'); if (filter==='done') todos=todos.filter((todo)=>todo.status==='done'); todos.forEach(function(todo){ const row=document.createElement('tr'); [['title',todo.title],['scope',todoScopeLabel(todo.scope)],['priority',priorityLabel(todo.priority)],['assigned',getUserName(todo.assignedTo)],['due',formatDate(todo.dueAt)],['reminder',formatDate(todo.reminderAt)],['status',todoStatusLabel(todo.status)],['comments',String((todo.comments || []).length)]].forEach(function(pair){ const td=document.createElement('td'); td.textContent=pair[1]; row.appendChild(td); }); const actions=document.createElement('td'); actions.className='inline-actions'; const toggle=document.createElement('button'); toggle.className='small-btn'; toggle.textContent=todo.status==='done'?'Öffnen':'Erledigt'; toggle.addEventListener('click', function(){ toggleTodoDone(todo.id); }); const edit=document.createElement('button'); edit.className='small-btn'; edit.textContent='Titel ändern'; edit.addEventListener('click', function(){ const title=prompt('Neuer Aufgabentitel:', todo.title); if (title !== null) updateTodo(todo.id, { title:title.trim(), scope:todo.scope, priority:todo.priority, assignedTo:todo.assignedTo, dueAt:todo.dueAt, reminderAt:todo.reminderAt, calendarDate:todo.calendarDate }); }); const del=document.createElement('button'); del.className='danger'; del.textContent='Löschen'; del.addEventListener('click', function(){ deleteTodo(todo.id); }); actions.appendChild(toggle); actions.appendChild(edit); actions.appendChild(del); row.appendChild(actions); adminTodoTableBody.appendChild(row); }); }
        function switchAdminTab(tabName) { adminTabButtons.forEach(function(button){ button.classList.toggle('active', button.dataset.adminTab === tabName); }); adminTabFamilies.classList.toggle('active', tabName === 'families'); adminTabUsers.classList.toggle('active', tabName === 'users'); adminTabLists.classList.toggle('active', tabName === 'lists'); adminTabTodos.classList.toggle('active', tabName === 'todos'); }
        function showStatus(message, isError) { statusMessage.textContent=message; statusMessage.classList.toggle('error', isError===true); window.clearTimeout(showStatus.timeoutId); showStatus.timeoutId=window.setTimeout(function(){ statusMessage.textContent=''; statusMessage.classList.remove('error'); }, 3500); }

        listForm.addEventListener('submit', function(event){ event.preventDefault(); createList(); });
        todoForm.addEventListener('submit', function(event){ event.preventDefault(); createTodo(); });
        todoFamilyInput.addEventListener('change', refreshCreateTodoAssignees);
        itemForm.addEventListener('submit', function(event){ event.preventDefault(); addItem(); });
        toggleVisibilityButton.addEventListener('click', updateActiveListVisibility);
        updateListTypeButton.addEventListener('click', updateActiveListType);
        deleteListButton.addEventListener('click', deleteActiveList);
        updateOwnerButton.addEventListener('click', updateActiveListOwner);
        familyForm.addEventListener('submit', function(event){ event.preventDefault(); adminCreateFamily(); });
        adminListFilter.addEventListener('change', renderAdminListTable);
        adminTodoFilter.addEventListener('change', renderAdminTodoTable);
        adminTabButtons.forEach(function(button){ button.addEventListener('click', function(){ switchAdminTab(button.dataset.adminTab); }); });
        loadData();
    </script>
</body>
</html>
