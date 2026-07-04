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
    <title><?= e('HaushaltsPilot – Teil 07 Login & Rollen') ?></title>
    <meta name="csrf-token" content="<?= e($csrfToken) ?>">
    <style>
        :root { --card:#fff; --text:#1f2937; --muted:#6b7280; --primary:#2563eb; --primary-dark:#1d4ed8; --success:#16a34a; --success-dark:#15803d; --danger:#dc2626; --danger-dark:#b91c1c; --warning:#f59e0b; --border:#d1d5db; --soft:#f9fafb; --done:#e5e7eb; --shadow:0 18px 40px rgba(15,23,42,.12); --radius:18px; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; font-family:Arial,Helvetica,sans-serif; background:linear-gradient(135deg,#e0ecff,#f8fafc); color:var(--text); padding:20px; }
        .app { width:100%; max-width:1180px; margin:0 auto; background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow); padding:24px; }
        .topbar { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:22px; }
        h1 { margin:0 0 8px; font-size:1.9rem; } p { color:var(--muted); line-height:1.5; margin:0; }
        .userbox { text-align:right; color:var(--muted); font-size:.9rem; white-space:nowrap; }
        .userbox strong { display:block; color:var(--text); margin-bottom:6px; }
        .logout { border:0; border-radius:10px; background:var(--danger); color:white; padding:8px 10px; font-weight:bold; cursor:pointer; }
        .logout:hover{background:var(--danger-dark)}
        .security-note { margin:0 0 20px; background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; border-radius:14px; padding:12px 14px; font-size:.88rem; line-height:1.45; }
        .layout { display:grid; grid-template-columns:310px 1fr; gap:22px; align-items:start; }
        .panel { background:var(--soft); border:1px solid var(--border); border-radius:16px; padding:16px; }
        .panel h2 { margin:0 0 14px; font-size:1.1rem; }
        .list-form, .item-form { display:grid; gap:10px; margin-bottom:12px; }
        .item-form { grid-template-columns:1.5fr 1fr 1fr auto; }
        input, select { width:100%; border:1px solid var(--border); border-radius:12px; padding:12px 14px; font-size:1rem; outline:none; background:white; }
        input:focus, select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.15); }
        button { border:0; border-radius:12px; padding:12px 14px; font-size:.95rem; cursor:pointer; transition:.2s ease; }
        button:disabled { background:#d1d5db !important; color:#6b7280 !important; cursor:not-allowed; }
        .add-btn { background:var(--primary); color:white; font-weight:bold; } .add-btn:hover { background:var(--primary-dark); }
        .delete-list-btn { width:100%; background:var(--danger); color:white; font-weight:bold; margin-top:12px; } .delete-list-btn:hover { background:var(--danger-dark); }
        .list-buttons { display:grid; gap:8px; margin-top:12px; }
        .list-button { display:flex; justify-content:space-between; align-items:center; gap:8px; width:100%; background:white; border:1px solid var(--border); color:var(--text); text-align:left; }
        .list-button:hover { border-color:var(--primary); } .list-button.active { background:var(--primary); border-color:var(--primary); color:white; }
        .list-button small { opacity:.85; white-space:nowrap; }
        .scope-row { display:flex; gap:8px; align-items:center; color:var(--muted); font-size:.88rem; }
        .scope-row input { width:auto; }
        .current-title { display:flex; justify-content:space-between; align-items:baseline; gap:12px; margin-bottom:14px; }
        .current-title h2 { margin:0; font-size:1.25rem; } .current-title span { color:var(--muted); font-size:.9rem; }
        .status-message { min-height:22px; margin-bottom:12px; font-size:.88rem; color:var(--muted); text-align:center; } .status-message.error { color:var(--danger); font-weight:bold; }
        .stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
        .stat-card { background:white; border:1px solid var(--border); border-radius:14px; padding:12px; text-align:center; } .stat-card span { display:block; color:var(--muted); font-size:.82rem; margin-bottom:4px; } .stat-card strong { font-size:1.3rem; }
        .shopping-list { list-style:none; padding:0; margin:0; }
        .shopping-item { display:grid; grid-template-columns:1fr auto; gap:12px; align-items:center; padding:14px; border:1px solid var(--border); border-radius:14px; margin-bottom:10px; background:white; }
        .shopping-item.done { background:var(--done); opacity:.75; } .shopping-item.done .item-name { text-decoration:line-through; color:var(--muted); }
        .item-main { min-width:0; } .item-name { display:block; font-weight:bold; word-break:break-word; margin-bottom:5px; }
        .item-meta { display:flex; flex-wrap:wrap; gap:6px; } .badge { display:inline-block; border-radius:999px; padding:4px 8px; font-size:.78rem; background:var(--soft); border:1px solid var(--border); color:var(--muted); }
        .item-actions { display:flex; gap:8px; align-items:center; } .toggle-btn { background:var(--success); color:white; padding:8px 10px; font-size:.85rem; white-space:nowrap; } .toggle-btn:hover { background:var(--success-dark); } .delete-btn { background:var(--danger); color:white; padding:8px 10px; font-size:.85rem; white-space:nowrap; } .delete-btn:hover { background:var(--danger-dark); }
        .empty-message { text-align:center; color:var(--muted); background:white; border:1px dashed var(--border); border-radius:12px; padding:18px; margin-top:12px; }
        .admin-panel { margin-top:22px; display:none; } .admin-panel.visible { display:block; }
        .user-table { width:100%; border-collapse:collapse; background:white; border-radius:14px; overflow:hidden; }
        .user-table th, .user-table td { border-bottom:1px solid var(--border); padding:10px; text-align:left; font-size:.9rem; }
        .user-actions { display:flex; flex-wrap:wrap; gap:6px; }
        .small-btn { padding:7px 9px; border-radius:9px; font-size:.82rem; background:var(--primary); color:white; }
        .small-danger { background:var(--danger); }
        .footer-note { margin-top:22px; font-size:.82rem; color:var(--muted); text-align:center; line-height:1.4; }
        @media(max-width:900px){ .layout{grid-template-columns:1fr;} .item-form{grid-template-columns:1fr;} .topbar{display:block}.userbox{text-align:left;margin-top:14px} }
        @media(max-width:560px){ body{padding:12px}.app{padding:18px} h1{font-size:1.5rem}.stats{grid-template-columns:1fr}.shopping-item{grid-template-columns:1fr}.item-actions{width:100%}.toggle-btn,.delete-btn{flex:1}.current-title{display:block}.current-title span{display:block;margin-top:4px}.user-table{display:block;overflow-x:auto;} }
    </style>
</head>
<body>
    <main class="app">
        <header class="topbar">
            <div>
                <h1>HaushaltsPilot</h1>
                <p>Teil 07: Login, Registrierung, Sessions und Rollen/Rechte.</p>
            </div>
            <div class="userbox">
                <strong id="currentUserLabel">Lade Benutzer…</strong>
                <form method="post" action="auth.php">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="action" value="logout">
                    <button class="logout" type="submit">Logout</button>
                </form>
            </div>
        </header>

        <div class="security-note">
            Rollenbasis: Admins sehen und verwalten alles. Nutzer sehen und verwalten eigene Listen sowie freigegebene Gemeinschaftslisten. Die gleiche Rechteidee ist für spätere Todos, Personal Messages, Einzelchat, Familienchat und Kalender vorbereitet.
        </div>

        <section class="layout">
            <aside class="panel">
                <h2>Listen</h2>
                <form class="list-form" id="listForm">
                    <input type="text" id="listNameInput" placeholder="Neue Liste, z. B. Baumarkt" autocomplete="off" maxlength="40">
                    <label class="scope-row"><input type="checkbox" id="listSharedInput"> als Gemeinschaftsliste freigeben</label>
                    <button class="add-btn" type="submit">Liste erstellen</button>
                </form>
                <div class="list-buttons" id="listButtons"></div>
                <button class="delete-list-btn" id="deleteListButton">Aktive Liste löschen</button>
            </aside>

            <section class="panel">
                <div class="current-title">
                    <h2 id="currentListTitle">Aktive Liste</h2>
                    <span id="currentListInfo">0 Artikel</span>
                </div>

                <form class="item-form" id="itemForm">
                    <input type="text" id="itemNameInput" placeholder="Artikel, z. B. Milch" autocomplete="off" maxlength="80">
                    <input type="text" id="itemAmountInput" placeholder="Menge, z. B. 2 Liter" autocomplete="off" maxlength="40">
                    <select id="itemCategoryInput">
                        <option value="Lebensmittel">Lebensmittel</option>
                        <option value="Getränke">Getränke</option>
                        <option value="Haushalt">Haushalt</option>
                        <option value="Drogerie">Drogerie</option>
                        <option value="Schule">Schule</option>
                        <option value="Medikamente">Medikamente</option>
                        <option value="Sonstiges">Sonstiges</option>
                    </select>
                    <button class="add-btn" type="submit">Hinzufügen</button>
                </form>

                <div class="status-message" id="statusMessage"></div>

                <section class="stats" aria-label="Listenstatistik">
                    <div class="stat-card"><span>Gesamt</span><strong id="totalCount">0</strong></div>
                    <div class="stat-card"><span>Offen</span><strong id="openCount">0</strong></div>
                    <div class="stat-card"><span>Erledigt</span><strong id="doneCount">0</strong></div>
                </section>

                <ul class="shopping-list" id="shoppingList"></ul>
                <div class="empty-message" id="emptyMessage">Diese Liste ist leer. Füge deinen ersten Artikel hinzu.</div>
            </section>
        </section>

        <section class="panel admin-panel" id="adminPanel">
            <h2>Admin: Benutzerverwaltung</h2>
            <table class="user-table">
                <thead><tr><th>Benutzer</th><th>Rolle</th><th>Status</th><th>Erstellt</th><th>Aktionen</th></tr></thead>
                <tbody id="userTableBody"></tbody>
            </table>
        </section>

        <p class="footer-note">Teil 08 kann auf dieser Rechtebasis Gemeinschaftslisten weiter ausbauen oder direkt Familiengruppen ergänzen.</p>
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

        const listForm = document.getElementById("listForm");
        const listNameInput = document.getElementById("listNameInput");
        const listSharedInput = document.getElementById("listSharedInput");
        const listButtons = document.getElementById("listButtons");
        const deleteListButton = document.getElementById("deleteListButton");
        const itemForm = document.getElementById("itemForm");
        const itemNameInput = document.getElementById("itemNameInput");
        const itemAmountInput = document.getElementById("itemAmountInput");
        const itemCategoryInput = document.getElementById("itemCategoryInput");
        const currentListTitle = document.getElementById("currentListTitle");
        const currentListInfo = document.getElementById("currentListInfo");
        const shoppingList = document.getElementById("shoppingList");
        const emptyMessage = document.getElementById("emptyMessage");
        const statusMessage = document.getElementById("statusMessage");
        const totalCount = document.getElementById("totalCount");
        const openCount = document.getElementById("openCount");
        const doneCount = document.getElementById("doneCount");
        const currentUserLabel = document.getElementById("currentUserLabel");
        const adminPanel = document.getElementById("adminPanel");
        const userTableBody = document.getElementById("userTableBody");

        let appData = { currentUser: null, lists: [], activeListId: null, users: [], isAdmin: false };

        async function apiGet(action) {
            const response = await fetch("api.php?action=" + encodeURIComponent(action), {
                method: "GET",
                credentials: "same-origin",
                headers: { "Accept": "application/json" }
            });
            return handleApiResponse(response);
        }

        async function apiPost(action, payload) {
            const response = await fetch("api.php?action=" + encodeURIComponent(action), {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-Token": csrfToken
                },
                body: JSON.stringify(payload || {})
            });
            return handleApiResponse(response);
        }

        async function handleApiResponse(response) {
            let result;
            try { result = await response.json(); } catch (error) { throw new Error("Serverantwort war kein gültiges JSON."); }
            if (!response.ok || result.success !== true) { throw new Error(result.message || "Unbekannter Serverfehler."); }
            return result;
        }

        async function loadData() {
            try {
                const result = await apiGet("load");
                appData = result.data;
                renderApp();
            } catch (error) {
                showStatus(error.message, true);
                if (error.message === "Nicht angemeldet.") { window.location.href = "login.php"; }
            }
        }

        function getActiveList() {
            return appData.lists.find(function(list) { return list.id === appData.activeListId; });
        }

        function getUserName(userId) {
            if (appData.currentUser && appData.currentUser.id === userId) return appData.currentUser.displayName;
            const user = appData.users.find(function(item) { return item.id === userId; });
            return user ? user.displayName : "Unbekannt";
        }

        async function createList() {
            const name = listNameInput.value.trim();
            if (name === "") { showStatus("Bitte gib einen Listennamen ein.", true); listNameInput.focus(); return; }
            try {
                const result = await apiPost("create_list", { name: name, isShared: listSharedInput.checked });
                appData = result.data;
                listNameInput.value = "";
                listSharedInput.checked = false;
                renderApp();
                showStatus(result.message);
            } catch (error) { showStatus(error.message, true); }
        }

        async function selectList(listId) {
            try {
                const result = await apiPost("set_active_list", { listId: listId });
                appData = result.data;
                renderApp();
            } catch (error) { showStatus(error.message, true); }
        }

        async function deleteActiveList() {
            const activeList = getActiveList();
            if (!activeList) return;
            const confirmed = confirm("Möchtest du die Liste \"" + activeList.name + "\" wirklich löschen?");
            if (!confirmed) return;
            try {
                const result = await apiPost("delete_list", { listId: activeList.id });
                appData = result.data;
                renderApp();
                showStatus(result.message);
            } catch (error) { showStatus(error.message, true); }
        }

        async function addItem() {
            const activeList = getActiveList();
            if (!activeList) { showStatus("Keine aktive Liste gefunden.", true); return; }
            const name = itemNameInput.value.trim();
            if (name === "") { showStatus("Bitte gib einen Artikelnamen ein.", true); itemNameInput.focus(); return; }
            try {
                const result = await apiPost("add_item", { listId: activeList.id, name: name, amount: itemAmountInput.value.trim(), category: itemCategoryInput.value });
                appData = result.data;
                itemNameInput.value = "";
                itemAmountInput.value = "";
                itemCategoryInput.value = "Lebensmittel";
                renderApp();
                showStatus(result.message);
                itemNameInput.focus();
            } catch (error) { showStatus(error.message, true); }
        }

        async function toggleItemDone(itemId) {
            const activeList = getActiveList();
            if (!activeList) return;
            try {
                const result = await apiPost("toggle_item", { listId: activeList.id, itemId: itemId });
                appData = result.data;
                renderApp();
                showStatus(result.message);
            } catch (error) { showStatus(error.message, true); }
        }

        async function deleteItem(itemId) {
            const activeList = getActiveList();
            if (!activeList) return;
            try {
                const result = await apiPost("delete_item", { listId: activeList.id, itemId: itemId });
                appData = result.data;
                renderApp();
                showStatus(result.message);
            } catch (error) { showStatus(error.message, true); }
        }

        async function adminUpdateRole(userId, role) {
            try {
                const result = await apiPost("admin_update_user_role", { userId: userId, role: role });
                appData = result.data;
                renderApp();
                showStatus(result.message);
            } catch (error) { showStatus(error.message, true); }
        }

        async function adminToggleActive(userId) {
            try {
                const result = await apiPost("admin_toggle_user_active", { userId: userId });
                appData = result.data;
                renderApp();
                showStatus(result.message);
            } catch (error) { showStatus(error.message, true); }
        }

        async function adminDeleteUser(userId) {
            if (!confirm("Benutzer wirklich löschen? Eigene Listen dieses Benutzers werden entfernt.")) return;
            try {
                const result = await apiPost("admin_delete_user", { userId: userId });
                appData = result.data;
                renderApp();
                showStatus(result.message);
            } catch (error) { showStatus(error.message, true); }
        }

        function renderApp() {
            renderCurrentUser();
            renderListButtons();
            renderActiveList();
            renderAdminPanel();
        }

        function renderCurrentUser() {
            if (!appData.currentUser) return;
            currentUserLabel.textContent = appData.currentUser.displayName + " (" + appData.currentUser.role + ")";
        }

        function renderListButtons() {
            listButtons.textContent = "";
            appData.lists.forEach(function(list) {
                const button = document.createElement("button");
                button.className = "list-button";
                button.type = "button";
                if (list.id === appData.activeListId) button.classList.add("active");

                const nameSpan = document.createElement("span");
                nameSpan.textContent = list.name;

                const infoSmall = document.createElement("small");
                infoSmall.textContent = (list.isShared ? "Gemeinsam" : "Privat") + " · " + list.items.length;

                button.appendChild(nameSpan);
                button.appendChild(infoSmall);
                button.addEventListener("click", function() { selectList(list.id); });
                listButtons.appendChild(button);
            });
            deleteListButton.disabled = appData.lists.length === 0;
        }

        function renderActiveList() {
            const activeList = getActiveList();
            shoppingList.textContent = "";
            if (!activeList) {
                currentListTitle.textContent = "Keine Liste";
                currentListInfo.textContent = "0 Artikel";
                updateStats([]);
                return;
            }
            currentListTitle.textContent = activeList.name;
            currentListInfo.textContent = (activeList.isShared ? "Gemeinschaftsliste" : "Private Liste") + " · Besitzer: " + getUserName(activeList.ownerId);

            activeList.items.forEach(function(item) {
                const listItem = document.createElement("li");
                listItem.className = "shopping-item";
                if (item.done) listItem.classList.add("done");

                const itemMain = document.createElement("div");
                itemMain.className = "item-main";
                const itemName = document.createElement("span");
                itemName.className = "item-name";
                itemName.textContent = item.name;
                const itemMeta = document.createElement("div");
                itemMeta.className = "item-meta";

                const values = [
                    item.amount !== "" ? "Menge: " + item.amount : "Menge: keine Angabe",
                    "Kategorie: " + item.category,
                    item.done ? "Status: erledigt" : "Status: offen",
                    "Von: " + getUserName(item.createdBy)
                ];
                values.forEach(function(value) {
                    const badge = document.createElement("span");
                    badge.className = "badge";
                    badge.textContent = value;
                    itemMeta.appendChild(badge);
                });

                itemMain.appendChild(itemName);
                itemMain.appendChild(itemMeta);

                const itemActions = document.createElement("div");
                itemActions.className = "item-actions";
                const toggleButton = document.createElement("button");
                toggleButton.className = "toggle-btn";
                toggleButton.type = "button";
                toggleButton.textContent = item.done ? "Öffnen" : "Erledigt";
                toggleButton.addEventListener("click", function() { toggleItemDone(item.id); });
                const deleteButton = document.createElement("button");
                deleteButton.className = "delete-btn";
                deleteButton.type = "button";
                deleteButton.textContent = "Löschen";
                deleteButton.addEventListener("click", function() { deleteItem(item.id); });
                itemActions.appendChild(toggleButton);
                itemActions.appendChild(deleteButton);

                listItem.appendChild(itemMain);
                listItem.appendChild(itemActions);
                shoppingList.appendChild(listItem);
            });

            updateStats(activeList.items);
        }

        function renderAdminPanel() {
            adminPanel.classList.toggle("visible", appData.isAdmin === true);
            userTableBody.textContent = "";
            if (!appData.isAdmin) return;

            appData.users.forEach(function(user) {
                const row = document.createElement("tr");
                const nameCell = document.createElement("td");
                nameCell.textContent = user.displayName + " (@" + user.username + ")";
                const roleCell = document.createElement("td");
                roleCell.textContent = user.role;
                const statusCell = document.createElement("td");
                statusCell.textContent = user.active ? "aktiv" : "deaktiviert";
                const createdCell = document.createElement("td");
                createdCell.textContent = user.createdAt;
                const actionsCell = document.createElement("td");
                actionsCell.className = "user-actions";

                const roleButton = document.createElement("button");
                roleButton.className = "small-btn";
                roleButton.type = "button";
                roleButton.textContent = user.role === "admin" ? "zu Nutzer" : "zu Admin";
                roleButton.addEventListener("click", function() { adminUpdateRole(user.id, user.role === "admin" ? "user" : "admin"); });

                const activeButton = document.createElement("button");
                activeButton.className = "small-btn";
                activeButton.type = "button";
                activeButton.textContent = user.active ? "deaktivieren" : "aktivieren";
                activeButton.addEventListener("click", function() { adminToggleActive(user.id); });

                const deleteButton = document.createElement("button");
                deleteButton.className = "small-btn small-danger";
                deleteButton.type = "button";
                deleteButton.textContent = "löschen";
                deleteButton.addEventListener("click", function() { adminDeleteUser(user.id); });

                actionsCell.appendChild(roleButton);
                actionsCell.appendChild(activeButton);
                actionsCell.appendChild(deleteButton);

                row.appendChild(nameCell);
                row.appendChild(roleCell);
                row.appendChild(statusCell);
                row.appendChild(createdCell);
                row.appendChild(actionsCell);
                userTableBody.appendChild(row);
            });
        }

        function updateStats(items) {
            const total = items.length;
            const done = items.filter(function(item) { return item.done === true; }).length;
            const open = total - done;
            totalCount.textContent = total;
            openCount.textContent = open;
            doneCount.textContent = done;
            emptyMessage.style.display = total === 0 ? "block" : "none";
        }

        function showStatus(message, isError) {
            statusMessage.textContent = message;
            statusMessage.classList.toggle("error", isError === true);
            window.clearTimeout(showStatus.timeoutId);
            showStatus.timeoutId = window.setTimeout(function() {
                statusMessage.textContent = "";
                statusMessage.classList.remove("error");
            }, 3500);
        }

        listForm.addEventListener("submit", function(event) { event.preventDefault(); createList(); });
        itemForm.addEventListener("submit", function(event) { event.preventDefault(); addItem(); });
        deleteListButton.addEventListener("click", deleteActiveList);

        loadData();
    </script>
</body>
</html>
