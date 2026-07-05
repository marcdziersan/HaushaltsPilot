# Teil 03 – Mengen, Kategorien und Status

Teil 03 macht aus einfachen Texteingaben strukturierte Artikelobjekte. Jeder Artikel kann eine Menge, eine Kategorie und einen Erledigt-Status besitzen.

> **Rolle in der Reihe:** Dieser Teil ist der Übergang von einer reinen Textliste zu einem echten Datenmodell. Damit wird die spätere Erweiterung um Listen, Benutzer und Datenbank deutlich sauberer.

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 03 – Mengen, Kategorien und Status  
**Quelltext zu diesem Teil:** [versions/03-categories-status](../versions/03-categories-status/)

[← Teil 02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [README / Übersicht](../README.md) | [Teil 04 – Mehrere Einzellisten →](teil-04-mehrere-einzellisten.md)

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [Version 01](../versions/01-simple-shopping-list/) | Artikel hinzufügen und löschen |
| 02 | [02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [Version 02](../versions/02-localstorage/) | Speicherung im Browser |
| 03 | **[03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md)** | **[Version 03](../versions/03-categories-status/)** | strukturierte Artikeldaten |
| 04 | [04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md) | [Version 04](../versions/04-multiple-lists/) | mehrere getrennte Listen |
| 05 | [05 – PHP-JSON-Backend](teil-05-php-json-backend.md) | [Version 05](../versions/05-json-backend/) | serverseitige JSON-Speicherung |
| 06 | [06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [Version 06](../versions/06-configurable-storage/) | JSON, SQLite oder MySQL/MariaDB |
| 07 | [07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md) | [Version 07](../versions/07-login-roles/) | Registrierung, Login, Sessions und Rollen |
| 08 | [08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md) | [Version 08](../versions/08-personal-shared-lists/) | private und gemeinsame Listenrechte |
| 09 | [09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [Version 09](../versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | [10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [Version 10](../versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |
| 11 | [11 – Todos](teil-11-todos.md) | [Version 11](../versions/11-todos/) | persönliche und gemeinsame Aufgaben |

<!-- tutorial-nav:end -->

---

## Ziel dieses Kapitels

Am Ende dieses Teils kann die Anwendung:

- Artikel nicht mehr nur als Text speichern
- Mengenangaben erfassen
- Kategorien zuordnen
- Artikel als offen oder erledigt markieren
- jeden Artikel über eine eigene ID eindeutig identifizieren
- Filter- oder Gruppierungslogik vorbereiten

---

## Warum, weshalb, wieso dieser Schritt?

Eine Einkaufsliste besteht in der Praxis selten nur aus Artikelnamen. Für echte Nutzung braucht man Mengen, Sortierung, Kategorien und Statusinformationen.
Der Schritt ist wichtig, weil spätere Funktionen nicht zuverlässig funktionieren, wenn Daten nur als einfache Zeichenketten existieren.
Die ID ist dabei zentral: Sobald ein Artikel bearbeitet, gelöscht oder synchronisiert wird, darf man ihn nicht nur über den Namen suchen. Namen können doppelt vorkommen.

Der didaktische Gedanke der Reihe bleibt dabei gleich: Jede neue Funktion löst ein konkretes Problem des vorherigen Standes. Dadurch entsteht keine Sammlung isolierter Codebeispiele, sondern eine fortlaufende Anwendung mit wachsender fachlicher und technischer Tiefe.

---

## Ausgangspunkt aus dem vorherigen Teil

- Aus einfachen Strings wurden strukturierte Artikelobjekte.
- Jeder Artikel besitzt eine eindeutige technische ID.
- Der Status eines Artikels kann verändert und gespeichert werden.

Dieser Ausgangspunkt bestimmt, warum die Erweiterung in diesem Kapitel sinnvoll ist und welche Grenzen weiterhin bewusst stehen bleiben.

---

## Fachliche Grundlagen

- **Objektstruktur**: Jeder Artikel wird als Objekt mit mehreren Eigenschaften gespeichert.
- **ID**: Eine eindeutige Kennung trennt die technische Identität vom sichtbaren Namen.
- **Status**: Ein boolescher Wert wie `done` bildet offen/erledigt ab.
- **Kategorie**: Eine fachliche Gruppierung, die später für Filter, Sortierung oder Statistik genutzt werden kann.

---

## Technische Umsetzung im Überblick

- Beim Hinzufügen wird nicht nur ein Text gespeichert, sondern ein vollständiges Artikelobjekt erzeugt.
- Die Oberfläche zeigt neben dem Namen auch Menge, Kategorie und Status an.
- Der Status kann per Button umgeschaltet werden.
- Alle Änderungen werden weiterhin im `localStorage` gespeichert.

### Projektstand nach Teil 03

```txt
versions/
└── 03-categories-status/
    └── index.html
```

---

## Architekturentscheidung

Das Datenmodell wird bewusst im Frontend eingeführt, bevor ein Backend hinzukommt. So bleibt sichtbar, welche Felder später serverseitig gespeichert werden müssen.
IDs werden früh eingeführt, weil sie fast jede spätere CRUD-Operation vereinfachen.
Die Oberfläche folgt nun stärker dem Datenmodell: Ein Artikel ist nicht mehr eine Zeile Text, sondern ein fachliches Objekt.

**Wichtig:** Die Reihe bleibt bewusst ohne Framework-Overkill. Ziel ist nicht, ein modernes Framework zu umgehen, sondern zuerst die Grundmechanik einer Webanwendung zu verstehen: Datenmodell, Oberfläche, Anfrage, Antwort, Speicherung, Validierung und Rechteprüfung.

---

## Pro und Kontra

### Vorteile

- realistischere Datenstruktur
- gute Vorbereitung auf CRUD und API
- Artikel können eindeutig angesprochen werden
- Statuswechsel ohne Namensvergleich möglich
- Grundlage für spätere Filter und Listenansichten

### Nachteile

- mehr JavaScript-Code als in Teil 02
- Datenmigration kann relevant werden, wenn alte localStorage-Daten existieren
- mehr Eingabefelder bedeuten mehr Validierungsbedarf
- ohne Backend weiterhin nur lokal nutzbar

Diese Nachteile sind in diesem Kapitel nicht automatisch Fehler. Viele davon sind bewusste Zwischenstände, die in späteren Teilen gezielt aufgelöst werden.

---

## Sicherheits- und Qualitätsaspekte

- Auch strukturierte Daten bleiben Benutzereingaben. Sie dürfen nicht als HTML ausgegeben werden.
- Kategorien sollten aus festen Werten bestehen oder zumindest normalisiert werden, damit die Daten konsistent bleiben.
- Da noch kein Backend vorhanden ist, gibt es keine serverseitige Vertrauensgrenze. Alle Daten sind lokal manipulierbar.

---

## Typische Fehlerquellen

- Artikel über den Namen statt über die ID löschen
- Status nur im DOM ändern, aber nicht im Datenarray speichern
- leere Namen mit gültigen Mengen speichern
- alte localStorage-Daten nicht berücksichtigen und dadurch Fehler beim Laden erzeugen

---

## Testcheckliste

| Test | Erwartetes Ergebnis |
| --- | --- |
| Artikel mit Menge hinzufügen | Menge wird angezeigt und gespeichert |
| Artikel mit Kategorie hinzufügen | Kategorie erscheint am Artikel |
| Status umschalten | Artikel wechselt zwischen offen und erledigt |
| Seite neu laden | Menge, Kategorie und Status bleiben erhalten |
| zwei gleiche Artikelnamen hinzufügen | beide bleiben einzeln löschbar |

---

## Was wurde gegenüber dem vorherigen Stand verbessert?

- Aus einfachen Strings wurden strukturierte Artikelobjekte.
- Jeder Artikel besitzt eine eindeutige technische ID.
- Der Status eines Artikels kann verändert und gespeichert werden.

---

## Grenzen dieser Version

- weiterhin nur eine einzige Einkaufsliste
- noch keine Bearbeitung bestehender Artikelnamen
- keine Benutzer- oder Haushaltslogik
- keine serverseitige Prüfung

---

## Ausblick auf den nächsten Teil

Teil 04 führt mehrere Einzellisten ein. Dadurch kann der Nutzer getrennte Listen wie Wocheneinkauf, Drogerie oder Baumarkt verwalten.

---

## Einordnung für die Praxis

Dieser Teil ist ein Lernschritt, kein fertiges Produkt. Genau darin liegt der Wert der Reihe: Jeder Stand ist klein genug, um verstanden zu werden, aber konkret genug, um später erweitert zu werden.

In einem professionellen Umfeld würde man zusätzlich auf saubere Release-Stände, automatisierte Tests, Konfigurationsbeispiele, Datenmigrationen, Deployment-Dokumentation und eine klare Trennung zwischen Demo- und Produktivbetrieb achten. Die Tutorialreihe führt diese Themen schrittweise ein, ohne den Einstieg unnötig zu überladen.

---

## Navigation

[← Teil 02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [README / Übersicht](../README.md) | [Teil 04 – Mehrere Einzellisten →](teil-04-mehrere-einzellisten.md)
