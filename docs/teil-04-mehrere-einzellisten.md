# Teil 04 – Mehrere Einzellisten

Teil 04 erweitert die Anwendung von einer einzelnen Einkaufsliste zu mehreren getrennten Listen. Damit entsteht erstmals eine übergeordnete Struktur: Listen enthalten Artikel.

> **Rolle in der Reihe:** Dieser Teil ist fachlich entscheidend, weil die spätere Familien- und Gemeinschaftslogik nicht auf einzelnen Artikeln, sondern auf Listen als eigener Entität aufbaut.

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 04 – Mehrere Einzellisten  
**Quelltext zu diesem Teil:** [versions/04-multiple-lists](../versions/04-multiple-lists/)

[← Teil 03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [README / Übersicht](../README.md) | [Teil 05 – PHP-JSON-Backend →](teil-05-php-json-backend.md)

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [Version 01](../versions/01-simple-shopping-list/) | Artikel hinzufügen und löschen |
| 02 | [02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [Version 02](../versions/02-localstorage/) | Speicherung im Browser |
| 03 | [03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [Version 03](../versions/03-categories-status/) | strukturierte Artikeldaten |
| 04 | **[04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md)** | **[Version 04](../versions/04-multiple-lists/)** | mehrere getrennte Listen |
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

- mehrere Listen anlegen
- zwischen Listen wechseln
- Artikel nur der aktiven Liste zuordnen
- Listen löschen
- eine aktive Liste speichern
- den Datenaufbau für spätere Backend-Speicherung vorbereiten

---

## Warum, weshalb, wieso dieser Schritt?

Eine einzelne Liste reicht für eine Demo, aber nicht für eine Haushaltsanwendung. In der Praxis gibt es Wocheneinkauf, Drogerie, Baumarkt, Schulbedarf oder Vorratsliste.
Mit mehreren Listen wird die Anwendung fachlich realistischer und technisch interessanter: Artikel sind nicht mehr global, sondern gehören zu einer bestimmten Liste.
Dieser Schritt muss vor Login und Familienfunktion kommen, weil Rechte später an Listen hängen: Wer besitzt welche Liste, wer darf sie sehen, wer darf sie bearbeiten?

Der didaktische Gedanke der Reihe bleibt dabei gleich: Jede neue Funktion löst ein konkretes Problem des vorherigen Standes. Dadurch entsteht keine Sammlung isolierter Codebeispiele, sondern eine fortlaufende Anwendung mit wachsender fachlicher und technischer Tiefe.

---

## Ausgangspunkt aus dem vorherigen Teil

- Die Anwendung kann mehrere getrennte Listen verwalten.
- Artikel sind nun fachlich einer Liste zugeordnet.
- Die Datenstruktur ist bereit für Backend- und Datenbanklogik.

Dieser Ausgangspunkt bestimmt, warum die Erweiterung in diesem Kapitel sinnvoll ist und welche Grenzen weiterhin bewusst stehen bleiben.

---

## Fachliche Grundlagen

- **Liste als Entität**: Eine Liste hat eine ID, einen Namen und eigene Artikel.
- **Aktive Liste**: Die Oberfläche zeigt immer genau die momentan ausgewählte Liste.
- **Verschachtelte Datenstruktur**: Listen enthalten Arrays von Artikeln.
- **Fachliche Beziehung**: Ein Artikel existiert nicht mehr losgelöst, sondern innerhalb einer Liste.

---

## Technische Umsetzung im Überblick

- Der Datenbestand besteht nun aus mehreren Listenobjekten.
- Jede Liste enthält ihr eigenes `items`-Array.
- Eine `activeListId` merkt sich, welche Liste gerade angezeigt wird.
- Beim Hinzufügen eines Artikels wird zuerst die aktive Liste gesucht, dann wird der Artikel in dieser Liste gespeichert.
- Der komplette Datenbestand wird weiterhin im Browser gespeichert.

### Projektstand nach Teil 04

```txt
versions/
└── 04-multiple-lists/
    └── index.html
```

---

## Architekturentscheidung

Teil 04 führt eine fachliche Hierarchie ein: Anwendung → Listen → Artikel.
Diese Struktur lässt sich später direkt auf JSON-Dateien, SQLite oder MySQL übertragen.
Die aktive Liste ist eine UI-Entscheidung, aber sie muss mitgespeichert werden, damit die Bedienung nach einem Reload konsistent bleibt.

**Wichtig:** Die Reihe bleibt bewusst ohne Framework-Overkill. Ziel ist nicht, ein modernes Framework zu umgehen, sondern zuerst die Grundmechanik einer Webanwendung zu verstehen: Datenmodell, Oberfläche, Anfrage, Antwort, Speicherung, Validierung und Rechteprüfung.

---

## Pro und Kontra

### Vorteile

- deutlich näher an einer echten Haushaltsanwendung
- saubere Vorbereitung für Besitzer- und Rechtekonzepte
- Artikel sind klar einer Liste zugeordnet
- Datenstruktur kann später serverseitig übernommen werden
- mehr Praxiswert für Nutzer

### Nachteile

- verschachtelte Daten erhöhen die Komplexität
- Löschen einer Liste muss sauber behandelt werden
- aktive Liste kann ungültig werden, wenn die Liste gelöscht wird
- ohne Backend weiterhin nicht geräteübergreifend nutzbar

Diese Nachteile sind in diesem Kapitel nicht automatisch Fehler. Viele davon sind bewusste Zwischenstände, die in späteren Teilen gezielt aufgelöst werden.

---

## Sicherheits- und Qualitätsaspekte

- Noch gibt es keine Benutzerrechte. Jede Person am gleichen Browser sieht dieselben lokalen Daten.
- Listennamen und Artikeldaten bleiben Benutzereingaben und müssen weiterhin sicher ausgegeben werden.
- Sicherheit wird hier vor allem als Datenkonsistenz verstanden: keine verwaiste aktive Liste, keine ungültigen Referenzen.

---

## Typische Fehlerquellen

- Artikel versehentlich global statt in der aktiven Liste speichern
- nach dem Löschen einer Liste die `activeListId` nicht aktualisieren
- Listen ohne Namen zulassen
- verschachtelte Daten direkt im DOM suchen, statt im Datenmodell zu arbeiten

---

## Testcheckliste

| Test | Erwartetes Ergebnis |
| --- | --- |
| neue Liste anlegen | Liste erscheint in der Listenauswahl |
| zwischen Listen wechseln | jeweils passende Artikel werden angezeigt |
| Artikel in Liste A anlegen | Artikel erscheint nicht in Liste B |
| aktive Liste löschen | eine andere Liste wird aktiv oder ein Leerzustand erscheint |
| Seite neu laden | Listen und aktive Auswahl bleiben erhalten |

---

## Was wurde gegenüber dem vorherigen Stand verbessert?

- Die Anwendung kann mehrere getrennte Listen verwalten.
- Artikel sind nun fachlich einer Liste zugeordnet.
- Die Datenstruktur ist bereit für Backend- und Datenbanklogik.

---

## Grenzen dieser Version

- keine gemeinsame Nutzung zwischen Geräten oder Personen
- keine Anmeldung
- keine Rechteprüfung
- kein Import/Export

---

## Ausblick auf den nächsten Teil

Teil 05 verschiebt die Speicherung vom Browser auf ein PHP-Backend mit JSON-Datei. Damit entsteht die erste echte Client-Server-Version.

---

## Einordnung für die Praxis

Dieser Teil ist ein Lernschritt, kein fertiges Produkt. Genau darin liegt der Wert der Reihe: Jeder Stand ist klein genug, um verstanden zu werden, aber konkret genug, um später erweitert zu werden.

In einem professionellen Umfeld würde man zusätzlich auf saubere Release-Stände, automatisierte Tests, Konfigurationsbeispiele, Datenmigrationen, Deployment-Dokumentation und eine klare Trennung zwischen Demo- und Produktivbetrieb achten. Die Tutorialreihe führt diese Themen schrittweise ein, ohne den Einstieg unnötig zu überladen.

---

## Navigation

[← Teil 03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [README / Übersicht](../README.md) | [Teil 05 – PHP-JSON-Backend →](teil-05-php-json-backend.md)
