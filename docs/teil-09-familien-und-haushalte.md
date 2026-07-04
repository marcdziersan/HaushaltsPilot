# Teil 09 – Familien und Haushalte

In Teil 08 gab es bereits persönliche Listen und gemeinsame Listen. Diese gemeinsamen Listen waren technisch aber noch zu allgemein gedacht.

In Teil 09 führen wir deshalb eine eigene Ebene für Familien und Haushalte ein.

Ab jetzt gilt:

- Ein Benutzer kann einem Haushalt zugeordnet werden.
- Eine private Liste gehört nur dem Besitzer.
- Eine Gemeinschaftsliste gehört zu einem Haushalt.
- Nur Mitglieder desselben Haushalts sehen diese Gemeinschaftsliste.
- Admins sehen und verwalten weiterhin alles.

Damit ist die Grundlage für die nächsten Module deutlich sauberer: Todos, Personal Messages, Einzelchat, Familienchat und Kalender können später ebenfalls zwischen privat und haushaltsbezogen unterscheiden.

---

## Ziel dieses Tutorials

Am Ende dieses Teils kann die Anwendung:

- Haushalte speichern
- Benutzer Haushalten zuordnen
- Benutzer aus Haushalten entfernen
- Listen einem Haushalt zuordnen
- private Listen von Haushaltslisten trennen
- Haushaltslisten nur für Mitglieder desselben Haushalts anzeigen
- Haushalte administrativ erstellen, umbenennen und löschen
- spätere Module auf Haushaltskontext vorbereiten

---

## Neue Datenstruktur

In Teil 08 bestand die Hauptstruktur aus:

```php
[
    'users' => [],
    'lists' => [],
    'activeLists' => []
]
```

In Teil 09 kommt `families` dazu:

```php
[
    'families' => [],
    'users' => [],
    'lists' => [],
    'activeLists' => []
]
```

---

## Familien / Haushalte

Ein Haushalt sieht in JSON vereinfacht so aus:

```json
{
    "id": "family_123",
    "name": "Mein Haushalt",
    "createdBy": "user_123",
    "createdAt": "2026-07-04 20:00:00"
}
```

---

## Benutzer mit Haushaltszuordnung

Benutzer erhalten ein neues Feld:

```json
{
    "id": "user_123",
    "username": "anna",
    "displayName": "Anna",
    "role": "user",
    "active": true,
    "familyId": "family_123"
}
```

Wenn `familyId` leer ist, gehört der Benutzer keinem Haushalt an.

---

## Listen mit Haushaltszuordnung

Listen erhalten ebenfalls ein neues Feld:

```json
{
    "id": "list_123",
    "ownerId": "user_123",
    "familyId": "family_123",
    "name": "Wocheneinkauf",
    "isShared": true,
    "items": []
}
```

Wichtig:

- `ownerId` sagt, wem die Liste gehört.
- `familyId` sagt, zu welchem Haushalt die Liste gehört.
- `isShared` sagt, ob die Liste als Haushaltsliste sichtbar ist.

---

## Sichtbarkeitsregel

Die wichtigste neue Regel lautet:

```txt
Ein Nutzer sieht eine Liste, wenn er Admin ist,
oder wenn er Besitzer der Liste ist,
oder wenn die Liste geteilt ist und zur selben familyId gehört.
```

Damit sind Gemeinschaftslisten nicht mehr global, sondern haushaltsbezogen.

---

## Rechte in Teil 09

### Admin

Admins dürfen:

- alle Haushalte verwalten
- alle Benutzer verwalten
- Benutzer Haushalten zuordnen
- alle Listen sehen
- Listenbesitzer ändern
- Listen privat/gemeinsam schalten

### Nutzer

Nutzer dürfen:

- eigene private Listen verwalten
- eigene Listen als Haushaltsliste freigeben, wenn sie einem Haushalt angehören
- Haushaltslisten ihres Haushalts sehen und bearbeiten
- keine fremden privaten Listen sehen
- keine Benutzer oder Haushalte verwalten

---

## Warum Nutzer ohne Haushalt erlaubt sind

Registrierte Benutzer starten bewusst ohne Haushalt.

Das ist für ein Tutorial sauberer, weil dadurch klar wird:

- Registrierung erstellt nur ein Benutzerkonto.
- Haushaltszuordnung ist eine Admin-Aufgabe.
- Gemeinschaftsdaten werden erst sichtbar, wenn der Nutzer einem Haushalt angehört.

Später kann daraus ein Einladungssystem entstehen.

---

## Auswirkungen auf geteilte Listen

Eine geteilte Liste benötigt einen Haushalt.

Wenn ein Listenbesitzer keinem Haushalt angehört, kann seine Liste nicht als Haushaltsliste freigegeben werden.

Wenn ein Admin einen Benutzer aus einem Haushalt entfernt, werden dessen eigene geteilte Listen automatisch privat gesetzt. So bleiben keine global sichtbaren Gemeinschaftsdaten übrig.

---

## Vorbereitete spätere Module

Das Datenbankschema enthält weiterhin vorbereitete Tabellen für spätere Module.

Diese Tabellen wurden in Teil 09 ebenfalls um Haushaltsbezug vorbereitet:

- `message_threads` mit `family_id`
- `todos` mit `family_id`
- `calendar_events` mit `family_id`

Damit können spätere Module unterscheiden zwischen:

- privat
- direkt zwischen Benutzern
- haushaltsbezogen

---

## Testfälle

| Test | Erwartetes Ergebnis |
|---|---|
| Admin erstellt Haushalt | Haushalt erscheint in der Admin-Tabelle |
| Admin ordnet Nutzer Haushalt zu | Nutzer sieht danach Haushaltslisten dieses Haushalts |
| Nutzer ohne Haushalt erstellt Liste | Liste ist privat |
| Nutzer ohne Haushalt will Liste teilen | Aktion wird abgelehnt |
| Nutzer mit Haushalt teilt Liste | Liste wird Haushaltsliste |
| Anderes Haushaltsmitglied meldet sich an | Haushaltsliste ist sichtbar |
| Nutzer aus anderem Haushalt meldet sich an | Haushaltsliste ist nicht sichtbar |
| Admin löscht Haushalt | Nutzer werden ohne Haushalt gesetzt, betroffene Listen werden privat |
| Admin ändert Listenbesitzer | Liste übernimmt Haushalt des neuen Besitzers |

---

## Nächster Schritt

Teil 10 kann jetzt die Gemeinschaftslisten weiter ausbauen.

Sinnvolle Erweiterungen:

- explizite Haushaltsübersicht für normale Nutzer
- Haushaltsmitglieder anzeigen
- gemeinsame Listen besser gruppieren
- später Einladungen oder Beitrittscodes
