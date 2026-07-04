# Teil 08 – Persönliche und gemeinsame Listen

In Teil 07 wurden Login, Registrierung, Sessions und zwei Rollen eingeführt:

- Admin
- Nutzer

Teil 08 nutzt diese Grundlage und macht daraus ein saubereres Listensystem.

Jeder Nutzer hat eigene Listen. Zusätzlich gibt es Gemeinschaftslisten, die für alle angemeldeten Nutzer sichtbar und inhaltlich bearbeitbar sind.

---

## Ziel dieses Tutorials

Am Ende dieses Teils kann die Anwendung:

- persönliche Listen getrennt anzeigen
- Gemeinschaftslisten getrennt anzeigen
- Besitzer einer Liste anzeigen
- Ersteller eines Artikels anzeigen
- Listen privat oder gemeinsam schalten
- Listenbesitzer durch Admin ändern
- Listen nach Sichtbarkeit filtern
- Rechte zwischen Inhaltspflege und Listeneinstellungen unterscheiden

---

## Warum dieser Schritt wichtig ist

In Teil 07 war die technische Basis schon vorhanden:

```txt
ownerId
isShared
admin/user
```

Teil 08 macht diese Basis sichtbar und sauber bedienbar.

Das ist wichtig, weil spätere Module dieselbe Rechteidee brauchen:

- Todos
- Personal Messages
- Einzelchat
- Familienchat
- Kalender

Eine Todo-Aufgabe, eine Nachricht oder ein Kalendertermin braucht später ebenfalls eine Antwort auf diese Fragen:

- Wem gehört der Eintrag?
- Ist er privat oder gemeinsam?
- Wer darf ihn sehen?
- Wer darf ihn bearbeiten?
- Wer darf die Sichtbarkeit ändern?

---

## Rechte in Teil 08

| Aktion | Admin | Besitzer | Nutzer in Gemeinschaftsliste |
|---|---:|---:|---:|
| Liste sehen | ja | ja | ja, wenn gemeinsam |
| Artikel hinzufügen | ja | ja | ja |
| Artikel erledigen/offen setzen | ja | ja | ja |
| Artikel löschen | ja | ja | ja |
| Liste privat/gemeinsam setzen | ja | ja | nein |
| Liste löschen | ja | ja | nein |
| Besitzer ändern | ja | nein | nein |
| Benutzer verwalten | ja | nein | nein |

Der wichtige Unterschied ist:

```txt
Inhalte verwalten ≠ Listeneinstellungen verwalten
```

Normale Nutzer dürfen Inhalte in Gemeinschaftslisten pflegen. Sie dürfen aber nicht einfach eine Gemeinschaftsliste privat schalten oder löschen, wenn sie nicht Besitzer der Liste sind.

---

## Neue API-Aktionen

### `update_list_visibility`

Ändert eine Liste von privat zu gemeinsam oder zurück.

Erlaubt für:

- Admin
- Besitzer der Liste

Nicht erlaubt für:

- normale Nutzer, die die Gemeinschaftsliste nur mitbenutzen

---

### `admin_update_list_owner`

Ändert den Besitzer einer Liste.

Erlaubt für:

- Admin

Nicht erlaubt für:

- normale Nutzer
- Besitzer selbst ohne Adminrolle

---

## Oberfläche

Die Listenansicht ist jetzt aufgeteilt in:

```txt
Meine Listen
Gemeinschaftslisten
Andere Nutzerlisten          nur Admin
```

Dadurch sieht ein Nutzer sofort, was wirklich ihm gehört und was gemeinsam genutzt wird.

---

## Admin-Bereich

Der Admin-Bereich enthält weiterhin die Benutzerverwaltung.

Neu dazu kommt eine Listenverwaltung mit Filter:

- alle Listen
- meine Listen
- Gemeinschaftslisten
- private Listen

Der Admin kann dort:

- Besitzer ändern
- Liste freigeben
- Liste wieder privat setzen

---

## Sicherheit

Teil 08 übernimmt die Sicherheitsbasis aus Teil 07:

- Passwort-Hashing
- Session-Schutz
- CSRF-Schutz
- serverseitige Validierung
- sichere Textausgabe
- PDO Prepared Statements im Datenbankmodus

Wichtig ist: Die Rechteprüfung findet nicht nur im Frontend statt.

Die API prüft jede Aktion serverseitig.

---

## Nächster Schritt

Teil 09 kann jetzt Familien- oder Haushaltsgruppen einführen.

Dann wird aus einer globalen Gemeinschaftsliste eine echte Gruppenlogik:

```txt
Familie Müller
├── Mitglieder
├── private Listen
└── Gruppenlisten
```

Das ist die bessere Grundlage für Familienchat, Familientermine und gemeinsame Todos.
