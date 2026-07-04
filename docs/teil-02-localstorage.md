# Teil 02 – Einkaufsliste mit LocalStorage

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 02 – Einkaufsliste mit LocalStorage  
**Quelltext zu diesem Teil:** [versions/02-localstorage](../versions/02-localstorage/)

[← Teil 01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [README / Übersicht](../README.md) | [Teil 03 – Mengen, Kategorien und Status →](teil-03-kategorien-mengen-status.md)

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [Version 01](../versions/01-simple-shopping-list/) | Artikel hinzufügen/löschen |
| 02 | **[02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md)** | **[Version 02](../versions/02-localstorage/)** | Speicherung im Browser |
| 03 | [03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [Version 03](../versions/03-categories-status/) | strukturierte Artikeldaten |
| 04 | [04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md) | [Version 04](../versions/04-multiple-lists/) | mehrere getrennte Listen |
| 05 | [05 – PHP-JSON-Backend](teil-05-php-json-backend.md) | [Version 05](../versions/05-json-backend/) | serverseitige JSON-Speicherung |
| 06 | [06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [Version 06](../versions/06-configurable-storage/) | JSON, SQLite oder MySQL/MariaDB |
| 07 | [07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md) | [Version 07](../versions/07-login-roles/) | Registrierung, Login, Sessions, Rollen |
| 08 | [08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md) | [Version 08](../versions/08-personal-shared-lists/) | private und gemeinsame Listenrechte |
| 09 | [09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [Version 09](../versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | [10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [Version 10](../versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |

<!-- tutorial-nav:end -->
In Teil 01 haben wir eine einfache Einkaufsliste gebaut. Die Liste funktionierte bereits im Browser, hatte aber noch einen entscheidenden Nachteil: Nach dem Neuladen der Seite waren alle Einträge verschwunden.

In diesem zweiten Teil erweitern wir die Anwendung um `localStorage`.

Damit werden die Einträge direkt im Browser gespeichert und beim nächsten Öffnen der Seite wieder geladen.

---

## Ziel dieses Tutorials

Am Ende dieses Teils kann die Anwendung:

* Artikel hinzufügen
* Artikel löschen
* Artikel dauerhaft im Browser speichern
* gespeicherte Artikel beim Laden der Seite wieder anzeigen
* die komplette Liste zurücksetzen
* ungültige oder leere Eingaben verhindern
* beschädigte gespeicherte Daten abfangen

---

## Lernziele

In diesem Teil lernst du:

* was `localStorage` ist
* wie Daten im Browser gespeichert werden
* warum JavaScript-Objekte vor dem Speichern in JSON umgewandelt werden müssen
* wie gespeicherte JSON-Daten wieder geladen werden
* wie man einfache Fehler beim Laden abfängt
* wie man eine Liste vollständig zurücksetzt

---

## Was ist localStorage?

`localStorage` ist ein Speicherbereich im Browser.

Dort können einfache Daten dauerhaft gespeichert werden. Dauerhaft bedeutet in diesem Fall: Die Daten bleiben auch erhalten, wenn die Seite neu geladen oder der Browser geschlossen wird.

Die Daten bleiben gespeichert, bis sie aktiv gelöscht werden.

Typische Einsatzfälle:

* kleine Einstellungen
* Darkmode-Auswahl
* einfache Listen
* lokale Entwürfe
* Lernprojekte ohne Backend

Wichtig: `localStorage` ist keine Datenbank und kein sicherer Speicherort für sensible Daten.

Passwörter, private Nachrichten, Gesundheitsdaten oder geheime Informationen gehören dort nicht hinein.

---

## Projektstand nach Teil 02

```txt id="sysfv6"
versions/
├── 01-simple-shopping-list/
│   └── index.html
└── 02-localstorage/
    └── index.html
```

---

## Unterschied zu Teil 01

In Teil 01 lag die Einkaufsliste nur in einem JavaScript-Array:

```js id="l30y08"
let items = [];
```

Sobald die Seite neu geladen wurde, war dieses Array wieder leer.

In Teil 02 speichern wir das Array zusätzlich im Browser:

```js id="gojs6h"
localStorage.setItem("shoppingItems", JSON.stringify(items));
```

Beim Start der Anwendung laden wir die Daten wieder:

```js id="ahrgpn"
const savedItems = localStorage.getItem("shoppingItems");
items = JSON.parse(savedItems);
```

---

## Neue Funktionen in Teil 02

### 1. Automatisches Speichern

Nach jeder Änderung wird die Liste gespeichert:

* nach dem Hinzufügen eines Artikels
* nach dem Löschen eines Artikels
* nach dem Zurücksetzen der gesamten Liste

---

### 2. Automatisches Laden

Beim Öffnen der Seite prüft die Anwendung, ob bereits gespeicherte Artikel vorhanden sind.

Wenn ja, werden sie wieder angezeigt.

---

### 3. Liste vollständig zurücksetzen

Ein neuer Button löscht alle Einträge aus der Liste und entfernt die gespeicherten Daten aus dem Browser.

---

### 4. Fehlerbehandlung

Wenn im Browser beschädigte Daten gespeichert sind, soll die Anwendung nicht abstürzen.

Deshalb wird beim Laden ein `try...catch` verwendet.

---

## Testfälle

| Test                       | Erwartetes Ergebnis                                        |
| -------------------------- | ---------------------------------------------------------- |
| Artikel hinzufügen         | Artikel erscheint in der Liste                             |
| Seite neu laden            | Artikel bleibt sichtbar                                    |
| Artikel löschen            | Artikel wird entfernt und bleibt auch nach Reload entfernt |
| Mehrere Artikel hinzufügen | Alle Artikel bleiben gespeichert                           |
| Liste zurücksetzen         | Alle Artikel werden gelöscht                               |
| Seite nach Reset neu laden | Liste bleibt leer                                          |
| Leere Eingabe abschicken   | Kein Artikel wird hinzugefügt                              |

---

## Warum noch kein Backend?

In diesem Teil geht es bewusst nur um Speicherung im Browser.

Ein Backend mit PHP, JSON oder Datenbank folgt später. Der Vorteil dieser Reihenfolge ist, dass die Grundlogik zuerst einfach bleibt.

Wir lernen also zuerst:

1. Daten im Array verwalten
2. Daten im Browser speichern
3. später Daten an ein Backend senden
4. danach Daten in einer Datenbank speichern

---

## Einschränkungen von localStorage

`localStorage` hat klare Grenzen:

* Daten gelten nur für diesen Browser
* Daten werden nicht zwischen Geräten synchronisiert
* Daten sind nicht geschützt
* andere Benutzer auf demselben Gerät könnten sie sehen
* für echte Benutzerkonten ist ein Backend notwendig

Für dieses Tutorial ist `localStorage` aber ideal, weil man ohne Server sofort versteht, wie Speichern und Laden grundsätzlich funktioniert.

---

## Nächster Schritt

In Teil 03 erweitern wir die Einkaufsliste um:

* Mengen
* Kategorien
* Status offen/erledigt
* bessere Datenstruktur mit Objekten statt einfachen Textwerten

Aus dieser einfachen Liste wird dann langsam eine praktischere Anwendung.
