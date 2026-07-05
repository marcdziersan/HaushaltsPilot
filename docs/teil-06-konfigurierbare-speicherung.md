# Teil 06 – Konfigurierbare Speicherung

Teil 06 führt eine austauschbare Speicherschicht ein. Die Anwendung kann denselben fachlichen Code mit JSON-Datei, SQLite oder MySQL/MariaDB betreiben.

> **Rolle in der Reihe:** Dieser Teil professionalisiert die Architektur. Speicherung wird nicht mehr direkt an eine konkrete Technik gebunden, sondern über eine zentrale Storage-Schicht gekapselt.

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 06 – Konfigurierbare Speicherung  
**Quelltext zu diesem Teil:** [versions/06-configurable-storage](../versions/06-configurable-storage/)

[← Teil 05 – PHP-JSON-Backend](teil-05-php-json-backend.md) | [README / Übersicht](../README.md) | [Teil 07 – Login, Benutzer und Rollen →](teil-07-login-benutzer-rollen.md)

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [Version 01](../versions/01-simple-shopping-list/) | Artikel hinzufügen und löschen |
| 02 | [02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [Version 02](../versions/02-localstorage/) | Speicherung im Browser |
| 03 | [03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [Version 03](../versions/03-categories-status/) | strukturierte Artikeldaten |
| 04 | [04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md) | [Version 04](../versions/04-multiple-lists/) | mehrere getrennte Listen |
| 05 | [05 – PHP-JSON-Backend](teil-05-php-json-backend.md) | [Version 05](../versions/05-json-backend/) | serverseitige JSON-Speicherung |
| 06 | **[06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md)** | **[Version 06](../versions/06-configurable-storage/)** | JSON, SQLite oder MySQL/MariaDB |
| 07 | [07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md) | [Version 07](../versions/07-login-roles/) | Registrierung, Login, Sessions und Rollen |
| 08 | [08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md) | [Version 08](../versions/08-personal-shared-lists/) | private und gemeinsame Listenrechte |
| 09 | [09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [Version 09](../versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | [10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [Version 10](../versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |
| 11 | [11 – Todos](teil-11-todos.md) | [Version 11](../versions/11-todos/) | persönliche und gemeinsame Aufgaben |

<!-- tutorial-nav:end -->

---

## Ziel dieses Kapitels

Am Ende dieses Teils kann die Anwendung:

- Speicherung konfigurierbar machen
- JSON, SQLite und MySQL/MariaDB als Speicheroptionen anbieten
- einen Installer für die Ersteinrichtung bereitstellen
- Datenbanktabellen automatisch anlegen
- API-Code von konkreten Speichermechanismen entkoppeln
- bestehende Sicherheitsgrundlagen beibehalten

---

## Warum, weshalb, wieso dieser Schritt?

Teil 05 war ein guter Einstieg, aber eine direkt verwendete JSON-Datei bindet die Anwendung zu stark an eine konkrete Speichertechnik.
Professioneller Code trennt fachliche Aktionen von Infrastruktur. Ob Daten aus JSON, SQLite oder MySQL kommen, sollte nicht überall im Projekt entschieden werden.
Eine austauschbare Speicherschicht zeigt ein wichtiges Architekturprinzip: Änderungen an der Infrastruktur sollen möglichst wenig Auswirkungen auf Oberfläche und Fachlogik haben.

Der didaktische Gedanke der Reihe bleibt dabei gleich: Jede neue Funktion löst ein konkretes Problem des vorherigen Standes. Dadurch entsteht keine Sammlung isolierter Codebeispiele, sondern eine fortlaufende Anwendung mit wachsender fachlicher und technischer Tiefe.

---

## Ausgangspunkt aus dem vorherigen Teil

- Direkte JSON-Abhängigkeit wurde entfernt.
- Die Anwendung besitzt nun eine konfigurierbare Persistenzschicht.
- Ein Installer unterstützt die Ersteinrichtung.
- SQLite und MySQL/MariaDB bringen das Projekt näher an reale Webanwendungen.

Dieser Ausgangspunkt bestimmt, warum die Erweiterung in diesem Kapitel sinnvoll ist und welche Grenzen weiterhin bewusst stehen bleiben.

---

## Fachliche Grundlagen

- **Storage-Schicht**: Eine zentrale Datei kapselt Lesen, Schreiben und Initialisieren der Daten.
- **Konfiguration**: `config.php` bestimmt, welche Speicherart verwendet wird.
- **Installer**: `installer.php` bereitet Dateien oder Datenbanktabellen für den ersten Start vor.
- **PDO**: SQLite und MySQL/MariaDB werden über eine einheitliche Datenbankschnittstelle angesprochen.
- **Transaktion**: Mehrere Datenbankoperationen können als zusammengehöriger Vorgang behandelt werden.

---

## Technische Umsetzung im Überblick

- `includes/bootstrap.php` lädt Konfiguration und Grundfunktionen.
- `includes/storage.php` enthält die Speicherlogik für JSON, SQLite und MySQL/MariaDB.
- `installer.php` richtet die gewählte Speicherart ein.
- `config.php` enthält Speicherart, Pfade, Datenbankzugang und App-Key.
- `api.php` arbeitet weiter mit fachlichen Aktionen, muss aber nicht mehr selbst wissen, ob JSON oder SQL verwendet wird.

### Projektstand nach Teil 06

```txt
versions/
└── 06-configurable-storage/
    ├── api.php
    ├── config.php
    ├── index.php
    ├── installer.php
    ├── data/
    │   └── .htaccess
    └── includes/
        ├── bootstrap.php
        ├── security.php
        └── storage.php
```

---

## Architekturentscheidung

Die Speicherschicht ist der zentrale Architekturfortschritt dieses Kapitels.
Statt direkte Dateizugriffe in der API zu verteilen, gibt es eine Stelle, an der Persistenz behandelt wird.
Das verbessert Wartbarkeit und Erweiterbarkeit. Später können Benutzer, Rollen und Haushalte auf derselben Grundlage ergänzt werden.

**Wichtig:** Die Reihe bleibt bewusst ohne Framework-Overkill. Ziel ist nicht, ein modernes Framework zu umgehen, sondern zuerst die Grundmechanik einer Webanwendung zu verstehen: Datenmodell, Oberfläche, Anfrage, Antwort, Speicherung, Validierung und Rechteprüfung.

---

## Pro und Kontra

### Vorteile

- deutlich professionellere Projektstruktur
- Speichertechnik kann gewechselt werden
- SQLite eignet sich für lokale Demos und kleine Installationen
- MySQL/MariaDB bereitet produktionsnähere Umgebungen vor
- Installer senkt die Einstiegshürde

### Nachteile

- mehr Dateien und höhere Einstiegskomplexität
- Konfiguration kann falsch gesetzt werden
- mehr Fehlerquellen bei Datenbankzugängen
- Storage-Abstraktion muss sauber gepflegt werden
- für absolute Anfänger schwerer als Teil 05

Diese Nachteile sind in diesem Kapitel nicht automatisch Fehler. Viele davon sind bewusste Zwischenstände, die in späteren Teilen gezielt aufgelöst werden.

---

## Sicherheits- und Qualitätsaspekte

- CSRF-Schutz und serverseitige Validierung bleiben erhalten.
- Für SQL-Speicherung werden PDO Prepared Statements genutzt, um SQL-Injection zu vermeiden.
- Konfigurationsdateien dürfen in echten Projekten keine öffentlichen Geheimnisse enthalten. Für eine Veröffentlichung sollte zusätzlich ein `config.example.php`-Muster genutzt werden.
- Der Installer sollte in Produktivumgebungen nach der Einrichtung entfernt oder geschützt werden.

---

## Typische Fehlerquellen

- Speicherart in `config.php` ändern, ohne den Installer auszuführen
- MySQL-Zugangsdaten falsch setzen
- Installer öffentlich online lassen
- API-Code wieder direkt an SQL oder JSON koppeln
- Tabellenstruktur und JSON-Struktur auseinanderlaufen lassen

---

## Testcheckliste

| Test | Erwartetes Ergebnis |
| --- | --- |
| Installer mit JSON ausführen | JSON-Datenbestand wird angelegt |
| Installer mit SQLite ausführen | SQLite-Datei und Tabellen werden erstellt |
| Installer mit MySQL ausführen | Tabellen werden in der Datenbank erstellt |
| Liste anlegen | Daten werden in der gewählten Speicherart gespeichert |
| Speicherart wechseln und neu installieren | Anwendung startet mit passender Datenbasis |
| API ohne CSRF testen | schreibende Aktionen werden abgelehnt |

---

## Was wurde gegenüber dem vorherigen Stand verbessert?

- Direkte JSON-Abhängigkeit wurde entfernt.
- Die Anwendung besitzt nun eine konfigurierbare Persistenzschicht.
- Ein Installer unterstützt die Ersteinrichtung.
- SQLite und MySQL/MariaDB bringen das Projekt näher an reale Webanwendungen.

---

## Grenzen dieser Version

- noch kein Benutzerlogin
- keine Rollen oder Rechte
- Konfigurationsmanagement ist noch einfach gehalten
- keine automatisierten Migrationen zwischen Versionen

---

## Ausblick auf den nächsten Teil

Teil 07 ergänzt Registrierung, Login, Sessions und Rollen. Dadurch entstehen erstmals unterschiedliche Berechtigungen für Admins und Nutzer.

---

## Einordnung für die Praxis

Dieser Teil ist ein Lernschritt, kein fertiges Produkt. Genau darin liegt der Wert der Reihe: Jeder Stand ist klein genug, um verstanden zu werden, aber konkret genug, um später erweitert zu werden.

In einem professionellen Umfeld würde man zusätzlich auf saubere Release-Stände, automatisierte Tests, Konfigurationsbeispiele, Datenmigrationen, Deployment-Dokumentation und eine klare Trennung zwischen Demo- und Produktivbetrieb achten. Die Tutorialreihe führt diese Themen schrittweise ein, ohne den Einstieg unnötig zu überladen.

---

## Navigation

[← Teil 05 – PHP-JSON-Backend](teil-05-php-json-backend.md) | [README / Übersicht](../README.md) | [Teil 07 – Login, Benutzer und Rollen →](teil-07-login-benutzer-rollen.md)
