# Teil 10 – Gemeinschaftslisten und Admin-Tabs

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 10 – Gemeinschaftslisten und Admin-Tabs  
**Quelltext zu diesem Teil:** [versions/10-shared-lists-admin-tabs](../versions/10-shared-lists-admin-tabs/)

[← Teil 09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [README / Übersicht](../README.md) | kein nächster Teil →

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [Version 01](../versions/01-simple-shopping-list/) | Artikel hinzufügen/löschen |
| 02 | [02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [Version 02](../versions/02-localstorage/) | Speicherung im Browser |
| 03 | [03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [Version 03](../versions/03-categories-status/) | strukturierte Artikeldaten |
| 04 | [04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md) | [Version 04](../versions/04-multiple-lists/) | mehrere getrennte Listen |
| 05 | [05 – PHP-JSON-Backend](teil-05-php-json-backend.md) | [Version 05](../versions/05-json-backend/) | serverseitige JSON-Speicherung |
| 06 | [06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [Version 06](../versions/06-configurable-storage/) | JSON, SQLite oder MySQL/MariaDB |
| 07 | [07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md) | [Version 07](../versions/07-login-roles/) | Registrierung, Login, Sessions, Rollen |
| 08 | [08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md) | [Version 08](../versions/08-personal-shared-lists/) | private und gemeinsame Listenrechte |
| 09 | [09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [Version 09](../versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | **[10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md)** | **[Version 10](../versions/10-shared-lists-admin-tabs/)** | vertiefte Gemeinschaftslisten und Admin-Tabs |

<!-- tutorial-nav:end -->
In Teil 09 wurden Familien und Haushalte eingeführt. Dadurch waren Gemeinschaftslisten nicht mehr global, sondern an einen Haushalt gebunden.

Teil 10 vertieft diese Gemeinschaftslisten.

Bisher war eine Gemeinschaftsliste einfach nur eine geteilte Liste. Jetzt bekommt jede Liste zusätzlich einen fachlichen Typ.

---

## Ziel dieses Tutorials

Am Ende dieses Teils kann die Anwendung:

- gemeinsame Einkaufslisten unterscheiden
- gemeinsame Haushaltslisten unterscheiden
- sonstige gemeinsame Listen führen
- Listentypen speichern
- Listentypen ändern
- Gemeinschaftslisten nach Typ gruppiert anzeigen
- Admin-Bereiche in Tabs anzeigen
- Admin-Listen nach Typ filtern

---

## Neue Datenstruktur

Eine Liste besitzt jetzt zusätzlich `listType`.

```js
{
    id: "list_123",
    ownerId: "user_123",
    familyId: "family_123",
    name: "Wocheneinkauf",
    listType: "shopping",
    isShared: true,
    items: [],
    createdAt: "2026-07-04 20:00:00"
}
```

Erlaubte Listentypen:

| Wert | Bedeutung |
|---|---|
| `shopping` | Einkaufsliste |
| `household` | Haushaltsliste |
| `other` | sonstige Liste |

---

## Warum Listentypen?

Ohne Typ sieht jede Liste gleich aus. Für eine Haushalts-App ist das auf Dauer unübersichtlich.

Ein Haushalt kann zum Beispiel gleichzeitig haben:

- Wocheneinkauf
- Drogerie
- Putzplan
- Reparaturen
- Schulbedarf
- Sonstiges

Mit `listType` kann die Oberfläche diese Listen sauber gruppieren.

---

## Gemeinschaftslisten in Teil 10

Gemeinschaftslisten bleiben weiterhin haushaltsbezogen.

Ein normaler Nutzer sieht:

- eigene private Listen
- eigene freigegebene Listen
- gemeinsame Einkaufslisten des eigenen Haushalts
- gemeinsame Haushaltslisten des eigenen Haushalts
- sonstige gemeinsame Listen des eigenen Haushalts

Ein Nutzer sieht keine Gemeinschaftslisten fremder Haushalte.

---

## Admin-Tabs

Die Admin-Verwaltung war inzwischen zu voll für eine einzige Ansicht. Deshalb wird sie in Tabs getrennt.

Tabs:

- Haushalte
- Benutzer
- Listen

Das ist keine neue Sicherheitsgrenze. Die Rechteprüfung bleibt weiterhin im Backend. Die Tabs verbessern nur die Bedienbarkeit.

---

## Neue API-Aktion

```txt
update_list_type
```

Diese Aktion ändert den Typ einer Liste.

Nur Besitzer oder Admins dürfen den Typ einer Liste ändern.

---

## Sicherheit

Die Sicherheitsbasis bleibt erhalten:

- CSRF-Token für POST-Aktionen
- serverseitige Rollenprüfung
- serverseitige Validierung
- feste erlaubte Listentypen
- keine freie SQL-Verkettung mit Benutzereingaben
- PDO Prepared Statements bei SQLite/MySQL
- sichere Textausgabe im Frontend mit `textContent`

---

## Testfälle

| Test | Erwartetes Ergebnis |
|---|---|
| Einkaufsliste erstellen | Liste erscheint als Einkaufsliste |
| Haushaltsliste erstellen | Liste erscheint als Haushaltsliste |
| Liste gemeinsam freigeben | Liste erscheint im passenden Gemeinschaftsbereich |
| Listentyp ändern | Liste wandert in die passende Gruppe |
| Nutzer aus anderem Haushalt | sieht diese Gemeinschaftsliste nicht |
| Admin öffnet Verwaltung | sieht Tabs für Haushalte, Benutzer, Listen |
| Admin filtert Listen nach Typ | nur passende Listen werden angezeigt |
| ungültiger Listentyp per API | Anfrage wird abgelehnt |

---

## Nächster Schritt

Teil 11 kann jetzt sauber Todos ergänzen.

Dabei kann dieselbe Logik genutzt werden:

- eigene Todos
- Haushalts-Todos
- spätere Zuweisung an Benutzer
- Status offen/erledigt
- Fälligkeit
