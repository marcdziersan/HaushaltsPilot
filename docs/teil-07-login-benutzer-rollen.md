# Teil 07 – Login, Benutzer und Rollen

In Teil 06 konnte HaushaltsPilot bereits frei zwischen JSON, SQLite und MySQL/MariaDB über PDO konfiguriert werden.

In Teil 07 kommt die Benutzerbasis dazu:

- Registrierung
- Login
- Logout
- Sessions
- Passwort-Hashing
- Rollenverwaltung
- Benutzerverwaltung für Admins
- private Listen
- Gemeinschaftslisten
- vorbereitete Rechtebasis für spätere Module

Die Anwendung bleibt beim Speicherkonzept aus Teil 06: JSON, SQLite oder MySQL/MariaDB können weiterhin über den Installer gewählt werden.

---

## Rollenmodell

Es gibt zwei Rollen:

| Rolle | Bedeutung |
|---|---|
| `admin` | Kann Benutzer, Rollen, Status und alle Listen verwalten |
| `user` | Kann eigene Listen und Gemeinschaftslisten sehen und verwalten |

Die Rechteprüfung findet serverseitig in `api.php` statt. Das Frontend blendet Funktionen nur optisch ein oder aus, ist aber nicht die Sicherheitsinstanz.

---

## Datenmodell

Teil 07 erweitert die Datenstruktur aus Teil 06.

### Benutzer

```json
{
  "id": "user_xxx",
  "username": "admin",
  "displayName": "Administrator",
  "passwordHash": "$2y$...",
  "role": "admin",
  "active": true,
  "createdAt": "2026-07-04 20:00:00"
}
```

Passwörter werden nicht im Klartext gespeichert, sondern mit `password_hash()`.

### Listen

```json
{
  "id": "list_xxx",
  "ownerId": "user_xxx",
  "name": "Einkauf",
  "isShared": false,
  "items": [],
  "createdAt": "2026-07-04 20:00:00"
}
```

Eine Liste gehört einem Benutzer über `ownerId`.

Mit `isShared: true` wird daraus eine Gemeinschaftsliste.

### Aktive Listen pro Benutzer

```json
{
  "activeLists": {
    "user_xxx": "list_xxx"
  }
}
```

Dadurch kann jeder Benutzer seine eigene zuletzt aktive Liste behalten.

---

## Sicherheitsgrundlagen

Teil 07 enthält weiterhin die Sicherheitsbasis aus Teil 05 und 06:

- CSRF-Token für POST-Aktionen
- Session-Cookie mit `HttpOnly`, `SameSite=Lax` und optional `Secure`
- `session_regenerate_id(true)` nach Login und Registrierung
- Passwort-Hashing mit `password_hash()`
- Passwortprüfung mit `password_verify()`
- serverseitige Validierung
- Rollenprüfung im Backend
- sichere Ausgabe mit `htmlspecialchars()` in PHP
- sichere DOM-Ausgabe mit `textContent` in JavaScript
- keine Ausgabe von Benutzerdaten über `innerHTML`
- PDO mit Prepared Statements bei SQLite und MySQL/MariaDB
- deaktivierte emulierte Prepared Statements über `PDO::ATTR_EMULATE_PREPARES = false`

---

## Installer

Der Installer fragt jetzt zusätzlich nach einem Admin-Konto:

- Admin-Benutzername
- Anzeigename
- Admin-Passwort
- Registrierung erlauben ja/nein

Beim Installieren wird automatisch ein Admin-Benutzer erzeugt.

Neue Benutzer über die Registrierung erhalten automatisch die Rolle `user`.

---

## Admin-Funktionen

Admins können in der Oberfläche:

- Benutzer sehen
- Benutzer zu Admin machen
- Admins zu Nutzern machen
- Benutzer deaktivieren
- Benutzer aktivieren
- Benutzer löschen

Schutzregeln:

- Der letzte aktive Admin darf nicht deaktiviert werden.
- Der letzte aktive Admin darf nicht gelöscht werden.
- Ein Admin kann sich nicht selbst deaktivieren.
- Ein Admin kann sich nicht selbst löschen.
- Ein Admin kann sich nicht selbst die Admin-Rolle entziehen.

---

## Nutzerrechte

Normale Nutzer können:

- eigene Listen sehen
- eigene Listen verwalten
- Gemeinschaftslisten sehen
- Gemeinschaftslisten verwalten
- Artikel in erlaubten Listen hinzufügen
- Artikel in erlaubten Listen erledigen/öffnen
- Artikel in erlaubten Listen löschen

Private Listen anderer Nutzer bleiben unsichtbar.

---

## Vorbereitung für kommende Module

Die Rechtebasis ist bewusst allgemein gehalten.

Die späteren Module können dieselbe Logik nutzen:

- private Todos: `ownerId`
- Gemeinschafts-Todos: `scope = shared`
- persönliche Nachrichten: Sender und Empfänger
- Einzelchat: Thread mit zwei Mitgliedern
- Familienchat: gemeinsamer Thread
- persönlicher Kalender: `ownerId`
- Familienkalender: `scope = shared`

Für SQLite und MySQL/MariaDB werden bereits vorbereitende Tabellen angelegt:

- `message_threads`
- `thread_members`
- `messages`
- `todos`
- `calendar_events`

Diese Tabellen werden in Teil 07 noch nicht aktiv genutzt, aber die Struktur ist vorbereitet.

---

## Dateien

```txt
versions/07-login-roles/
├── index.php
├── login.php
├── auth.php
├── api.php
├── installer.php
├── includes/
│   ├── bootstrap.php
│   ├── security.php
│   └── storage.php
└── data/
    └── .htaccess
```

---

## Testfälle

| Test | Erwartetes Ergebnis |
|---|---|
| Installer ausführen | Admin-Konto wird erstellt |
| Login mit Admin | Anwendung öffnet sich |
| Registrierung | Neuer Nutzer mit Rolle `user` entsteht |
| Nutzer legt private Liste an | Nur Nutzer und Admin sehen sie |
| Nutzer legt Gemeinschaftsliste an | Alle Nutzer sehen sie |
| Nutzer bearbeitet Gemeinschaftsliste | Änderung wird gespeichert |
| Admin öffnet Anwendung | Admin sieht alle Listen |
| Admin ändert Rolle | Rolle wird gespeichert |
| Admin deaktiviert Nutzer | Nutzer kann sich nicht mehr anmelden |
| Letzten Admin deaktivieren | Wird verhindert |
| CSRF-Token entfernen | POST-Aktion wird abgelehnt |

---

## Nächster Schritt

Teil 08 kann jetzt sinnvoll Familien- oder Haushaltsgruppen ergänzen.

Dann gehören Gemeinschaftslisten nicht mehr global allen Nutzern, sondern gezielt einer Familie oder einem Haushalt.
