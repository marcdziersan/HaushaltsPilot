# Teil 04 – Mehrere Einzellisten

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 04 – Mehrere Einzellisten  
**Quelltext zu diesem Teil:** [versions/04-multiple-lists](../versions/04-multiple-lists/)

[← Teil 03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [README / Übersicht](../README.md) | [Teil 05 – PHP-JSON-Backend →](teil-05-php-json-backend.md)

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [Version 01](../versions/01-simple-shopping-list/) | Artikel hinzufügen/löschen |
| 02 | [02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [Version 02](../versions/02-localstorage/) | Speicherung im Browser |
| 03 | [03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [Version 03](../versions/03-categories-status/) | strukturierte Artikeldaten |
| 04 | **[04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md)** | **[Version 04](../versions/04-multiple-lists/)** | mehrere getrennte Listen |
| 05 | [05 – PHP-JSON-Backend](teil-05-php-json-backend.md) | [Version 05](../versions/05-json-backend/) | serverseitige JSON-Speicherung |
| 06 | [06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [Version 06](../versions/06-configurable-storage/) | JSON, SQLite oder MySQL/MariaDB |
| 07 | [07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md) | [Version 07](../versions/07-login-roles/) | Registrierung, Login, Sessions, Rollen |
| 08 | [08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md) | [Version 08](../versions/08-personal-shared-lists/) | private und gemeinsame Listenrechte |
| 09 | [09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [Version 09](../versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | [10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [Version 10](../versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |

<!-- tutorial-nav:end -->
In Teil 03 hatte unsere Einkaufsliste bereits eine bessere Datenstruktur.
Ein Artikel bestand nicht mehr nur aus einfachem Text, sondern aus einem Objekt mit Name, Menge, Kategorie und Status.

In Teil 04 erweitern wir die Anwendung um mehrere Einzellisten.

Aus einer einzigen Liste wird jetzt ein kleines Listensystem.

Beispiele:

* Einkauf
* Baumarkt
* Haushalt
* Schule
* Medikamente
* Sonstiges

Jede Liste hat ihre eigenen Artikel.

---

## Ziel dieses Tutorials

Am Ende dieses Teils kann die Anwendung:

* mehrere Listen verwalten
* eine neue Liste erstellen
* zwischen Listen wechseln
* die aktive Liste anzeigen
* Artikel nur in der aktiven Liste hinzufügen
* Artikel pro Liste getrennt speichern
* Artikel als erledigt/offen markieren
* Artikel löschen
* einzelne Listen löschen
* alle Daten im Browser speichern
* gespeicherte Listen beim Laden wiederherstellen

---

## Lernziele

In diesem Teil lernst du:

* wie man verschachtelte Datenstrukturen aufbaut
* wie Listen eigene Artikel enthalten
* wie man eine aktive Liste verwaltet
* wie man Daten sauber nach Listen trennt
* wie man mit IDs statt Array-Positionen arbeitet
* wie man komplexere Daten mit `localStorage` speichert
* wie man die Oberfläche abhängig von der aktiven Liste neu rendert

---

## Projektstand nach Teil 04

```txt id="mxxyq9"
versions/
├── 01-simple-shopping-list/
│   └── index.html
├── 02-localstorage/
│   └── index.html
├── 03-categories-status/
│   └── index.html
└── 04-multiple-lists/
    └── index.html
```

---

## Unterschied zu Teil 03

In Teil 03 hatten wir nur ein Array mit Artikeln:

```js id="c23q1z"
let items = [
    {
        id: "item-123",
        name: "Milch",
        amount: "2 Liter",
        category: "Lebensmittel",
        done: false
    }
];
```

Das reicht für eine einzelne Einkaufsliste.

Für mehrere Listen brauchen wir eine neue Struktur:

```js id="zu6shx"
let lists = [
    {
        id: "list-123",
        name: "Einkauf",
        items: [
            {
                id: "item-456",
                name: "Milch",
                amount: "2 Liter",
                category: "Lebensmittel",
                done: false
            }
        ]
    },
    {
        id: "list-789",
        name: "Baumarkt",
        items: [
            {
                id: "item-999",
                name: "Schrauben",
                amount: "1 Packung",
                category: "Haushalt",
                done: false
            }
        ]
    }
];
```

Jede Liste besitzt also ihr eigenes `items`-Array.

---

## Die aktive Liste

Damit die Anwendung weiß, in welche Liste ein neuer Artikel eingefügt werden soll, speichern wir zusätzlich die aktive Liste:

```js id="0kke6z"
let activeListId = "list-123";
```

Wenn der Benutzer auf eine andere Liste klickt, ändert sich diese ID.

Danach wird die Oberfläche neu aufgebaut.

---

## Neue Funktionen in Teil 04

### 1. Neue Liste erstellen

Der Benutzer kann einen Listennamen eingeben und eine neue Liste anlegen.

Beispiele:

* Einkauf
* Baumarkt
* Schule
* Drogerie
* Medikamente

---

### 2. Zwischen Listen wechseln

Jede Liste wird als eigener Button angezeigt.

Beim Klick auf einen Listenbutton wird diese Liste aktiv.

Nur die Artikel dieser aktiven Liste werden angezeigt.

---

### 3. Artikel getrennt speichern

Artikel aus der Liste `Einkauf` landen nicht in der Liste `Baumarkt`.

Jede Liste verwaltet ihre eigenen Artikel.

---

### 4. Aktive Liste löschen

Eine Liste kann gelöscht werden, solange mindestens eine Liste übrig bleibt.

Die letzte vorhandene Liste wird nicht gelöscht. So bleibt die Anwendung immer bedienbar.

---

### 5. Gesamtdaten speichern

In Teil 03 wurde nur das Artikelarray gespeichert.

In Teil 04 speichern wir ein komplettes Datenobjekt:

```js id="wd6o1y"
{
    lists: lists,
    activeListId: activeListId
}
```

So merkt sich die Anwendung auch, welche Liste zuletzt aktiv war.

---

## Testfälle

| Test                     | Erwartetes Ergebnis                          |
| ------------------------ | -------------------------------------------- |
| Neue Liste erstellen     | Liste erscheint in der Listenübersicht       |
| Liste anklicken          | Liste wird aktiv angezeigt                   |
| Artikel hinzufügen       | Artikel landet nur in der aktiven Liste      |
| Zwischen Listen wechseln | Jede Liste zeigt ihre eigenen Artikel        |
| Artikel erledigen        | Status ändert sich nur in dieser Liste       |
| Artikel löschen          | Artikel verschwindet nur aus dieser Liste    |
| Seite neu laden          | Listen und Artikel bleiben erhalten          |
| Aktive Liste löschen     | Liste wird entfernt, andere Liste wird aktiv |
| Letzte Liste löschen     | Wird verhindert                              |
| Alles zurücksetzen       | Standardliste wird wiederhergestellt         |

---

## Warum noch kein Login?

In diesem Teil gibt es noch keine Benutzerkonten.

Alle Listen gehören aktuell nur zu diesem Browser.

Das ist bewusst so. Erst wenn die Listenstruktur sauber funktioniert, lohnt sich der nächste größere Schritt Richtung Backend, PHP, JSON oder Datenbank.

---

## Nächster Schritt

In Teil 05 bauen wir ein einfaches PHP-JSON-Backend.

Dann werden die Daten nicht mehr nur im Browser gespeichert, sondern serverseitig in JSON-Dateien.

Das ist der erste Schritt weg von einer reinen Frontend-Anwendung hin zu einer echten Webanwendung.
