# Teil 10 – Gemeinschaftslisten und Admin-Tabs

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
