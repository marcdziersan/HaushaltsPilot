# Teil 02 – Einkaufsliste mit LocalStorage

Teil 02 erweitert die Einkaufsliste um dauerhafte Browser-Speicherung. Die Anwendung bleibt weiterhin rein frontendbasiert, verliert ihre Daten aber nicht mehr beim Neuladen.

> **Rolle in der Reihe:** Dieser Teil zeigt den ersten echten Persistenzschritt. Die Daten verlassen noch nicht den Browser, aber sie werden erstmals nicht nur im flüchtigen DOM gehalten.

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 02 – Einkaufsliste mit LocalStorage  
**Quelltext zu diesem Teil:** [versions/02-localstorage](../versions/02-localstorage/)

[← Teil 01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [README / Übersicht](../README.md) | [Teil 03 – Mengen, Kategorien und Status →](teil-03-kategorien-mengen-status.md)

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [Version 01](../versions/01-simple-shopping-list/) | Artikel hinzufügen und löschen |
| 02 | **[02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md)** | **[Version 02](../versions/02-localstorage/)** | Speicherung im Browser |
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

- Artikel dauerhaft im Browser speichern
- Daten beim Seitenstart automatisch laden
- nach jeder Änderung den aktuellen Stand sichern
- eine einfache JavaScript-Datenstruktur einführen
- DOM-Darstellung aus gespeicherten Daten neu rendern

---

## Warum, weshalb, wieso dieser Schritt?

Nach Teil 01 ist der größte fachliche Mangel offensichtlich: Nach dem Neuladen ist alles weg. `localStorage` löst genau dieses Problem mit sehr wenig zusätzlicher Technik.
Wir verwenden noch kein Backend, weil der Lernschritt sonst zu groß wäre. Persistenz soll zuerst als Konzept verstanden werden: Daten speichern, Daten laden, Daten wieder anzeigen.
Dieser Zwischenschritt ist didaktisch wertvoll, weil er später den Unterschied zwischen lokaler Speicherung und serverseitiger Speicherung deutlich macht.

Der didaktische Gedanke der Reihe bleibt dabei gleich: Jede neue Funktion löst ein konkretes Problem des vorherigen Standes. Dadurch entsteht keine Sammlung isolierter Codebeispiele, sondern eine fortlaufende Anwendung mit wachsender fachlicher und technischer Tiefe.

---

## Ausgangspunkt aus dem vorherigen Teil

- Daten sind nicht mehr nur temporär im DOM vorhanden.
- Die Anwendung kann aus gespeicherten Daten neu aufgebaut werden.
- Der Code nähert sich einem echten Datenfluss: laden, ändern, speichern, rendern.

Dieser Ausgangspunkt bestimmt, warum die Erweiterung in diesem Kapitel sinnvoll ist und welche Grenzen weiterhin bewusst stehen bleiben.

---

## Fachliche Grundlagen

- **localStorage** speichert Schlüssel-Wert-Paare dauerhaft im Browser des jeweiligen Geräts.
- **JSON.stringify()** wandelt JavaScript-Daten in eine speicherbare Zeichenkette um.
- **JSON.parse()** wandelt gespeicherte Zeichenketten zurück in JavaScript-Daten.
- **Rendern aus Daten** bedeutet: Die Liste wird nicht mehr nur spontan ergänzt, sondern aus dem aktuellen Datenbestand neu aufgebaut.

---

## Technische Umsetzung im Überblick

- Die Anwendung bleibt in einer einzelnen `index.html`.
- Die Artikeldaten werden in einem Array gehalten und unter einem festen Schlüssel im Browser gespeichert.
- Beim Laden der Seite wird versucht, vorhandene Daten aus `localStorage` zu lesen.
- Nach dem Hinzufügen oder Löschen eines Artikels wird der vollständige Datenstand erneut gespeichert.

### Projektstand nach Teil 02

```txt
versions/
└── 02-localstorage/
    └── index.html
```

---

## Architekturentscheidung

Die wichtigste Architekturänderung ist die Trennung zwischen Daten und Oberfläche. Der DOM ist nicht mehr die einzige Wahrheit.
Die Anwendung bekommt ein einfaches Client-State-Modell: Daten liegen im Array, die Oberfläche wird daraus erzeugt.
Diese Denkweise ist später entscheidend, wenn dieselben Daten nicht mehr aus dem Browser, sondern aus einer API kommen.

**Wichtig:** Die Reihe bleibt bewusst ohne Framework-Overkill. Ziel ist nicht, ein modernes Framework zu umgehen, sondern zuerst die Grundmechanik einer Webanwendung zu verstehen: Datenmodell, Oberfläche, Anfrage, Antwort, Speicherung, Validierung und Rechteprüfung.

---

## Pro und Kontra

### Vorteile

- weiterhin ohne Server nutzbar
- sehr leicht testbar
- Daten bleiben nach dem Neuladen erhalten
- guter Einstieg in JSON-Serialisierung
- saubere Vorbereitung auf spätere API-Daten

### Nachteile

- Daten bleiben nur auf diesem Gerät und in diesem Browser
- kein Mehrbenutzerbetrieb möglich
- keine Zugriffskontrolle
- Speicher kann vom Benutzer gelöscht werden
- für sensible Daten ungeeignet

Diese Nachteile sind in diesem Kapitel nicht automatisch Fehler. Viele davon sind bewusste Zwischenstände, die in späteren Teilen gezielt aufgelöst werden.

---

## Sicherheits- und Qualitätsaspekte

- `localStorage` ist nicht für vertrauliche Daten geeignet. Alles, was dort gespeichert wird, liegt im Browser des Nutzers.
- Es gibt noch keine Authentifizierung und keine Rechteprüfung. Deshalb eignet sich dieser Teil nur für lokale Demo- und Lernzwecke.
- Benutzereingaben sollten weiterhin als Text ausgegeben werden. Speicherung macht unsichere Ausgabe nicht sicherer.

---

## Typische Fehlerquellen

- `JSON.parse()` ohne Fehlerbehandlung verwenden und dadurch bei beschädigten Daten die Anwendung abbrechen lassen
- Daten speichern, aber beim Start nicht wieder laden
- den DOM direkt ändern, ohne den Datenbestand zu aktualisieren
- mehrere Schlüssel uneinheitlich verwenden und dadurch alte Datenbestände verlieren

---

## Testcheckliste

| Test | Erwartetes Ergebnis |
| --- | --- |
| Artikel hinzufügen und Seite neu laden | Artikel ist weiterhin vorhanden |
| Artikel löschen und Seite neu laden | Artikel bleibt gelöscht |
| mehrere Artikel speichern | alle Einträge werden wieder geladen |
| Browserdaten löschen | Anwendung startet mit leerer Liste |
| leere Eingabe testen | keine leeren Daten werden gespeichert |

---

## Was wurde gegenüber dem vorherigen Stand verbessert?

- Daten sind nicht mehr nur temporär im DOM vorhanden.
- Die Anwendung kann aus gespeicherten Daten neu aufgebaut werden.
- Der Code nähert sich einem echten Datenfluss: laden, ändern, speichern, rendern.

---

## Grenzen dieser Version

- weiterhin nur eine Liste
- keine strukturierten Artikeldaten
- keine Synchronisation zwischen Geräten
- keine serverseitige Datenhaltung

---

## Ausblick auf den nächsten Teil

Teil 03 erweitert einzelne Artikel um Menge, Kategorie und Status. Dadurch wird aus einfachem Text ein kleines Datenmodell.

---

## Einordnung für die Praxis

Dieser Teil ist ein Lernschritt, kein fertiges Produkt. Genau darin liegt der Wert der Reihe: Jeder Stand ist klein genug, um verstanden zu werden, aber konkret genug, um später erweitert zu werden.

In einem professionellen Umfeld würde man zusätzlich auf saubere Release-Stände, automatisierte Tests, Konfigurationsbeispiele, Datenmigrationen, Deployment-Dokumentation und eine klare Trennung zwischen Demo- und Produktivbetrieb achten. Die Tutorialreihe führt diese Themen schrittweise ein, ohne den Einstieg unnötig zu überladen.

---

## Navigation

[← Teil 01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [README / Übersicht](../README.md) | [Teil 03 – Mengen, Kategorien und Status →](teil-03-kategorien-mengen-status.md)
