<?php
declare(strict_types=1);

require __DIR__ . '/includes/security.php';

$csrfToken = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e('HaushaltsPilot – Teil 05 PHP-JSON-Backend') ?></title>
    <meta name="csrf-token" content="<?= e($csrfToken) ?>">

    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #16a34a;
            --success-dark: #15803d;
            --danger: #dc2626;
            --danger-dark: #b91c1c;
            --warning: #f59e0b;
            --warning-dark: #d97706;
            --border: #d1d5db;
            --soft: #f9fafb;
            --done: #e5e7eb;
            --shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
            --radius: 18px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #e0ecff, #f8fafc);
            color: var(--text);
            padding: 20px;
        }

        .app {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .app-header {
            margin-bottom: 24px;
            text-align: center;
        }

        .app-header h1 {
            margin: 0 0 8px;
            font-size: 1.9rem;
        }

        .app-header p {
            margin: 0;
            color: var(--muted);
            line-height: 1.5;
            font-size: 0.95rem;
        }

        .security-note {
            margin: 16px auto 0;
            max-width: 760px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
            border-radius: 14px;
            padding: 12px 14px;
            font-size: 0.88rem;
            line-height: 1.45;
        }

        .layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 22px;
            align-items: start;
        }

        .panel {
            background: var(--soft);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px;
        }

        .panel h2 {
            margin: 0 0 14px;
            font-size: 1.1rem;
        }

        .list-form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-bottom: 14px;
        }

        .item-form {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr auto;
            gap: 10px;
            margin-bottom: 12px;
        }

        input,
        select {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 1rem;
            outline: none;
            background: white;
        }

        input:focus,
        select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        button {
            border: none;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: 0.2s ease;
        }

        button:disabled {
            background: #d1d5db !important;
            color: #6b7280 !important;
            cursor: not-allowed;
        }

        .add-btn {
            background: var(--primary);
            color: white;
            font-weight: bold;
        }

        .add-btn:hover {
            background: var(--primary-dark);
        }

        .delete-list-btn {
            width: 100%;
            background: var(--danger);
            color: white;
            font-weight: bold;
            margin-top: 12px;
        }

        .delete-list-btn:hover {
            background: var(--danger-dark);
        }

        .reset-btn {
            width: 100%;
            background: var(--warning);
            color: white;
            font-weight: bold;
            margin-top: 10px;
        }

        .reset-btn:hover {
            background: var(--warning-dark);
        }

        .list-buttons {
            display: grid;
            gap: 8px;
        }

        .list-button {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            width: 100%;
            background: white;
            border: 1px solid var(--border);
            color: var(--text);
            text-align: left;
        }

        .list-button:hover {
            border-color: var(--primary);
        }

        .list-button.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .list-button small {
            opacity: 0.8;
            white-space: nowrap;
        }

        .current-title {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
            margin-bottom: 14px;
        }

        .current-title h2 {
            margin: 0;
            font-size: 1.25rem;
        }

        .current-title span {
            color: var(--muted);
            font-size: 0.9rem;
        }

        .status-message {
            min-height: 22px;
            margin-bottom: 12px;
            font-size: 0.88rem;
            color: var(--muted);
            text-align: center;
        }

        .status-message.error {
            color: var(--danger);
            font-weight: bold;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }

        .stat-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px;
            text-align: center;
        }

        .stat-card span {
            display: block;
            color: var(--muted);
            font-size: 0.82rem;
            margin-bottom: 4px;
        }

        .stat-card strong {
            font-size: 1.3rem;
        }

        .shopping-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .shopping-item {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: center;
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 14px;
            margin-bottom: 10px;
            background: white;
        }

        .shopping-item.done {
            background: var(--done);
            opacity: 0.75;
        }

        .shopping-item.done .item-name {
            text-decoration: line-through;
            color: var(--muted);
        }

        .item-main {
            min-width: 0;
        }

        .item-name {
            display: block;
            font-weight: bold;
            word-break: break-word;
            margin-bottom: 5px;
        }

        .item-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 8px;
            font-size: 0.78rem;
            background: var(--soft);
            border: 1px solid var(--border);
            color: var(--muted);
        }

        .item-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .toggle-btn {
            background: var(--success);
            color: white;
            padding: 8px 10px;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .toggle-btn:hover {
            background: var(--success-dark);
        }

        .delete-btn {
            background: var(--danger);
            color: white;
            padding: 8px 10px;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .delete-btn:hover {
            background: var(--danger-dark);
        }

        .empty-message {
            text-align: center;
            color: var(--muted);
            background: white;
            border: 1px dashed var(--border);
            border-radius: 12px;
            padding: 18px;
            margin-top: 12px;
        }

        .footer-note {
            margin-top: 22px;
            font-size: 0.82rem;
            color: var(--muted);
            text-align: center;
            line-height: 1.4;
        }

        @media (max-width: 860px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .item-form {
                grid-template-columns: 1fr;
            }

            .add-btn {
                width: 100%;
            }
        }

        @media (max-width: 520px) {
            body {
                padding: 12px;
            }

            .app {
                padding: 18px;
            }

            .app-header h1 {
                font-size: 1.5rem;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .shopping-item {
                grid-template-columns: 1fr;
            }

            .item-actions {
                width: 100%;
            }

            .toggle-btn,
            .delete-btn {
                flex: 1;
            }

            .current-title {
                display: block;
            }

            .current-title span {
                display: block;
                margin-top: 4px;
            }
        }
    </style>
</head>
<body>

    <main class="app">
        <header class="app-header">
            <h1>HaushaltsPilot</h1>
            <p>
                Teil 05 der Tutorial-Reihe.
                Die Listen werden jetzt serverseitig mit PHP und JSON gespeichert.
            </p>

            <div class="security-note">
                Sicherheitsbasis: CSRF-Token für POST-Aktionen, serverseitige Validierung,
                sichere Textausgabe und kein Rendering über innerHTML.
            </div>
        </header>

        <section class="layout">
            <aside class="panel">
                <h2>Meine Listen</h2>

                <form class="list-form" id="listForm">
                    <input
                        type="text"
                        id="listNameInput"
                        placeholder="Neue Liste, z. B. Baumarkt"
                        autocomplete="off"
                        maxlength="40"
                    >
                    <button class="add-btn" type="submit">Liste erstellen</button>
                </form>

                <div class="list-buttons" id="listButtons"></div>

                <button class="delete-list-btn" id="deleteListButton">
                    Aktive Liste löschen
                </button>

                <button class="reset-btn" id="resetButton">
                    Alles zurücksetzen
                </button>
            </aside>

            <section class="panel">
                <div class="current-title">
                    <h2 id="currentListTitle">Aktive Liste</h2>
                    <span id="currentListInfo">0 Artikel</span>
                </div>

                <form class="item-form" id="itemForm">
                    <input
                        type="text"
                        id="itemNameInput"
                        placeholder="Artikel, z. B. Milch"
                        autocomplete="off"
                        maxlength="80"
                    >

                    <input
                        type="text"
                        id="itemAmountInput"
                        placeholder="Menge, z. B. 2 Liter"
                        autocomplete="off"
                        maxlength="40"
                    >

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
                    <div class="stat-card">
                        <span>Gesamt</span>
                        <strong id="totalCount">0</strong>
                    </div>

                    <div class="stat-card">
                        <span>Offen</span>
                        <strong id="openCount">0</strong>
                    </div>

                    <div class="stat-card">
                        <span>Erledigt</span>
                        <strong id="doneCount">0</strong>
                    </div>
                </section>

                <ul class="shopping-list" id="shoppingList"></ul>

                <div class="empty-message" id="emptyMessage">
                    Diese Liste ist leer. Füge deinen ersten Artikel hinzu.
                </div>
            </section>
        </section>

        <p class="footer-note">
            In Teil 06 ersetzen wir die JSON-Datei durch eine Datenbank mit PDO und Prepared Statements.
        </p>
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

        const listForm = document.getElementById("listForm");
        const listNameInput = document.getElementById("listNameInput");
        const listButtons = document.getElementById("listButtons");
        const deleteListButton = document.getElementById("deleteListButton");
        const resetButton = document.getElementById("resetButton");

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

        let appData = {
            lists: [],
            activeListId: null
        };

        async function apiGet(action) {
            const response = await fetch("api.php?action=" + encodeURIComponent(action), {
                method: "GET",
                credentials: "same-origin",
                headers: {
                    "Accept": "application/json"
                }
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

            try {
                result = await response.json();
            } catch (error) {
                throw new Error("Serverantwort war kein gültiges JSON.");
            }

            if (!response.ok || result.success !== true) {
                throw new Error(result.message || "Unbekannter Serverfehler.");
            }

            return result;
        }

        async function loadData() {
            try {
                const result = await apiGet("load");
                appData = result.data;
                renderApp();
            } catch (error) {
                showStatus(error.message, true);
            }
        }

        function getActiveList() {
            return appData.lists.find(function(list) {
                return list.id === appData.activeListId;
            });
        }

        async function createList() {
            const name = listNameInput.value.trim();

            if (name === "") {
                showStatus("Bitte gib einen Listennamen ein.", true);
                listNameInput.focus();
                return;
            }

            try {
                const result = await apiPost("create_list", {
                    name: name
                });

                appData = result.data;
                listNameInput.value = "";
                listNameInput.focus();

                renderApp();
                showStatus(result.message);
            } catch (error) {
                showStatus(error.message, true);
            }
        }

        async function selectList(listId) {
            try {
                const result = await apiPost("set_active_list", {
                    listId: listId
                });

                appData = result.data;
                renderApp();
            } catch (error) {
                showStatus(error.message, true);
            }
        }

        async function deleteActiveList() {
            const activeList = getActiveList();

            if (!activeList) {
                return;
            }

            if (appData.lists.length <= 1) {
                showStatus("Die letzte Liste kann nicht gelöscht werden.", true);
                return;
            }

            const confirmed = confirm("Möchtest du die Liste \"" + activeList.name + "\" wirklich löschen?");

            if (!confirmed) {
                return;
            }

            try {
                const result = await apiPost("delete_list", {
                    listId: activeList.id
                });

                appData = result.data;
                renderApp();
                showStatus(result.message);
            } catch (error) {
                showStatus(error.message, true);
            }
        }

        async function resetAll() {
            const confirmed = confirm("Möchtest du wirklich alle Listen und Artikel zurücksetzen?");

            if (!confirmed) {
                return;
            }

            try {
                const result = await apiPost("reset", {});
                appData = result.data;
                renderApp();
                showStatus(result.message);
            } catch (error) {
                showStatus(error.message, true);
            }
        }

        async function addItem() {
            const activeList = getActiveList();

            if (!activeList) {
                showStatus("Keine aktive Liste gefunden.", true);
                return;
            }

            const name = itemNameInput.value.trim();
            const amount = itemAmountInput.value.trim();
            const category = itemCategoryInput.value;

            if (name === "") {
                showStatus("Bitte gib einen Artikelnamen ein.", true);
                itemNameInput.focus();
                return;
            }

            try {
                const result = await apiPost("add_item", {
                    listId: activeList.id,
                    name: name,
                    amount: amount,
                    category: category
                });

                appData = result.data;

                itemNameInput.value = "";
                itemAmountInput.value = "";
                itemCategoryInput.value = "Lebensmittel";
                itemNameInput.focus();

                renderApp();
                showStatus(result.message);
            } catch (error) {
                showStatus(error.message, true);
            }
        }

        async function toggleItemDone(itemId) {
            const activeList = getActiveList();

            if (!activeList) {
                return;
            }

            try {
                const result = await apiPost("toggle_item", {
                    listId: activeList.id,
                    itemId: itemId
                });

                appData = result.data;
                renderApp();
                showStatus(result.message);
            } catch (error) {
                showStatus(error.message, true);
            }
        }

        async function deleteItem(itemId) {
            const activeList = getActiveList();

            if (!activeList) {
                return;
            }

            try {
                const result = await apiPost("delete_item", {
                    listId: activeList.id,
                    itemId: itemId
                });

                appData = result.data;
                renderApp();
                showStatus(result.message);
            } catch (error) {
                showStatus(error.message, true);
            }
        }

        function renderApp() {
            renderListButtons();
            renderActiveList();
        }

        function renderListButtons() {
            listButtons.textContent = "";

            appData.lists.forEach(function(list) {
                const button = document.createElement("button");
                button.className = "list-button";
                button.type = "button";

                if (list.id === appData.activeListId) {
                    button.classList.add("active");
                }

                const nameSpan = document.createElement("span");
                nameSpan.textContent = list.name;

                const countSmall = document.createElement("small");
                countSmall.textContent = list.items.length + " Artikel";

                button.appendChild(nameSpan);
                button.appendChild(countSmall);

                button.addEventListener("click", function() {
                    selectList(list.id);
                });

                listButtons.appendChild(button);
            });

            deleteListButton.disabled = appData.lists.length <= 1;
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
            currentListInfo.textContent = activeList.items.length + " Artikel";

            activeList.items.forEach(function(item) {
                const listItem = document.createElement("li");
                listItem.className = "shopping-item";

                if (item.done) {
                    listItem.classList.add("done");
                }

                const itemMain = document.createElement("div");
                itemMain.className = "item-main";

                const itemName = document.createElement("span");
                itemName.className = "item-name";
                itemName.textContent = item.name;

                const itemMeta = document.createElement("div");
                itemMeta.className = "item-meta";

                const amountBadge = document.createElement("span");
                amountBadge.className = "badge";
                amountBadge.textContent = item.amount !== "" ? "Menge: " + item.amount : "Menge: keine Angabe";

                const categoryBadge = document.createElement("span");
                categoryBadge.className = "badge";
                categoryBadge.textContent = "Kategorie: " + item.category;

                const statusBadge = document.createElement("span");
                statusBadge.className = "badge";
                statusBadge.textContent = item.done ? "Status: erledigt" : "Status: offen";

                itemMeta.appendChild(amountBadge);
                itemMeta.appendChild(categoryBadge);
                itemMeta.appendChild(statusBadge);

                itemMain.appendChild(itemName);
                itemMain.appendChild(itemMeta);

                const itemActions = document.createElement("div");
                itemActions.className = "item-actions";

                const toggleButton = document.createElement("button");
                toggleButton.className = "toggle-btn";
                toggleButton.type = "button";
                toggleButton.textContent = item.done ? "Öffnen" : "Erledigt";
                toggleButton.addEventListener("click", function() {
                    toggleItemDone(item.id);
                });

                const deleteButton = document.createElement("button");
                deleteButton.className = "delete-btn";
                deleteButton.type = "button";
                deleteButton.textContent = "Löschen";
                deleteButton.addEventListener("click", function() {
                    deleteItem(item.id);
                });

                itemActions.appendChild(toggleButton);
                itemActions.appendChild(deleteButton);

                listItem.appendChild(itemMain);
                listItem.appendChild(itemActions);

                shoppingList.appendChild(listItem);
            });

            updateStats(activeList.items);
        }

        function updateStats(items) {
            const total = items.length;

            const done = items.filter(function(item) {
                return item.done === true;
            }).length;

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
            }, 3000);
        }

        listForm.addEventListener("submit", function(event) {
            event.preventDefault();
            createList();
        });

        itemForm.addEventListener("submit", function(event) {
            event.preventDefault();
            addItem();
        });

        deleteListButton.addEventListener("click", deleteActiveList);
        resetButton.addEventListener("click", resetAll);

        loadData();
    </script>

</body>
</html>