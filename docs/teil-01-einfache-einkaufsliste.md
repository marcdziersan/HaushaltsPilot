# Teil 01 – Einfache Einkaufsliste

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 01 – Einfache Einkaufsliste  
**Quelltext zu diesem Teil:** [versions/01-simple-shopping-list](../versions/01-simple-shopping-list/)

← kein vorheriger Teil | [README / Übersicht](../README.md) | [Teil 02 – Einkaufsliste mit LocalStorage →](teil-02-localstorage.md)

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | **[01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md)** | **[Version 01](../versions/01-simple-shopping-list/)** | Artikel hinzufügen/löschen |
| 02 | [02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [Version 02](../versions/02-localstorage/) | Speicherung im Browser |
| 03 | [03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [Version 03](../versions/03-categories-status/) | strukturierte Artikeldaten |
| 04 | [04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md) | [Version 04](../versions/04-multiple-lists/) | mehrere getrennte Listen |
| 05 | [05 – PHP-JSON-Backend](teil-05-php-json-backend.md) | [Version 05](../versions/05-json-backend/) | serverseitige JSON-Speicherung |
| 06 | [06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [Version 06](../versions/06-configurable-storage/) | JSON, SQLite oder MySQL/MariaDB |
| 07 | [07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md) | [Version 07](../versions/07-login-roles/) | Registrierung, Login, Sessions, Rollen |
| 08 | [08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md) | [Version 08](../versions/08-personal-shared-lists/) | private und gemeinsame Listenrechte |
| 09 | [09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [Version 09](../versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | [10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [Version 10](../versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |

<!-- tutorial-nav:end -->
In diesem ersten Teil bauen wir eine sehr einfache Einkaufsliste mit HTML, CSS und JavaScript.

Die Anwendung läuft vollständig im Browser. Es gibt noch keine Speicherung, keine Datenbank und kein Backend. Nach dem Neuladen der Seite sind die Einträge wieder verschwunden. Das ist in diesem Schritt Absicht, damit der Einstieg möglichst einfach bleibt.

---

## Ziel dieses Tutorials

Am Ende dieses Teils haben wir eine kleine Webanwendung, mit der man:

* einen Artikel eingeben kann
* den Artikel zur Einkaufsliste hinzufügen kann
* alle Artikel untereinander anzeigen kann
* einzelne Artikel wieder löschen kann
* eine leere Eingabe verhindern kann

---

## Lernziele

In diesem Teil lernst du:

* wie eine einfache HTML-Struktur aufgebaut wird
* wie CSS für ein sauberes Layout genutzt wird
* wie JavaScript auf Benutzereingaben reagiert
* wie neue Elemente dynamisch in eine Liste eingefügt werden
* wie man einfache Eingaben prüft
* wie man Listeneinträge wieder entfernt

---

## Projektstand nach Teil 01

Die Anwendung besteht in diesem Schritt nur aus einer Datei:

```txt
versions/
└── 01-simple-shopping-list/
    └── index.html
```

---

## Funktionsumfang

Die erste Version ist bewusst klein gehalten.

Enthaltene Funktionen:

* Artikel hinzufügen
* Artikel per Button löschen
* Eingabefeld nach dem Hinzufügen leeren
* Eingabe per Button oder Enter-Taste
* Zähler für die Anzahl der Artikel
* Hinweis, wenn die Liste leer ist

Noch nicht enthalten:

* Speicherung im Browser
* Mehrere Listen
* Kategorien
* Mengen
* Benutzerlogin
* Familienlisten
* Datenbank
* Backend

Diese Funktionen werden in späteren Teilen ergänzt.

---

## Warum starten wir so einfach?

Viele Projekte werden am Anfang zu groß geplant. Dadurch wird der Einstieg unnötig kompliziert.

In dieser Tutorial-Reihe wächst die Anwendung Schritt für Schritt:

1. Erst eine einfache Liste.
2. Dann Speicherung im Browser.
3. Danach mehrere Listen.
4. Später PHP, JSON und Datenbank.
5. Danach Login, Familienlisten, Todos, Nachrichten und Kalender.

So bleibt jeder Entwicklungsschritt nachvollziehbar.

---

## Die Oberfläche

Die Oberfläche besteht aus:

* einem Titel
* einer kurzen Beschreibung
* einem Eingabefeld
* einem Button zum Hinzufügen
* einer Liste der Artikel
* einem Zähler
* einem Hinweis bei leerer Liste

---

## Die JavaScript-Logik

Die JavaScript-Logik macht drei Dinge:

1. Sie liest den Text aus dem Eingabefeld.
2. Sie erstellt daraus einen neuen Listeneintrag.
3. Sie erlaubt das Löschen einzelner Einträge.

Zusätzlich wird geprüft, ob der Benutzer überhaupt etwas eingegeben hat.

---

## Testfälle

Nach dem Erstellen der Datei kannst du folgende Dinge testen:

| Test                                | Erwartetes Ergebnis                    |
| ----------------------------------- | -------------------------------------- |
| Artikel eingeben und Button klicken | Artikel erscheint in der Liste         |
| Artikel eingeben und Enter drücken  | Artikel erscheint in der Liste         |
| Leere Eingabe abschicken            | Es wird kein Artikel hinzugefügt       |
| Artikel löschen                     | Artikel verschwindet aus der Liste     |
| Alle Artikel löschen                | Hinweis „Die Liste ist leer“ erscheint |
| Mehrere Artikel hinzufügen          | Zähler zeigt die richtige Anzahl       |

---

## Nächster Schritt

Im nächsten Teil erweitern wir die Einkaufsliste um eine Speicherung mit `localStorage`.

Dann bleiben die Artikel auch nach dem Neuladen der Seite erhalten.
