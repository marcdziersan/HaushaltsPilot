# Teil 05 – PHP-JSON-Backend

Teil 05 führt ein PHP-Backend ein. Die Daten werden nicht mehr im Browser gespeichert, sondern serverseitig in einer JSON-Datei verwaltet.

> **Rolle in der Reihe:** Dieser Teil ist der erste große Architekturwechsel der Reihe: Aus einer reinen Frontend-Anwendung wird eine Client-Server-Anwendung mit API, Backendvalidierung und serverseitiger Speicherung.

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 05 – PHP-JSON-Backend  
**Quelltext zu diesem Teil:** [versions/05-json-backend](../versions/05-json-backend/)

[← Teil 04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md) | [README / Übersicht](../README.md) | [Teil 06 – Konfigurierbare Speicherung →](teil-06-konfigurierbare-speicherung.md)

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [Version 01](../versions/01-simple-shopping-list/) | Artikel hinzufügen und löschen |
| 02 | [02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [Version 02](../versions/02-localstorage/) | Speicherung im Browser |
| 03 | [03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [Version 03](../versions/03-categories-status/) | strukturierte Artikeldaten |
| 04 | [04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md) | [Version 04](../versions/04-multiple-lists/) | mehrere getrennte Listen |
| 05 | **[05 – PHP-JSON-Backend](teil-05-php-json-backend.md)** | **[Version 05](../versions/05-json-backend/)** | serverseitige JSON-Speicherung |
| 06 | [06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [Version 06](../versions/06-configurable-storage/) | JSON, SQLite oder MySQL/MariaDB |
| 07 | [07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md) | [Version 07](../versions/07-login-roles/) | Registrierung, Login, Sessions und Rollen |
| 08 | [08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md) | [Version 08](../versions/08-personal-shared-lists/) | private und gemeinsame Listenrechte |
| 09 | [09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [Version 09](../versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | [10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [Version 10](../versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |

<!-- tutorial-nav:end -->

---

## Ziel dieses Kapitels

Am Ende dieses Teils kann die Anwendung:

- Daten serverseitig in `data/lists.json` speichern
- eine einfache PHP-API bereitstellen
- Frontend und Backend über `fetch()` verbinden
- CSRF-Schutz für schreibende Aktionen einführen
- Ausgaben serverseitig mit `htmlspecialchars()` absichern
- Backendaktionen für Listen und Artikel strukturieren

---

## Warum, weshalb, wieso dieser Schritt?

Browser-Speicherung ist praktisch, aber fachlich begrenzt. Sobald mehrere Geräte, zentrale Datenhaltung oder spätere Benutzerkonten gebraucht werden, muss ein Server ins Spiel kommen.
JSON als erste serverseitige Speicherung ist absichtlich gewählt: Die Datenstruktur bleibt sichtbar und verständlich, ohne sofort SQL, Tabellen und Joins erklären zu müssen.
Dieser Schritt zeigt den Kern vieler Webanwendungen: Das Frontend bedient die Oberfläche, das Backend entscheidet, welche Daten gelesen oder verändert werden dürfen.

Der didaktische Gedanke der Reihe bleibt dabei gleich: Jede neue Funktion löst ein konkretes Problem des vorherigen Standes. Dadurch entsteht keine Sammlung isolierter Codebeispiele, sondern eine fortlaufende Anwendung mit wachsender fachlicher und technischer Tiefe.

---

## Ausgangspunkt aus dem vorherigen Teil

- Speicherung liegt nun serverseitig statt im Browser.
- Es gibt eine API-Schicht zwischen Oberfläche und Daten.
- Erste Sicherheitsbausteine wie CSRF und Escaping sind vorhanden.

Dieser Ausgangspunkt bestimmt, warum die Erweiterung in diesem Kapitel sinnvoll ist und welche Grenzen weiterhin bewusst stehen bleiben.

---

## Fachliche Grundlagen

- **API-Endpunkt**: `api.php` nimmt Aktionen entgegen und gibt JSON-Antworten zurück.
- **fetch()**: Das Frontend ruft die API asynchron auf, ohne die Seite komplett neu zu laden.
- **JSON-Datei**: Der Server speichert den aktuellen Datenbestand strukturiert in einer Datei.
- **CSRF-Token**: Schreibende Aktionen werden gegen fremde Formular-/Request-Auslösung abgesichert.
- **Serverseitige Validierung**: Das Backend prüft Eingaben erneut, statt dem Browser blind zu vertrauen.

---

## Technische Umsetzung im Überblick

- `index.php` liefert die Oberfläche aus und stellt den CSRF-Token bereit.
- `api.php` verarbeitet Aktionen wie `load`, `create_list`, `set_active_list`, `delete_list`, `add_item`, `toggle_item`, `delete_item` und `reset`.
- `includes/security.php` bündelt erste Sicherheitsfunktionen.
- `data/.htaccess` schützt die JSON-Daten im Apache-Umfeld vor direktem Zugriff.
- `data/lists.json` enthält den serverseitigen Demo-Datenbestand.

### Projektstand nach Teil 05

```txt
versions/
└── 05-json-backend/
    ├── api.php
    ├── index.php
    ├── data/
    │   ├── .htaccess
    │   └── lists.json
    └── includes/
        └── security.php
```

---

## Architekturentscheidung

Die Anwendung trennt nun Oberfläche und Datenzugriff. Das Frontend fragt Daten an, das Backend liefert strukturierte Antworten.
JSON ist hier eine didaktische Brücke: Es verhält sich einfacher als eine Datenbank, zwingt aber bereits zu serverseitiger Logik.
Die API-Aktionen bilden die fachlichen Operationen ab. Das ist später leichter auf Datenbankmethoden übertragbar als direkte Dateizugriffe aus der Oberfläche.

**Wichtig:** Die Reihe bleibt bewusst ohne Framework-Overkill. Ziel ist nicht, ein modernes Framework zu umgehen, sondern zuerst die Grundmechanik einer Webanwendung zu verstehen: Datenmodell, Oberfläche, Anfrage, Antwort, Speicherung, Validierung und Rechteprüfung.

---

## Pro und Kontra

### Vorteile

- erste echte Client-Server-Architektur
- Daten liegen zentral auf dem Server
- keine Datenbankinstallation nötig
- JSON-Datei bleibt für Lernende lesbar
- Sicherheitskonzepte können früh eingeführt werden

### Nachteile

- JSON-Dateien skalieren schlechter als Datenbanken
- gleichzeitige Schreibzugriffe müssen sauber behandelt werden
- noch kein Benutzerlogin
- keine relationalen Abfragen
- für produktive Mehrbenutzersysteme nur begrenzt geeignet

Diese Nachteile sind in diesem Kapitel nicht automatisch Fehler. Viele davon sind bewusste Zwischenstände, die in späteren Teilen gezielt aufgelöst werden.

---

## Sicherheits- und Qualitätsaspekte

- Schreibende API-Aktionen benötigen einen CSRF-Token.
- Benutzereingaben werden validiert und bei HTML-Ausgaben escaped.
- Die Datendatei liegt in einem `data`-Ordner mit `.htaccess`-Schutz. Das ist hilfreich, ersetzt aber keine saubere Serverkonfiguration.
- Da noch kein Login existiert, schützt CSRF nicht vor unberechtigter Nutzung durch legitime Besucher. Rechteverwaltung kommt später.

---

## Typische Fehlerquellen

- Dateien ohne Sperrmechanismus parallel beschreiben
- API-Fehler nicht als JSON zurückgeben
- CSRF nur im Formular, aber nicht bei `fetch()` berücksichtigen
- Datenordner öffentlich auslieferbar lassen
- Frontendvalidierung mit Sicherheit verwechseln

---

## Testcheckliste

| Test | Erwartetes Ergebnis |
| --- | --- |
| Seite laden | Daten werden per API geladen |
| Liste anlegen | Liste erscheint und wird in JSON gespeichert |
| Artikel hinzufügen | Artikel bleibt nach Reload erhalten |
| Artikel erledigen | Status wird serverseitig gespeichert |
| Request ohne CSRF-Token senden | schreibende Aktion wird abgelehnt |
| JSON-Datei direkt im Browser aufrufen | Zugriff sollte durch Serverkonfiguration verhindert werden |

---

## Was wurde gegenüber dem vorherigen Stand verbessert?

- Speicherung liegt nun serverseitig statt im Browser.
- Es gibt eine API-Schicht zwischen Oberfläche und Daten.
- Erste Sicherheitsbausteine wie CSRF und Escaping sind vorhanden.

---

## Grenzen dieser Version

- noch kein austauschbarer Speicheradapter
- keine SQLite- oder MySQL-Unterstützung
- kein Login und keine Benutzerrollen
- JSON-Datei ist für große Datenmengen nicht ideal

---

## Ausblick auf den nächsten Teil

Teil 06 abstrahiert die Speicherung. Danach kann die Anwendung mit JSON, SQLite oder MySQL/MariaDB betrieben werden.

---

## Einordnung für die Praxis

Dieser Teil ist ein Lernschritt, kein fertiges Produkt. Genau darin liegt der Wert der Reihe: Jeder Stand ist klein genug, um verstanden zu werden, aber konkret genug, um später erweitert zu werden.

In einem professionellen Umfeld würde man zusätzlich auf saubere Release-Stände, automatisierte Tests, Konfigurationsbeispiele, Datenmigrationen, Deployment-Dokumentation und eine klare Trennung zwischen Demo- und Produktivbetrieb achten. Die Tutorialreihe führt diese Themen schrittweise ein, ohne den Einstieg unnötig zu überladen.

---

## Navigation

[← Teil 04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md) | [README / Übersicht](../README.md) | [Teil 06 – Konfigurierbare Speicherung →](teil-06-konfigurierbare-speicherung.md)
