# Teil 06 – Konfigurierbare Speicherung mit Installer

In Teil 05 wurde die Anwendung erstmals serverseitig gespeichert. Die Daten lagen in einer JSON-Datei.

In Teil 06 wird die Speicherung austauschbar. Die API bleibt für das Frontend gleich, aber im Backend kann gewählt werden, wo die Daten gespeichert werden:

- JSON-Datei
- SQLite über PDO
- MySQL/MariaDB über PDO

Dafür bekommt die Anwendung einen Installer.

---

## Ziel dieses Tutorials

Am Ende dieses Teils kann die Anwendung:

- beim ersten Start einen Installer anzeigen
- JSON, SQLite oder MySQL/MariaDB als Speicher auswählen
- eine `config.php` erzeugen
- JSON-Dateien im Ordner `data/` anlegen
- SQLite-Datenbanken im Ordner `data/` anlegen
- MySQL/MariaDB-Datenbank und Tabellen automatisch erzeugen
- dieselbe API unabhängig vom Speicher verwenden
- weiterhin CSRF, Validierung und sichere Ausgabe nutzen

---

## Warum eine austauschbare Speicher-Schicht?

Bis Teil 05 war die API direkt an JSON gebunden. Das ist für den Einstieg gut, aber später unpraktisch.

Ab Teil 06 gibt es eine Speicher-Schicht.

Das Frontend spricht weiter mit `api.php`.

Die API spricht mit einem Speicher-Adapter.

Der Adapter entscheidet, ob Daten aus JSON, SQLite oder MySQL/MariaDB kommen.

Dadurch muss das Frontend nicht umgebaut werden, wenn die Speicherung wechselt.

---

## Projektstruktur

```txt
versions/
└── 06-configurable-storage/
    ├── index.php
    ├── installer.php
    ├── api.php
    ├── config.php              # wird vom Installer erzeugt
    ├── includes/
    │   ├── bootstrap.php
    │   ├── security.php
    │   └── storage.php
    └── data/
        ├── .htaccess
        ├── lists.json           # bei JSON-Auswahl
        └── app.sqlite           # bei SQLite-Auswahl
```

---

## Speicherarten

### JSON

JSON speichert die Daten in:

```txt
data/lists.json
```

Vorteile:

- sehr einfach
- gut lesbar
- ideal zum Lernen
- kein Datenbankserver nötig

Nachteile:

- nicht ideal für viele gleichzeitige Benutzer
- keine echten SQL-Abfragen
- nicht optimal für spätere Rechteverwaltung

---

### SQLite über PDO

SQLite speichert die Daten in:

```txt
data/app.sqlite
```

Vorteile:

- echte Datenbank
- keine Serverkonfiguration nötig
- gut für lokale Demos
- perfekt für GitHub-Projekte
- PDO und Prepared Statements können direkt genutzt werden

Nachteile:

- für stark parallele Webanwendungen begrenzt
- nicht immer auf jedem Webhosting ideal

---

### MySQL/MariaDB über PDO

MySQL/MariaDB nutzt einen Datenbankserver.

Vorteile:

- geeignet für Webhosting
- besser für mehrere Benutzer
- saubere Grundlage für Login, Familien, Nachrichten und Kalender
- vorbereitet für produktivere Strukturen

Nachteile:

- Zugangsdaten nötig
- Datenbankserver nötig
- der Datenbanknutzer benötigt Rechte zum Erstellen der Datenbank oder Tabellen

---

## Sicherheitsgrundlagen bleiben erhalten

Teil 06 übernimmt die Sicherheitsbasis aus Teil 05:

- Session-Cookie mit `HttpOnly`
- CSRF-Token für POST-Aktionen
- serverseitige Validierung
- feste Kategorienliste
- Längenbegrenzung für Eingaben
- sichere Ausgabe mit `htmlspecialchars()`
- sichere DOM-Ausgabe mit `textContent`
- kein Rendering von Benutzerdaten über `innerHTML`
- Datenordner per `.htaccess` geschützt
- PDO mit Exceptions
- PDO mit deaktivierten emulierten Prepared Statements

---

## Datenbankschema

Bei SQLite und MySQL/MariaDB werden drei Tabellen angelegt:

```txt
app_settings
lists
items
```

### app_settings

Speichert einfache Anwendungseinstellungen, aktuell die aktive Liste.

### lists

Speichert die vorhandenen Listen.

### items

Speichert die Artikel einer Liste.

---

## Warum noch kein Benutzerlogin?

Teil 06 ist bewusst nur der Speicher-Umbau.

Login kommt im nächsten Teil.

Der Grund: Login benötigt eine stabile Datenbasis. Deshalb wird zuerst geklärt, ob die Anwendung mit JSON, SQLite oder MySQL/MariaDB arbeitet.

Danach können Benutzer sauber ergänzt werden.

---

## Testfälle

| Test | Erwartetes Ergebnis |
|---|---|
| Anwendung ohne config.php öffnen | Installer wird angezeigt |
| JSON auswählen | `data/lists.json` wird erzeugt |
| SQLite auswählen | `data/app.sqlite` wird erzeugt |
| MySQL auswählen | Datenbank und Tabellen werden erzeugt |
| Neue Liste erstellen | Liste wird im gewählten Speicher gespeichert |
| Artikel hinzufügen | Artikel bleibt nach Reload erhalten |
| Speicher im Installer neu wählen | Konfiguration wird bewusst überschrieben |
| POST ohne CSRF senden | Anfrage wird abgelehnt |
| Ungültige Kategorie senden | Anfrage wird abgelehnt |
| zu lange Eingabe senden | Anfrage wird abgelehnt |

---

## Nächster Schritt

In Teil 07 bauen wir Login und Benutzerkonten.

Dann kommen hinzu:

- Benutzertabelle
- Registrierung
- Login
- Logout
- Passwort-Hashing
- Sessions
- persönliche Listen pro Benutzer

Für SQLite und MySQL/MariaDB können wir direkt mit PDO und Prepared Statements weiterarbeiten.
