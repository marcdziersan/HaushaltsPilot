# Teil 10 – Gemeinschaftslisten und Admin-Tabs

Teil 10 verfeinert die Haushaltslisten durch Listentypen und strukturiert die inzwischen gewachsene Administration in Tabs.

> **Rolle in der Reihe:** Dieser Teil schließt die erste Hälfte der Reihe sauber ab. Die Anwendung besitzt nun Login, Rollen, persönliche Listen, Haushalte, Gemeinschaftslisten und eine besser bedienbare Administration.

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 10 – Gemeinschaftslisten und Admin-Tabs  
**Quelltext zu diesem Teil:** [versions/10-shared-lists-admin-tabs](../versions/10-shared-lists-admin-tabs/)

[← Teil 09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [README / Übersicht](../README.md) | kein nächster Teil →

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
| 10 | **[10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md)** | **[Version 10](../versions/10-shared-lists-admin-tabs/)** | vertiefte Gemeinschaftslisten und Admin-Tabs |

<!-- tutorial-nav:end -->

---

## Ziel dieses Kapitels

Am Ende dieses Teils kann die Anwendung:

- Listentypen für Einkauf, Haushalt und Sonstiges einführen
- Gemeinschaftslisten nach Typ gruppieren
- Listentypen ändern
- Adminlisten nach Typ und Status filtern
- Adminbereich in Tabs strukturieren
- Haushalte, Benutzer und Listen übersichtlicher verwalten
- Grundlage für Teil 11 und weitere Module stabilisieren

---

## Warum, weshalb, wieso dieser Schritt?

Mit Teil 09 ist die Fachlichkeit gewachsen. Es reicht nicht mehr, alle Listen gleich darzustellen.
Listentypen machen die Anwendung verständlicher: Einkaufsliste, Haushaltsliste und sonstige Liste haben unterschiedliche fachliche Bedeutung.
Der Adminbereich war inzwischen zu voll für eine einzige Ansicht. Tabs lösen kein Sicherheitsproblem, aber ein klares UX-Problem.

Der didaktische Gedanke der Reihe bleibt dabei gleich: Jede neue Funktion löst ein konkretes Problem des vorherigen Standes. Dadurch entsteht keine Sammlung isolierter Codebeispiele, sondern eine fortlaufende Anwendung mit wachsender fachlicher und technischer Tiefe.

---

## Ausgangspunkt aus dem vorherigen Teil

- Gemeinschaftslisten besitzen jetzt fachliche Typen.
- Die Administration ist in Tabs gegliedert.
- Listen können im Adminbereich besser gefiltert und verwaltet werden.
- Die erste Tutorialhälfte endet mit einer stabilen Mehrbenutzer-/Haushaltsbasis.

Dieser Ausgangspunkt bestimmt, warum die Erweiterung in diesem Kapitel sinnvoll ist und welche Grenzen weiterhin bewusst stehen bleiben.

---

## Fachliche Grundlagen

- **listType**: Jede Liste erhält einen fachlichen Typ.
- **Gruppierte Darstellung**: Gemeinschaftslisten können nach Zweck angezeigt werden.
- **Admin-Tabs**: Haushalte, Benutzer und Listen werden visuell getrennt.
- **Filterung**: Admins können Listen nach Typ, Sichtbarkeit oder Besitz filtern.
- **Backendvalidierung**: Nur erlaubte Typwerte werden akzeptiert.

---

## Technische Umsetzung im Überblick

- Listen erhalten das Feld `listType` mit Werten wie `shopping`, `household` oder `other`.
- `update_list_type` erlaubt Besitzern oder Admins, den Listentyp zu ändern.
- Die Oberfläche gruppiert gemeinsame Listen nach Typ.
- Der Adminbereich wird in Tabs für Haushalte, Benutzer und Listen aufgeteilt.
- Die Listenverwaltung erhält Filter und Aktionen für Typ, Besitzer und Sichtbarkeit.

### Projektstand nach Teil 10

```txt
versions/
└── 10-shared-lists-admin-tabs/
    ├── api.php
    ├── auth.php
    ├── config.php
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

---

## Architekturentscheidung

Listentypen sind eine fachliche Erweiterung, keine reine UI-Spielerei. Sie gehören daher in das Datenmodell und nicht nur als CSS-Klasse in die Oberfläche.
Tabs trennen Bedienbereiche, aber keine Sicherheitsbereiche. Die eigentliche Absicherung bleibt im Backend.
Teil 10 stabilisiert die bisherige Plattform, bevor neue Module wie Todos, Nachrichten oder Kalender ergänzt werden.

**Wichtig:** Die Reihe bleibt bewusst ohne Framework-Overkill. Ziel ist nicht, ein modernes Framework zu umgehen, sondern zuerst die Grundmechanik einer Webanwendung zu verstehen: Datenmodell, Oberfläche, Anfrage, Antwort, Speicherung, Validierung und Rechteprüfung.

---

## Pro und Kontra

### Vorteile

- bessere fachliche Ordnung der Listen
- übersichtlichere Nutzeroberfläche
- Adminbereich wird deutlich besser bedienbar
- Backend akzeptiert nur definierte Listentypen
- gute Grundlage für weitere Module

### Nachteile

- zusätzliches Datenfeld muss in allen Speicherarten berücksichtigt werden
- mehr UI-Zustand durch Tabs und Filter
- Migration älterer Listen braucht Defaultwerte
- Tabs dürfen nicht mit echter Rechteprüfung verwechselt werden
- Adminbereich bleibt weiter ausbaufähig

Diese Nachteile sind in diesem Kapitel nicht automatisch Fehler. Viele davon sind bewusste Zwischenstände, die in späteren Teilen gezielt aufgelöst werden.

---

## Sicherheits- und Qualitätsaspekte

- `listType` wird serverseitig gegen feste erlaubte Werte geprüft.
- Nur Besitzer oder Admins dürfen den Typ einer Liste ändern.
- Haushaltsgrenzen bleiben weiterhin verbindlich.
- Admin-Tabs ändern nichts an der Backendprüfung. Jede kritische API-Aktion bleibt geschützt.
- Textausgaben im Frontend sollten weiterhin über sichere DOM-Methoden erfolgen.

---

## Typische Fehlerquellen

- Listentyp nur im Frontend speichern und beim Reload verlieren
- beliebige Typwerte aus Requests akzeptieren
- Tabs als Rechteprüfung missverstehen
- Filter nur optisch anwenden, aber Adminaktionen nicht prüfen
- alte Listen ohne `listType` nicht mit Standardwert behandeln

---

## Testcheckliste

| Test | Erwartetes Ergebnis |
| --- | --- |
| Einkaufsliste erstellen | Liste erscheint in der Einkaufsgruppe |
| Haushaltsliste erstellen | Liste erscheint in der Haushaltsgruppe |
| Listentyp ändern | Liste wandert in die passende Gruppe |
| ungültigen Typ per API senden | Backend lehnt Anfrage ab |
| Admin öffnet Verwaltung | Tabs für Haushalte, Benutzer und Listen sind sichtbar |
| Admin filtert Listen | nur passende Listen werden angezeigt |
| Nutzer aus anderem Haushalt | sieht fremde Gemeinschaftsliste nicht |

---

## Was wurde gegenüber dem vorherigen Stand verbessert?

- Gemeinschaftslisten besitzen jetzt fachliche Typen.
- Die Administration ist in Tabs gegliedert.
- Listen können im Adminbereich besser gefiltert und verwaltet werden.
- Die erste Tutorialhälfte endet mit einer stabilen Mehrbenutzer-/Haushaltsbasis.

---

## Grenzen dieser Version

- noch keine Todos
- noch keine Nachrichten oder Chats
- noch kein Kalender
- kein fein abgestuftes Rechte- und Einladungssystem
- noch keine Testsuite oder Migrationen für Releases

---

## Ausblick auf den nächsten Teil

Teil 11 kann auf dieser Basis Todos ergänzen: private Aufgaben, Haushaltsaufgaben, Status, Fälligkeit und spätere Zuweisung an Benutzer.

---

## Einordnung für die Praxis

Dieser Teil ist ein Lernschritt, kein fertiges Produkt. Genau darin liegt der Wert der Reihe: Jeder Stand ist klein genug, um verstanden zu werden, aber konkret genug, um später erweitert zu werden.

In einem professionellen Umfeld würde man zusätzlich auf saubere Release-Stände, automatisierte Tests, Konfigurationsbeispiele, Datenmigrationen, Deployment-Dokumentation und eine klare Trennung zwischen Demo- und Produktivbetrieb achten. Die Tutorialreihe führt diese Themen schrittweise ein, ohne den Einstieg unnötig zu überladen.

---

## Navigation

[← Teil 09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [README / Übersicht](../README.md) | kein nächster Teil →
