# Teil 07 – Login, Benutzer und Rollen

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 07 – Login, Benutzer und Rollen  
**Quelltext zu diesem Teil:** [versions/07-login-roles](../versions/07-login-roles/)

[← Teil 06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [README / Übersicht](../README.md) | [Teil 08 – Persönliche und gemeinsame Listen →](teil-08-persoenliche-und-gemeinsame-listen.md)

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [Version 01](../versions/01-simple-shopping-list/) | Artikel hinzufügen/löschen |
| 02 | [02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [Version 02](../versions/02-localstorage/) | Speicherung im Browser |
| 03 | [03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [Version 03](../versions/03-categories-status/) | strukturierte Artikeldaten |
| 04 | [04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md) | [Version 04](../versions/04-multiple-lists/) | mehrere getrennte Listen |
| 05 | [05 – PHP-JSON-Backend](teil-05-php-json-backend.md) | [Version 05](../versions/05-json-backend/) | serverseitige JSON-Speicherung |
| 06 | [06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [Version 06](../versions/06-configurable-storage/) | JSON, SQLite oder MySQL/MariaDB |
| 07 | **[07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md)** | **[Version 07](../versions/07-login-roles/)** | Registrierung, Login, Sessions, Rollen |
| 08 | [08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md) | [Version 08](../versions/08-personal-shared-lists/) | private und gemeinsame Listenrechte |
| 09 | [09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [Version 09](../versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | [10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [Version 10](../versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |

<!-- tutorial-nav:end -->
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


### Speicher löschen und neu erstellen

Der Installer besitzt jetzt standardmäßig die Option:

```txt
Vorhandenen Speicher löschen und neu erstellen
```

Diese Option ist bewusst vorausgewählt. Für diese Tutorial-Reihe ist das die saubere Standardmethode, weil sich das Datenmodell zwischen den Teilen weiterentwickelt.

Je nach Speicherart passiert dabei Folgendes:

| Speicher | Verhalten beim Reset |
|---|---|
| JSON | `data/lists.json` wird gelöscht und neu erstellt |
| SQLite | `data/app.sqlite` wird gelöscht und neu erstellt |
| MySQL/MariaDB | Die gewählte Datenbank wird mit `DROP DATABASE IF EXISTS` gelöscht und danach neu erstellt |

Wichtig: Bei MySQL/MariaDB darf die Installer-Datenbankkennung nur Buchstaben, Zahlen und Unterstriche enthalten. Der Datenbankname wird geprüft und zusätzlich als Identifier gequotet.

Wenn bereits eine `config.php` existiert, muss zusätzlich bewusst bestätigt werden, dass die bestehende Installation überschrieben werden soll.

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
