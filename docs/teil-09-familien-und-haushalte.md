# Teil 09 – Familien und Haushalte

Teil 09 führt Haushalte/Familien als eigene fachliche Ebene ein. Gemeinsame Listen werden dadurch nicht mehr global geteilt, sondern nur innerhalb desselben Haushalts sichtbar.

> **Rolle in der Reihe:** Dieser Teil macht aus allgemeinen Freigaben ein echtes Haushaltsmodell. Damit entsteht die Grundlage für spätere Familien-Todos, Familienchat und gemeinsame Kalender.

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 09 – Familien und Haushalte  
**Quelltext zu diesem Teil:** [versions/09-families-households](../versions/09-families-households/)

[← Teil 08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md) | [README / Übersicht](../README.md) | [Teil 10 – Gemeinschaftslisten und Admin-Tabs →](teil-10-gemeinschaftslisten-und-admin-tabs.md)

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
| 09 | **[09 – Familien und Haushalte](teil-09-familien-und-haushalte.md)** | **[Version 09](../versions/09-families-households/)** | Haushaltszuordnung für Nutzer |
| 10 | [10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [Version 10](../versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |
| 11 | [11 – Todos](teil-11-todos.md) | [Version 11](../versions/11-todos/) | persönliche und gemeinsame Aufgaben |

<!-- tutorial-nav:end -->

---

## Ziel dieses Kapitels

Am Ende dieses Teils kann die Anwendung:

- Haushalte erstellen, umbenennen und löschen
- Benutzer einem Haushalt zuordnen
- Benutzer aus Haushalten entfernen
- Listen einem Haushalt zuordnen
- gemeinsame Listen nur im selben Haushalt anzeigen
- Adminverwaltung um Haushaltsfunktionen erweitern
- spätere Module auf privaten und haushaltsbezogenen Kontext vorbereiten

---

## Warum, weshalb, wieso dieser Schritt?

Teil 08 hatte gemeinsame Listen, aber noch keinen fachlichen Rahmen. Ohne Haushalte wären gemeinsame Listen im Zweifel zu breit sichtbar.
Eine Familien- oder Haushalts-App braucht eine Gruppengrenze. Nicht jeder registrierte Nutzer gehört automatisch zur selben Familie.
Die Haushaltszuordnung ist auch für spätere Module wichtig: Todos, Nachrichten und Kalender müssen wissen, ob etwas privat, direkt oder haushaltsbezogen ist.

Der didaktische Gedanke der Reihe bleibt dabei gleich: Jede neue Funktion löst ein konkretes Problem des vorherigen Standes. Dadurch entsteht keine Sammlung isolierter Codebeispiele, sondern eine fortlaufende Anwendung mit wachsender fachlicher und technischer Tiefe.

---

## Ausgangspunkt aus dem vorherigen Teil

- Gemeinsame Listen sind jetzt haushaltsbezogen statt global.
- Benutzer können fachlich einem Haushalt zugeordnet werden.
- Adminfunktionen decken Haushaltsverwaltung ab.
- Die Datenbasis ist auf spätere Familienmodule vorbereitet.

Dieser Ausgangspunkt bestimmt, warum die Erweiterung in diesem Kapitel sinnvoll ist und welche Grenzen weiterhin bewusst stehen bleiben.

---

## Fachliche Grundlagen

- **familyId**: Benutzer und Listen können einem Haushalt zugeordnet werden.
- **Haushaltsliste**: Eine geteilte Liste ist nur für Mitglieder desselben Haushalts sichtbar.
- **Nutzer ohne Haushalt**: Ein registrierter Nutzer kann zunächst ohne Gruppenzugehörigkeit existieren.
- **Adminverwaltung**: Admins pflegen Haushalte und Benutzerzuordnungen.
- **Referenzkonsistenz**: Wird ein Haushalt gelöscht oder ein Nutzer entfernt, müssen abhängige Listen sinnvoll behandelt werden.

---

## Technische Umsetzung im Überblick

- Die Datenstruktur wird um `families` erweitert.
- Benutzer erhalten ein Feld `familyId`.
- Listen erhalten ebenfalls ein Feld `familyId`.
- Die Sichtbarkeitsregel lautet: Admin sieht alles; Besitzer sieht eigene Listen; geteilte Listen sind nur im selben Haushalt sichtbar.
- Neue Adminaktionen sind u. a. `admin_create_family`, `admin_update_family_name`, `admin_delete_family` und `admin_update_user_family`.

### Projektstand nach Teil 09

```txt
versions/
└── 09-families-households/
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

Haushalte werden als eigene Entität modelliert, statt nur als Textfeld am Benutzer. Das ist wichtig für spätere Erweiterbarkeit.
Die Sichtbarkeit hängt nun nicht mehr nur an `isShared`, sondern zusätzlich an gemeinsamer `familyId`.
Damit entsteht eine klare fachliche Grenze: privat, eigener Haushalt, Adminsicht.

**Wichtig:** Die Reihe bleibt bewusst ohne Framework-Overkill. Ziel ist nicht, ein modernes Framework zu umgehen, sondern zuerst die Grundmechanik einer Webanwendung zu verstehen: Datenmodell, Oberfläche, Anfrage, Antwort, Speicherung, Validierung und Rechteprüfung.

---

## Pro und Kontra

### Vorteile

- gemeinsame Daten sind nicht mehr global sichtbar
- realistische Familien-/Haushaltsstruktur
- gute Grundlage für Chat, Todos und Kalender
- Admin kann Nutzer sauber organisieren
- Sichtbarkeitsregeln werden fachlich präziser

### Nachteile

- mehr Randfälle bei Haushaltswechseln
- Löschen von Haushalten benötigt klare Folgeregeln
- Nutzer ohne Haushalt können weniger Funktionen verwenden
- noch kein Einladungssystem für Selbstverwaltung
- Adminbereich wird umfangreicher und unübersichtlicher

Diese Nachteile sind in diesem Kapitel nicht automatisch Fehler. Viele davon sind bewusste Zwischenstände, die in späteren Teilen gezielt aufgelöst werden.

---

## Sicherheits- und Qualitätsaspekte

- Die `familyId` darf nicht blind aus dem Frontend vertraut werden. Das Backend muss prüfen, welcher Haushalt für den Nutzer gilt.
- Ein Nutzer darf keine Listen fremder Haushalte per direkter API-Manipulation erreichen.
- Adminaktionen zur Haushaltsverwaltung brauchen Rollenprüfung und CSRF-Schutz.
- Beim Entfernen aus einem Haushalt müssen geteilte Listen sauber zurückgesetzt werden, damit keine unbeabsichtigten Freigaben bleiben.

---

## Typische Fehlerquellen

- nur nach `isShared` filtern und die `familyId` vergessen
- Nutzer ohne Haushalt trotzdem Gemeinschaftslisten erstellen lassen
- Haushalt löschen, ohne Benutzer und Listenreferenzen zu bereinigen
- Haushaltswechsel ohne Aktualisierung geteilter Listen durchführen
- Admin- und Nutzersicht in einer unübersichtlichen Tabelle vermischen

---

## Testcheckliste

| Test | Erwartetes Ergebnis |
| --- | --- |
| Haushalt erstellen | Haushalt erscheint in der Verwaltung |
| Nutzer Haushalt zuordnen | Nutzer wird als Mitglied gezählt |
| Nutzer A teilt Liste im Haushalt 1 | Nutzer B im selben Haushalt sieht sie |
| Nutzer C in Haushalt 2 | sieht diese Liste nicht |
| Nutzer aus Haushalt entfernen | seine geteilten Listen werden nicht global sichtbar |
| Haushalt löschen | Zuordnungen werden konsistent behandelt |
| normaler Nutzer ruft Haushalts-Adminaktion auf | Backend lehnt Zugriff ab |

---

## Was wurde gegenüber dem vorherigen Stand verbessert?

- Gemeinsame Listen sind jetzt haushaltsbezogen statt global.
- Benutzer können fachlich einem Haushalt zugeordnet werden.
- Adminfunktionen decken Haushaltsverwaltung ab.
- Die Datenbasis ist auf spätere Familienmodule vorbereitet.

---

## Grenzen dieser Version

- kein Einladungssystem
- kein Rollenmodell innerhalb eines Haushalts
- noch keine Haushalts-Todos oder Nachrichten
- Adminbereich wird langsam zu umfangreich für eine einzelne Ansicht

---

## Ausblick auf den nächsten Teil

Teil 10 vertieft Gemeinschaftslisten durch Listentypen und teilt den Adminbereich in Tabs auf.

---

## Einordnung für die Praxis

Dieser Teil ist ein Lernschritt, kein fertiges Produkt. Genau darin liegt der Wert der Reihe: Jeder Stand ist klein genug, um verstanden zu werden, aber konkret genug, um später erweitert zu werden.

In einem professionellen Umfeld würde man zusätzlich auf saubere Release-Stände, automatisierte Tests, Konfigurationsbeispiele, Datenmigrationen, Deployment-Dokumentation und eine klare Trennung zwischen Demo- und Produktivbetrieb achten. Die Tutorialreihe führt diese Themen schrittweise ein, ohne den Einstieg unnötig zu überladen.

---

## Navigation

[← Teil 08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md) | [README / Übersicht](../README.md) | [Teil 10 – Gemeinschaftslisten und Admin-Tabs →](teil-10-gemeinschaftslisten-und-admin-tabs.md)
