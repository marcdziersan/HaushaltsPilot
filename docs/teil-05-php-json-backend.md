# Teil 05 – PHP-JSON-Backend mit Sicherheitsgrundlagen

In Teil 04 lief die Anwendung noch vollständig im Browser. Die Listen wurden mit `localStorage` gespeichert.

In Teil 05 bekommt die Anwendung ein erstes Backend mit PHP.

Die Daten werden jetzt serverseitig in einer JSON-Datei gespeichert. Damit verlassen wir die reine Browser-Anwendung und nähern uns einer echten Webanwendung.

Gleichzeitig bauen wir von Anfang an einige Sicherheitsgrundlagen ein, damit später nicht alles umgebaut werden muss.

---

## Ziel dieses Tutorials

Am Ende dieses Teils kann die Anwendung:

* Listen vom Server laden
* neue Listen serverseitig speichern
* zwischen Listen wechseln
* Artikel serverseitig speichern
* Artikel als erledigt/offen markieren
* Artikel löschen
* Listen löschen
* alle Daten zurücksetzen
* Daten in einer JSON-Datei speichern
* schreibende Aktionen mit CSRF-Token absichern
* Eingaben serverseitig prüfen

---

## Warum jetzt PHP?

Bisher lagen alle Daten nur im Browser. Das hat klare Grenzen:

* Daten sind nur auf diesem Gerät vorhanden
* andere Geräte sehen die Daten nicht
* mehrere Benutzer können nicht gemeinsam arbeiten
* es gibt keine serverseitige Kontrolle
* spätere Logins wären damit nicht sinnvoll möglich

Mit PHP können wir Daten zentral auf dem Server speichern und kontrollieren.

---

## Warum noch JSON und keine Datenbank?

Teil 05 nutzt bewusst noch keine Datenbank.

JSON ist für diesen Zwischenschritt gut geeignet, weil man die Datenstruktur direkt sehen kann.

Dadurch versteht man besser:

* wie Frontend und Backend kommunizieren
* wie Daten vom Server geladen werden
* wie Daten serverseitig gespeichert werden
* wie JSON als Austauschformat funktioniert
* warum später eine Datenbank sinnvoll wird

In Teil 06 wird JSON dann durch eine Datenbank ersetzt. Dort nutzen wir PDO und Prepared Statements.

---

## Sicherheitsgrundlagen in Teil 05

Diese Version ist noch keine vollständige Produktiv-Anwendung, aber sie startet bewusst sauberer.

Enthalten sind:

* CSRF-Schutz für POST-Aktionen
* serverseitige Eingabevalidierung
* begrenzte Textlängen
* erlaubte Kategorienliste
* sichere Ausgabe im Frontend mit `textContent`
* PHP-Ausgabe mit `htmlspecialchars()`
* JSON-Schreiben mit Dateisperre
* keine direkte Verarbeitung unbekannter Actions
* keine Speicherung sensibler Daten

---

## Was ist CSRF?

CSRF bedeutet Cross-Site Request Forgery.

Dabei versucht eine fremde Webseite, im Namen eines angemeldeten Benutzers eine Aktion auszuführen.

Beispiel:

Ein Benutzer ist in einer Anwendung eingeloggt. Währenddessen öffnet er eine andere Webseite. Diese fremde Webseite versucht dann, unbemerkt eine Aktion an die Anwendung zu senden.

Deshalb bekommen schreibende Aktionen einen CSRF-Token.

Der Server erzeugt den Token.
Das Frontend sendet ihn bei POST-Anfragen mit.
Der Server prüft, ob der Token gültig ist.

---

## Was ist htmlspecialchars?

`htmlspecialchars()` wandelt gefährliche HTML-Zeichen in harmlose Textdarstellung um.

Beispiel:

```php
htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
```

Das schützt bei serverseitiger HTML-Ausgabe vor eingeschleustem HTML oder JavaScript.

In dieser Version rendert JavaScript die Daten zusätzlich mit `textContent` statt `innerHTML`.

Das ist wichtig, weil Benutzereingaben niemals ungeprüft als HTML ausgegeben werden sollten.

---

## Projektstand nach Teil 05

```txt
versions/
├── 01-simple-shopping-list/
├── 02-localstorage/
├── 03-categories-status/
├── 04-multiple-lists/
└── 05-json-backend/
    ├── index.php
    ├── api.php
    ├── includes/
    │   └── security.php
    └── data/
        └── .htaccess
```

Die Datei `lists.json` wird beim ersten Start automatisch im Ordner `data/` erstellt.

---

## Neue Architektur

In Teil 04 war alles in einer HTML-Datei.

In Teil 05 wird getrennt:

| Datei                   | Aufgabe                                      |
| ----------------------- | -------------------------------------------- |
| `index.php`             | Oberfläche und CSRF-Token                    |
| `api.php`               | API für Laden, Speichern, Löschen            |
| `includes/security.php` | Session, CSRF, JSON-Helfer, Validierung      |
| `data/lists.json`       | gespeicherte Listendaten                     |
| `data/.htaccess`        | Schutz vor direktem Zugriff auf JSON-Dateien |

---

## API-Aktionen

| Action            | Methode | Aufgabe                         |
| ----------------- | ------- | ------------------------------- |
| `load`            | GET     | Daten laden                     |
| `create_list`     | POST    | neue Liste erstellen            |
| `set_active_list` | POST    | aktive Liste wechseln           |
| `delete_list`     | POST    | aktive Liste löschen            |
| `add_item`        | POST    | Artikel hinzufügen              |
| `toggle_item`     | POST    | Artikel erledigt/offen schalten |
| `delete_item`     | POST    | Artikel löschen                 |
| `reset`           | POST    | alles zurücksetzen              |

Alle POST-Aktionen benötigen einen gültigen CSRF-Token.

---

## Testfälle

| Test                       | Erwartetes Ergebnis                      |
| -------------------------- | ---------------------------------------- |
| Seite öffnen               | Standardliste wird geladen               |
| Neue Liste erstellen       | Liste wird serverseitig gespeichert      |
| Seite neu laden            | Liste bleibt vorhanden                   |
| Artikel hinzufügen         | Artikel erscheint und bleibt gespeichert |
| Artikel erledigen          | Status wird gespeichert                  |
| Artikel löschen            | Artikel verschwindet dauerhaft           |
| Liste wechseln             | aktive Liste wird gespeichert            |
| Liste löschen              | Liste wird entfernt                      |
| Letzte Liste löschen       | wird verhindert                          |
| CSRF-Token entfernen       | POST-Aktion wird abgelehnt               |
| zu langen Namen senden     | Anfrage wird abgelehnt                   |
| ungültige Kategorie senden | Anfrage wird abgelehnt                   |

---

## Noch nicht enthalten

* Benutzerlogin
* mehrere Benutzer
* Familiengruppen
* Datenbank
* PDO
* Prepared Statements
* Rechteverwaltung
* persönliche Listen pro Benutzer
* Gemeinschaftslisten
* Nachrichten
* Kalender

Diese Funktionen folgen später.

---

## Nächster Schritt

In Teil 06 ersetzen wir die JSON-Datei durch eine Datenbank.

Dann nutzen wir:

* PDO
* Prepared Statements
* Tabellen für Listen
* Tabellen für Artikel
* erste saubere Datenbankstruktur

Damit wird die Anwendung robuster und besser für Login und Familienfunktionen vorbereitet.
