# Teil 01 – Einfache Einkaufsliste

Der erste Teil baut die kleinste sinnvolle Version der Anwendung: eine Einkaufsliste im Browser, ohne Speicherung und ohne Backend. Der Fokus liegt auf HTML-Struktur, CSS-Grundlayout und einfacher DOM-Logik in JavaScript.

> **Rolle in der Reihe:** Dieser Teil ist der Einstiegspunkt der gesamten Reihe. Er erzeugt bewusst noch keine perfekte Anwendung, sondern ein klares, verständliches Fundament, auf dem alle späteren Erweiterungen aufbauen.

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 01 – Einfache Einkaufsliste  
**Quelltext zu diesem Teil:** [versions/01-simple-shopping-list](../versions/01-simple-shopping-list/)

← kein vorheriger Teil | [README / Übersicht](../README.md) | [Teil 02 – Einkaufsliste mit LocalStorage →](teil-02-localstorage.md)

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | **[01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md)** | **[Version 01](../versions/01-simple-shopping-list/)** | Artikel hinzufügen und löschen |
| 02 | [02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [Version 02](../versions/02-localstorage/) | Speicherung im Browser |
| 03 | [03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [Version 03](../versions/03-categories-status/) | strukturierte Artikeldaten |
| 04 | [04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md) | [Version 04](../versions/04-multiple-lists/) | mehrere getrennte Listen |
| 05 | [05 – PHP-JSON-Backend](teil-05-php-json-backend.md) | [Version 05](../versions/05-json-backend/) | serverseitige JSON-Speicherung |
| 06 | [06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [Version 06](../versions/06-configurable-storage/) | JSON, SQLite oder MySQL/MariaDB |
| 07 | [07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md) | [Version 07](../versions/07-login-roles/) | Registrierung, Login, Sessions und Rollen |
| 08 | [08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md) | [Version 08](../versions/08-personal-shared-lists/) | private und gemeinsame Listenrechte |
| 09 | [09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [Version 09](../versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | [10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [Version 10](../versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |

<!-- tutorial-nav:end -->

---

## Ziel dieses Kapitels

Am Ende dieses Teils kann die Anwendung:

- eine lauffähige Ein-Datei-Webanwendung erstellen
- Artikel über ein Eingabefeld hinzufügen
- leere Eingaben verhindern
- Artikel in einer Liste darstellen
- einzelne Artikel wieder löschen
- einen einfachen Artikelzähler anzeigen
- die Bedienung per Button und Enter-Taste ermöglichen

---

## Warum, weshalb, wieso dieser Schritt?

Ein professionelles Projekt beginnt nicht zwingend mit einer Datenbank, einem Login oder einem komplexen Framework. Sinnvoller ist zuerst ein klar begrenzter Kernprozess: Eingabe, Verarbeitung, Ausgabe und Entfernen von Daten.
Die Einkaufsliste ist dafür ideal, weil sie fachlich jeder versteht. Dadurch muss der Lernende nicht gleichzeitig eine komplizierte Domäne und neue Technik verstehen.
Wir starten ohne Speicherung, weil dadurch die direkte Browserlogik sichtbar bleibt. Erst wenn diese Logik verstanden ist, lohnt sich die Frage, wo Daten dauerhaft abgelegt werden.

Der didaktische Gedanke der Reihe bleibt dabei gleich: Jede neue Funktion löst ein konkretes Problem des vorherigen Standes. Dadurch entsteht keine Sammlung isolierter Codebeispiele, sondern eine fortlaufende Anwendung mit wachsender fachlicher und technischer Tiefe.

---

## Ausgangspunkt aus dem vorherigen Teil

- Startpunkt der Reihe; es gibt noch keinen vorherigen Teil.

Dieser Ausgangspunkt bestimmt, warum die Erweiterung in diesem Kapitel sinnvoll ist und welche Grenzen weiterhin bewusst stehen bleiben.

---

## Fachliche Grundlagen

- **HTML** beschreibt die Struktur der Seite: Überschrift, Formular, Eingabefeld, Button, Liste und Statusbereich.
- **CSS** sorgt für Lesbarkeit, Abstände, mobile Darstellung und ein optisch sauberes Grundlayout.
- **JavaScript** reagiert auf Benutzeraktionen, liest Eingaben aus, erzeugt DOM-Elemente und entfernt Listeneinträge wieder.
- **Validierung** bedeutet hier zunächst nur: Eine leere oder nur aus Leerzeichen bestehende Eingabe wird nicht übernommen.

---

## Technische Umsetzung im Überblick

- Die komplette Anwendung liegt in `versions/01-simple-shopping-list/index.html`.
- Der Artikeltext wird aus dem Eingabefeld gelesen, mit `trim()` bereinigt und nur dann übernommen, wenn noch Inhalt übrig bleibt.
- Für jeden Artikel wird ein Listeneintrag erzeugt. Der Löschen-Button hängt direkt an diesem Eintrag und entfernt genau diesen DOM-Knoten.
- Nach jeder Änderung wird der Statusbereich aktualisiert, damit Zähler und Leerhinweis immer zum aktuellen Stand passen.

### Projektstand nach Teil 01

```txt
versions/
└── 01-simple-shopping-list/
    └── index.html
```

---

## Architekturentscheidung

Für Teil 01 ist eine einzige HTML-Datei die richtige Entscheidung. Sie hält den Einstieg niedrigschwellig und vermeidet künstliche Komplexität.
Noch gibt es keine Trennung in Module, keine API und keine Persistenz. Das wäre an dieser Stelle didaktisch zu früh.
Die Architektur ist absichtlich klein, aber nicht beliebig: Eingabe, Verarbeitung und Darstellung sind bereits logisch voneinander unterscheidbar.

**Wichtig:** Die Reihe bleibt bewusst ohne Framework-Overkill. Ziel ist nicht, ein modernes Framework zu umgehen, sondern zuerst die Grundmechanik einer Webanwendung zu verstehen: Datenmodell, Oberfläche, Anfrage, Antwort, Speicherung, Validierung und Rechteprüfung.

---

## Pro und Kontra

### Vorteile

- sehr einfacher Einstieg ohne Server
- sofort im Browser testbar
- DOM-Manipulation wird direkt sichtbar
- geringer technischer Overhead
- gute Basis für spätere Erweiterungen

### Nachteile

- Daten gehen beim Neuladen verloren
- keine Mehrbenutzerfähigkeit
- keine echte Datenstruktur für Kategorien oder Mengen
- keine serverseitige Validierung
- für produktive Nutzung noch ungeeignet

Diese Nachteile sind in diesem Kapitel nicht automatisch Fehler. Viele davon sind bewusste Zwischenstände, die in späteren Teilen gezielt aufgelöst werden.

---

## Sicherheits- und Qualitätsaspekte

- Da noch kein Backend existiert, gibt es in diesem Teil keine Serverangriffsfläche.
- Texte sollten trotzdem als Text und nicht als ungeprüftes HTML eingefügt werden. Das verhindert früh, dass sich unsichere Ausgabemuster einschleichen.
- Sicherheitskonzepte wie CSRF, Sessions oder Rechteprüfung kommen bewusst später, wenn ein Server eingeführt wird.

---

## Typische Fehlerquellen

- Eingaben ungeprüft übernehmen und dadurch leere Listeneinträge erzeugen
- HTML per String zusammenbauen, obwohl `textContent` für Benutzereingaben sicherer ist
- den Zähler nicht nach jedem Hinzufügen oder Löschen aktualisieren
- nur den Button unterstützen und die Enter-Taste vergessen

---

## Testcheckliste

| Test | Erwartetes Ergebnis |
| --- | --- |
| Artikel eingeben und Button klicken | Artikel erscheint in der Liste |
| Artikel eingeben und Enter drücken | Artikel erscheint in der Liste |
| Leere Eingabe abschicken | kein neuer Eintrag wird erzeugt |
| Artikel löschen | genau dieser Artikel verschwindet |
| alle Artikel löschen | Leerhinweis erscheint wieder |
| mehrere Artikel hinzufügen | Zähler zeigt die korrekte Anzahl |

---

## Was wurde gegenüber dem vorherigen Stand verbessert?

- Startpunkt der Reihe; es gibt noch keinen vorherigen Teil.

---

## Grenzen dieser Version

- keine dauerhafte Speicherung
- nur eine einzige Liste
- keine Mengen, Kategorien oder Statuswerte
- keine Trennung zwischen Datenmodell und Darstellung

---

## Ausblick auf den nächsten Teil

In Teil 02 wird `localStorage` ergänzt. Damit bleibt die Liste auch nach dem Neuladen des Browsers erhalten.

---

## Einordnung für die Praxis

Dieser Teil ist ein Lernschritt, kein fertiges Produkt. Genau darin liegt der Wert der Reihe: Jeder Stand ist klein genug, um verstanden zu werden, aber konkret genug, um später erweitert zu werden.

In einem professionellen Umfeld würde man zusätzlich auf saubere Release-Stände, automatisierte Tests, Konfigurationsbeispiele, Datenmigrationen, Deployment-Dokumentation und eine klare Trennung zwischen Demo- und Produktivbetrieb achten. Die Tutorialreihe führt diese Themen schrittweise ein, ohne den Einstieg unnötig zu überladen.

---

## Navigation

← kein vorheriger Teil | [README / Übersicht](../README.md) | [Teil 02 – Einkaufsliste mit LocalStorage →](teil-02-localstorage.md)
