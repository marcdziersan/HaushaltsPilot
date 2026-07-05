# Teil 11 – Todos

Teil 11 ergänzt HaushaltsPilot um ein eigenständiges Aufgabenmodul. Neben Einkaufs- und Haushaltslisten gibt es jetzt persönliche Todos und gemeinsame Familienaufgaben.

> **Rolle in der Reihe:** Nach Benutzern, Rollen, Haushalten und Gemeinschaftslisten entsteht das nächste echte Hauptmodul. Aufgaben werden nicht in Einkaufslisten versteckt, sondern als eigene fachliche Einheit modelliert.

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 11 – Todos  
**Quelltext zu diesem Teil:** [versions/11-todos](../versions/11-todos/)

[← Teil 10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [README / Übersicht](../README.md) | [Teil 12 – Personal Messages →](#ausblick-auf-den-naechsten-teil)

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [Version 01](../versions/01-simple-shopping-list/) | Artikel hinzufügen und löschen |
| 02 | [02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [Version 02](../versions/02-localstorage/) | Speicherung im Browser |
| 03 | [03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [Version 03](../versions/03-categories-status/) | strukturierte Artikeldaten |
| 04 | [04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md) | [Version 04](../versions/04-multiple-lists/) | mehrere getrennte Listen |
| 05 | [05 – PHP-JSON-Backend](teil-05-php-json-backend.md) | [Version 05](../versions/05-json-backend/) | serverseitige JSON-Speicherung |
| 06 | [06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [Version 06](../versions/06-configurable-storage/) | JSON, SQLite oder MySQL/MariaDB |
| 07 | [07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md) | [Version 07](../versions/07-login-roles/) | Registrierung, Login, Sessions und Rollen |
| 08 | [08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md) | [Version 08](../versions/08-personal-shared-lists/) | private und gemeinsame Listenrechte |
| 09 | [09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [Version 09](../versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | [10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [Version 10](../versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |
| 11 | **[11 – Todos](teil-11-todos.md)** | **[Version 11](../versions/11-todos/)** | persönliche und gemeinsame Aufgaben |

<!-- tutorial-nav:end -->

---

## Ziel dieses Kapitels

Am Ende dieses Teils kann die Anwendung:

- persönliche Aufgaben erstellen
- Familienaufgaben erstellen
- Aufgaben nach privat und Haushalt trennen
- Aufgaben als offen oder erledigt markieren
- Aufgaben löschen
- optionale Fälligkeitsdaten speichern
- Aufgaben im JSON-, SQLite- und MySQL-Speicher berücksichtigen
- Admins eine Todo-Übersicht anzeigen
- Haushaltsgrenzen auch für Aufgaben einhalten

---

## Warum, weshalb, wieso dieser Schritt?

Eine Haushaltsanwendung besteht nicht nur aus Einkaufslisten. In echten Haushalten entstehen zusätzlich Aufgaben:

- Müll rausbringen
- Badezimmer putzen
- Kindersachen vorbereiten
- Terminunterlagen zusammensuchen
- Reparaturen erledigen
- Schul- oder Kita-Aufgaben organisieren

Diese Aufgaben sind fachlich nicht dasselbe wie Einkaufsartikel. Ein Einkaufsartikel hat Menge und Kategorie. Eine Aufgabe hat Status, Zuständigkeit, Sichtbarkeit und optional eine Fälligkeit. Deshalb wird in Teil 11 ein neues Modul eingeführt, statt Aufgaben einfach als weitere Listeneinträge zu missbrauchen.

Der didaktische Wert ist wichtig: Die Reihe zeigt hier erstmals, wie neben einem bestehenden Hauptmodul ein zweites Modul sauber ergänzt wird, ohne die bisherige Listenlogik zu zerstören.

---

## Ausgangspunkt aus dem vorherigen Teil

Teil 10 hat folgende Grundlage geschaffen:

- Benutzerkonten sind vorhanden.
- Rollen sind vorhanden.
- Haushalte sind vorhanden.
- Listen können privat oder haushaltsbezogen sein.
- Der Adminbereich ist in Tabs strukturiert.
- Speicherarten JSON, SQLite und MySQL/MariaDB sind vorbereitet.

Genau deshalb kann Teil 11 Todos sauber unterscheiden: Eine Aufgabe kann privat sein oder zum Haushalt gehören. Ohne die Vorarbeit aus Teil 07 bis Teil 10 wäre diese Trennung fachlich unsauber.

---

## Fachliche Grundlagen

### Private Aufgabe

Eine private Aufgabe gehört einem Benutzer und ist nur für diesen Benutzer sichtbar. Admins können sie im Rahmen der Administrationsansicht ebenfalls sehen.

Beispiel:

```txt
Steuerunterlagen sortieren
```

### Familienaufgabe

Eine Familienaufgabe gehört zu einem Haushalt. Alle Mitglieder dieses Haushalts können sie sehen und bearbeiten.

Beispiel:

```txt
Mülltonnen rausstellen
```

### Status

Eine Aufgabe hat in dieser Version bewusst nur zwei Zustände:

| Status | Bedeutung |
| --- | --- |
| `open` | Aufgabe ist offen |
| `done` | Aufgabe ist erledigt |

Diese Entscheidung hält Teil 11 überschaubar. Komplexere Zustände wie „in Bearbeitung“, „wartet“ oder „verschoben“ können später ergänzt werden.

---

## Technische Umsetzung im Überblick

Die neue Version liegt in:

```txt
versions/
└── 11-todos/
    ├── api.php
    ├── auth.php
    ├── config.example.php
    ├── index.php
    ├── installer.php
    ├── login.php
    ├── data/
    │   └── .htaccess
    └── includes/
        ├── bootstrap.php
        ├── security.php
        └── storage.php
```

Neu ist das Datenfeld `todos` auf oberster Anwendungsebene.

```js
{
    id: "todo_123",
    ownerId: "user_123",
    familyId: "family_123",
    scope: "family",
    title: "Mülltonnen rausstellen",
    status: "open",
    dueAt: "2026-07-10",
    createdAt: "2026-07-04 23:30:00"
}
```

Wichtige Felder:

| Feld | Bedeutung |
| --- | --- |
| `ownerId` | Besitzer der Aufgabe |
| `familyId` | Haushalt des Besitzers bzw. der Familienaufgabe |
| `scope` | `private` oder `family` |
| `title` | Aufgabentext |
| `status` | `open` oder `done` |
| `dueAt` | optionales Fälligkeitsdatum |
| `createdAt` | Erstellzeitpunkt |

---

## Neue API-Aktionen

### `create_todo`

Erstellt eine private Aufgabe oder Familienaufgabe.

```txt
POST api.php?action=create_todo
```

Erwartete Daten:

```json
{
  "title": "Müll rausbringen",
  "scope": "family",
  "dueAt": "2026-07-10"
}
```

### `toggle_todo`

Wechselt den Status zwischen offen und erledigt.

```txt
POST api.php?action=toggle_todo
```

### `delete_todo`

Löscht eine Aufgabe, sofern der aktuelle Nutzer Zugriff darauf hat.

```txt
POST api.php?action=delete_todo
```

---

## Architekturentscheidung

Todos werden als eigenes Modul modelliert. Das ist die wichtigste Entscheidung in Teil 11.

Eine scheinbar einfachere Lösung wäre gewesen, Aufgaben als normale Listeneinträge zu speichern. Das hätte kurzfristig Code gespart, aber fachlich Probleme erzeugt:

- Aufgaben brauchen keine Menge.
- Aufgaben brauchen keine Einkaufskategorie.
- Aufgaben haben eine andere Sichtbarkeit.
- Aufgaben sollen später mit Nachrichten, Kalender und Dashboard verbunden werden.
- Aufgaben können später Zuweisungen, Prioritäten oder Kommentare bekommen.

Deshalb ist ein eigenes `todos`-Array beziehungsweise eine eigene `todos`-Tabelle die sauberere Grundlage.

---

## Pro und Kontra

### Vorteile

- klares neues Hauptmodul
- private und gemeinsame Aufgaben sauber getrennt
- Haushaltsgrenzen bleiben verständlich
- Aufgaben können später im Dashboard auftauchen
- gute Vorbereitung für Kalender und Nachrichten
- JSON, SQLite und MySQL bleiben weiterhin nutzbar

### Nachteile

- zusätzliche Datenstruktur erhöht die Komplexität
- Validierung muss erweitert werden
- alle Speicherarten müssen Todos laden und speichern können
- UI wird voller
- noch keine Bearbeiten-Funktion für bestehende Aufgaben
- noch keine Zuweisung an einzelne Familienmitglieder

Diese Nachteile sind bewusst akzeptiert. Teil 11 soll das Modul stabil einführen, nicht alle späteren Komfortfunktionen sofort lösen.

---

## Sicherheits- und Qualitätsaspekte

- Todos werden serverseitig validiert.
- `scope` akzeptiert nur `private` oder `family`.
- `status` akzeptiert nur `open` oder `done`.
- Familienaufgaben dürfen nur erstellt werden, wenn der Nutzer einem Haushalt zugeordnet ist.
- Nutzer sehen nur eigene private Aufgaben oder Aufgaben ihres Haushalts.
- Admins sehen Aufgaben im Administrationsbereich.
- POST-Aktionen bleiben durch CSRF-Token geschützt.
- Ausgaben im Frontend werden über DOM-Methoden wie `textContent` gesetzt.
- Die Speicherung nutzt weiterhin die bestehende Storage-Abstraktion.

---

## Typische Fehlerquellen

- Familienaufgaben ohne Haushalt erlauben
- private Aufgaben versehentlich für alle Haushaltsmitglieder anzeigen
- Todo-Status nur im Frontend ändern und nicht speichern
- `todos` nicht in JSON, SQLite und MySQL gleichermaßen berücksichtigen
- alte Datenstrukturen ohne `todos` nicht abfangen
- UI-Sichtbarkeit mit Backend-Rechten verwechseln
- Adminansicht als Ersatz für API-Prüfungen missverstehen

---

## Testcheckliste

| Test | Erwartetes Ergebnis |
| --- | --- |
| private Aufgabe erstellen | Aufgabe erscheint unter „Meine Aufgaben“ |
| Familienaufgabe erstellen | Aufgabe erscheint unter „Familienaufgaben“ |
| Nutzer ohne Haushalt erstellt Familienaufgabe | Backend lehnt Anfrage ab |
| Aufgabe abhaken | Status wechselt zwischen offen und erledigt |
| Aufgabe löschen | Aufgabe verschwindet dauerhaft |
| zweiter Nutzer im selben Haushalt | sieht Familienaufgabe |
| Nutzer aus anderem Haushalt | sieht Familienaufgabe nicht |
| Admin öffnet Verwaltung | Todo-Tab ist sichtbar |
| ungültiger Scope per API | Anfrage wird abgelehnt |
| ungültiges Fälligkeitsdatum | Anfrage wird abgelehnt |

---

## Was wurde gegenüber dem vorherigen Stand verbessert?

- Die Anwendung besitzt jetzt ein zweites Hauptmodul neben den Listen.
- Private Aufgaben und Familienaufgaben sind fachlich getrennt.
- Das Datenmodell wurde um `todos` erweitert.
- JSON-, SQLite- und MySQL-Speicherung berücksichtigen Aufgaben.
- Die Oberfläche enthält einen eigenen Todo-Bereich.
- Der Adminbereich enthält einen zusätzlichen Todo-Tab.

---

## Grenzen dieser Version

- Aufgaben können noch nicht nachträglich umbenannt werden.
- Aufgaben haben noch keine Priorität.
- Aufgaben können noch nicht einzelnen Familienmitgliedern zugewiesen werden.
- Es gibt noch keine Kommentare zu Aufgaben.
- Es gibt noch keine Kalenderverknüpfung.
- Es gibt noch keine Erinnerungen oder Benachrichtigungen.
- Es gibt noch keine automatische Dashboard-Zusammenfassung.

Diese Grenzen sind passend für den Lernstand. Erst wird das Modul eingeführt, später wird es fachlich vertieft.

---

## Ausblick auf den nächsten Teil

Teil 12 ergänzt private Nachrichten zwischen Nutzern.

Damit beginnt der Nachrichtenbereich der geplanten Reihe:

- private Nachrichten zwischen Nutzern
- später direkter Einzelchat
- später Familienchat
- später Dashboard-Zusammenführung mit Todos und Terminen

Die Todos aus Teil 11 können später mit Nachrichten verbunden werden, zum Beispiel wenn eine Aufgabe erstellt oder erledigt wurde.

---

## Einordnung für die Praxis

Teil 11 zeigt einen realistischen Schritt in wachsenden Anwendungen: Aus einer Listenanwendung wird eine modulare Haushaltsplattform.

In einem produktiven System würde man zusätzlich überlegen:

- eigene Tabellenmigrationen pro Release
- Bearbeiten-Funktion für Aufgaben
- Zuständigkeiten pro Aufgabe
- Prioritäten
- Erinnerungen
- Audit-Log
- Tests für Rechteprüfungen
- serverseitige Sortierung und Pagination bei vielen Aufgaben

Für die Tutorialreihe ist der aktuelle Umfang bewusst richtig: Das Modul ist vollständig genug, um verstanden und genutzt zu werden, aber noch klein genug, um den Code nachvollziehen zu können.

---

## Navigation

[← Teil 10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [README / Übersicht](../README.md) | [Teil 12 – Personal Messages →](#ausblick-auf-den-naechsten-teil)
