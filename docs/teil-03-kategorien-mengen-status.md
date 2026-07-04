# Teil 03 – Mengen, Kategorien und Status

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 03 – Mengen, Kategorien und Status  
**Quelltext zu diesem Teil:** [versions/03-categories-status](../versions/03-categories-status/)

[← Teil 02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [README / Übersicht](../README.md) | [Teil 04 – Mehrere Einzellisten →](teil-04-mehrere-einzellisten.md)

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [Version 01](../versions/01-simple-shopping-list/) | Artikel hinzufügen/löschen |
| 02 | [02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [Version 02](../versions/02-localstorage/) | Speicherung im Browser |
| 03 | **[03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md)** | **[Version 03](../versions/03-categories-status/)** | strukturierte Artikeldaten |
| 04 | [04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md) | [Version 04](../versions/04-multiple-lists/) | mehrere getrennte Listen |
| 05 | [05 – PHP-JSON-Backend](teil-05-php-json-backend.md) | [Version 05](../versions/05-json-backend/) | serverseitige JSON-Speicherung |
| 06 | [06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [Version 06](../versions/06-configurable-storage/) | JSON, SQLite oder MySQL/MariaDB |
| 07 | [07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md) | [Version 07](../versions/07-login-roles/) | Registrierung, Login, Sessions, Rollen |
| 08 | [08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md) | [Version 08](../versions/08-personal-shared-lists/) | private und gemeinsame Listenrechte |
| 09 | [09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [Version 09](../versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | [10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [Version 10](../versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |

<!-- tutorial-nav:end -->
In Teil 01 haben wir eine einfache Einkaufsliste gebaut.
In Teil 02 wurde diese Liste mit `localStorage` im Browser gespeichert.

In Teil 03 erweitern wir die Datenstruktur.

Bisher bestand ein Eintrag nur aus einfachem Text:

```js
"Milch"
```

Jetzt wird jeder Eintrag zu einem Objekt:

```js
{
    id: "item-123",
    name: "Milch",
    amount: "2 Liter",
    category: "Lebensmittel",
    done: false
}
```

Dadurch kann die Einkaufsliste deutlich mehr Informationen speichern und anzeigen.

---

## Ziel dieses Tutorials

Am Ende dieses Teils kann die Anwendung:

* Artikel mit Namen hinzufügen
* Mengenangaben speichern
* Kategorien speichern
* Artikel als erledigt markieren
* erledigte Artikel wieder auf offen setzen
* Artikel löschen
* Artikel im Browser speichern
* gespeicherte Artikel beim Laden wiederherstellen
* offene und erledigte Artikel zählen
* die komplette Liste zurücksetzen

---

## Lernziele

In diesem Teil lernst du:

* warum Objekte besser sind als einfache Textwerte
* wie man strukturierte Daten in JavaScript speichert
* wie man Artikel mit mehreren Eigenschaften verwaltet
* wie man Checkboxen oder Buttons für Statusänderungen nutzt
* wie man erledigte Einträge optisch unterscheidet
* wie man Daten als JSON im `localStorage` speichert
* wie man einfache Statistiken berechnet

---

## Projektstand nach Teil 03

```txt
versions/
├── 01-simple-shopping-list/
│   └── index.html
├── 02-localstorage/
│   └── index.html
└── 03-categories-status/
    └── index.html
```

---

## Unterschied zu Teil 02

In Teil 02 bestand die Liste aus einfachen Textwerten:

```js
let items = ["Milch", "Brot", "Käse"];
```

Das ist für den Anfang gut, wird aber schnell zu wenig.

Wir können damit nicht sauber speichern:

* wie viel gekauft werden soll
* zu welcher Kategorie der Artikel gehört
* ob der Artikel bereits erledigt ist

Darum wird die Liste in Teil 03 auf Objekte umgestellt:

```js
let items = [
    {
        id: "item-1710000000000",
        name: "Milch",
        amount: "2 Liter",
        category: "Lebensmittel",
        done: false
    }
];
```

---

## Neue Eigenschaften pro Artikel

| Eigenschaft | Bedeutung                                         |
| ----------- | ------------------------------------------------- |
| `id`        | eindeutige Kennung für den Artikel                |
| `name`      | Name des Artikels                                 |
| `amount`    | Menge, z. B. `2 Liter`, `1 Packung`, `500 g`      |
| `category`  | Kategorie, z. B. Lebensmittel, Drogerie, Haushalt |
| `done`      | Status: erledigt oder offen                       |

---

## Warum eine ID?

In den ersten beiden Teilen haben wir Artikel über ihre Position im Array gelöscht.

Das funktioniert am Anfang, ist aber später unpraktisch.

Wenn Artikel sortiert, gefiltert oder bearbeitet werden, ist eine feste ID besser.

Beispiel:

```js
{
    id: "item-1710000000000",
    name: "Milch",
    amount: "2 Liter",
    category: "Lebensmittel",
    done: false
}
```

Mit dieser ID kann ein Artikel eindeutig gefunden, gelöscht oder geändert werden.

---

## Neue Funktionen in Teil 03

### 1. Menge pro Artikel

Beim Hinzufügen kann eine Menge angegeben werden.

Beispiele:

* `2 Liter`
* `1 Packung`
* `500 g`
* `3 Stück`
* `nach Bedarf`

---

### 2. Kategorie pro Artikel

Jeder Artikel bekommt eine Kategorie.

Beispiele:

* Lebensmittel
* Getränke
* Haushalt
* Drogerie
* Sonstiges

Das ist später wichtig, wenn wir filtern, sortieren oder mehrere Listen verwalten wollen.

---

### 3. Status offen/erledigt

Ein Artikel kann als erledigt markiert werden.

Dadurch wird sichtbar:

* was noch gekauft werden muss
* was bereits erledigt wurde

---

### 4. Statistik

Die Anwendung zählt jetzt:

* alle Artikel
* offene Artikel
* erledigte Artikel

---

## Testfälle

| Test                                       | Erwartetes Ergebnis                      |
| ------------------------------------------ | ---------------------------------------- |
| Artikel mit Menge und Kategorie hinzufügen | Artikel erscheint mit allen Angaben      |
| Artikel ohne Menge hinzufügen              | Artikel wird trotzdem hinzugefügt        |
| Leeren Artikelnamen absenden               | Artikel wird nicht hinzugefügt           |
| Artikel als erledigt markieren             | Artikel wird durchgestrichen dargestellt |
| Erledigten Artikel wieder öffnen           | Artikel ist wieder normal sichtbar       |
| Artikel löschen                            | Artikel verschwindet dauerhaft           |
| Seite neu laden                            | Alle Artikel bleiben erhalten            |
| Liste zurücksetzen                         | Alle Artikel werden gelöscht             |
| Mehrere Artikel erledigen                  | Statistik wird korrekt aktualisiert      |

---

## Warum noch keine mehreren Listen?

In Teil 03 verbessern wir bewusst nur die Struktur einzelner Artikel.

Mehrere Listen kommen im nächsten Teil.

Das ist sinnvoll, weil mehrere Listen nur sauber funktionieren, wenn die einzelnen Einträge bereits strukturiert aufgebaut sind.

---

## Nächster Schritt

In Teil 04 erweitern wir die Anwendung um mehrere Einzellisten.

Dann kann man zum Beispiel getrennte Listen nutzen für:

* Einkauf
* Baumarkt
* Haushalt
* Schule
* Medikamente
* Sonstiges
