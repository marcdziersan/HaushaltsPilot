# Teil 08 – Persönliche und gemeinsame Listen

Teil 08 ordnet Listen ihren Besitzern zu und unterscheidet private von gemeinsamen Listen. Nutzer sehen nicht mehr automatisch alles.

> **Rolle in der Reihe:** Dieser Teil ist der eigentliche Übergang von Login zu fachlicher Rechteverwaltung. Benutzerkonten allein reichen nicht; Daten müssen auch Besitz und Sichtbarkeit kennen.

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 08 – Persönliche und gemeinsame Listen  
**Quelltext zu diesem Teil:** [versions/08-personal-shared-lists](../versions/08-personal-shared-lists/)

[← Teil 07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md) | [README / Übersicht](../README.md) | [Teil 09 – Familien und Haushalte →](teil-09-familien-und-haushalte.md)

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [Version 01](../versions/01-simple-shopping-list/) | Artikel hinzufügen und löschen |
| 02 | [02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [Version 02](../versions/02-localstorage/) | Speicherung im Browser |
| 03 | [03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [Version 03](../versions/03-categories-status/) | strukturierte Artikeldaten |
| 04 | [04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md) | [Version 04](../versions/04-multiple-lists/) | mehrere getrennte Listen |
| 05 | [05 – PHP-JSON-Backend](teil-05-php-json-backend.md) | [Version 05](../versions/05-json-backend/) | serverseitige JSON-Speicherung |
| 06 | [06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [Version 06](../versions/06-configurable-storage/) | JSON, SQLite oder MySQL/MariaDB |
| 07 | [07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md) | [Version 07](../versions/07-login-roles/) | Registrierung, Login, Sessions und Rollen |
| 08 | **[08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md)** | **[Version 08](../versions/08-personal-shared-lists/)** | private und gemeinsame Listenrechte |
| 09 | [09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [Version 09](../versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | [10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [Version 10](../versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |
| 11 | [11 – Todos](teil-11-todos.md) | [Version 11](../versions/11-todos/) | persönliche und gemeinsame Aufgaben |

<!-- tutorial-nav:end -->

---

## Ziel dieses Kapitels

Am Ende dieses Teils kann die Anwendung:

- Listen einem Besitzer zuordnen
- private Listen nur dem Besitzer anzeigen
- gemeinsame Listen sichtbar machen
- Listenfreigabe ändern
- Adminfunktionen für Listenbesitzer ergänzen
- Backend-Sichtbarkeitsregeln einführen
- Nutzerrechte von Adminrechten trennen

---

## Warum, weshalb, wieso dieser Schritt?

Nach Teil 07 wissen wir, wer angemeldet ist. Jetzt muss die Anwendung daraus fachliche Konsequenzen ziehen: Nicht jede Liste gehört jedem.
Persönliche Listen sind wichtig, weil ein Haushaltssystem sowohl private als auch gemeinsame Bereiche braucht.
Dieser Schritt ist eine Vorstufe zu Familien und Haushalten. Noch sind gemeinsame Listen allgemein gedacht; im nächsten Teil werden sie sauber an einen Haushalt gebunden.

Der didaktische Gedanke der Reihe bleibt dabei gleich: Jede neue Funktion löst ein konkretes Problem des vorherigen Standes. Dadurch entsteht keine Sammlung isolierter Codebeispiele, sondern eine fortlaufende Anwendung mit wachsender fachlicher und technischer Tiefe.

---

## Ausgangspunkt aus dem vorherigen Teil

- Listen besitzen nun einen fachlichen Eigentümer.
- Private und gemeinsame Listen werden unterschieden.
- Adminfunktionen wurden um Listenverwaltung erweitert.
- Die API berücksichtigt Sichtbarkeit und Besitz.

Dieser Ausgangspunkt bestimmt, warum die Erweiterung in diesem Kapitel sinnvoll ist und welche Grenzen weiterhin bewusst stehen bleiben.

---

## Fachliche Grundlagen

- **ownerId**: Jede Liste bekommt einen Besitzer.
- **isShared**: Eine Liste kann privat oder gemeinsam sichtbar sein.
- **Sichtbarkeitsregel**: Das Backend entscheidet, welche Listen ein Nutzer laden darf.
- **Admin-Override**: Admins dürfen weiterhin alle Listen sehen und verwalten.
- **Besitzerwechsel**: Admins können Listen einem anderen Nutzer zuordnen.

---

## Technische Umsetzung im Überblick

- `create_list` erzeugt Listen nun mit Besitzerbezug.
- `load` liefert abhängig vom angemeldeten Nutzer nur erlaubte Listen zurück.
- `update_list_visibility` schaltet eine Liste zwischen privat und gemeinsam um.
- `admin_update_list_owner` erlaubt Admins den Besitzerwechsel.
- Die Oberfläche unterscheidet eigene Listen, freigegebene Listen und Adminlisten.

### Projektstand nach Teil 08

```txt
versions/
└── 08-personal-shared-lists/
    ├── api.php
    ├── auth.php
    ├── config.php
    ├── index.php
    ├── installer.php
    ├── login.php
    ├── data/
    │   └── .htaccess
    └── includes/
        ├── bootstrap.php
        ├── security.php
        └── storage.php
```

---

## Architekturentscheidung

Die wichtigste Entscheidung ist, Sichtbarkeit im Backend zu prüfen. Das Frontend darf unterstützen, aber nicht entscheiden.
Besitz und Sichtbarkeit sind Eigenschaften der Liste. Dadurch lassen sie sich später problemlos um Haushaltsbezug erweitern.
Teil 08 bleibt bewusst noch ohne Familienmodell, damit der Unterschied zwischen persönlicher Liste und allgemeiner Freigabe isoliert verständlich bleibt.

**Wichtig:** Die Reihe bleibt bewusst ohne Framework-Overkill. Ziel ist nicht, ein modernes Framework zu umgehen, sondern zuerst die Grundmechanik einer Webanwendung zu verstehen: Datenmodell, Oberfläche, Anfrage, Antwort, Speicherung, Validierung und Rechteprüfung.

---

## Pro und Kontra

### Vorteile

- fachlich deutlich realistischer
- Benutzer sehen nicht mehr automatisch alle Daten
- Grundlage für Haushaltslisten entsteht
- Adminverwaltung wird praxisnäher
- Rechteprüfung wird zentraler Bestandteil der API

### Nachteile

- gemeinsame Listen sind noch nicht haushaltsgebunden
- Rechteprüfungen müssen konsequent in jeder Aktion erfolgen
- Besitzerwechsel kann Datenzugriff stark verändern
- mehr Randfälle bei gelöschten oder deaktivierten Benutzern

Diese Nachteile sind in diesem Kapitel nicht automatisch Fehler. Viele davon sind bewusste Zwischenstände, die in späteren Teilen gezielt aufgelöst werden.

---

## Sicherheits- und Qualitätsaspekte

- Jede API-Aktion muss prüfen, ob der angemeldete Nutzer Besitzer, Admin oder berechtigter Teilnehmer ist.
- Das Frontend darf keine fremden Listen nur durch ausgeblendete Buttons schützen.
- Adminaktionen benötigen weiterhin Rollenprüfung und CSRF-Schutz.
- Benutzer-IDs aus Requests dürfen nicht ungeprüft übernommen werden.

---

## Typische Fehlerquellen

- `load` filtern, aber `delete_item` oder `toggle_item` nicht prüfen
- Freigabe nur optisch im Frontend speichern
- Besitzerwechsel ohne Prüfung auf gültigen Zielnutzer erlauben
- Adminrechte und Besitzerrechte vermischen
- gelöschte Nutzer als Listenbesitzer zurücklassen, ohne Fallbehandlung

---

## Testcheckliste

| Test | Erwartetes Ergebnis |
| --- | --- |
| Nutzer A erstellt private Liste | Nutzer B sieht sie nicht |
| Nutzer A gibt Liste frei | Liste erscheint als gemeinsame Liste |
| Nutzer B bearbeitet freigegebene Liste | Änderung wird gespeichert, wenn erlaubt |
| Nutzer B versucht private Liste per API zu löschen | Zugriff wird abgelehnt |
| Admin ändert Besitzer | Liste gehört danach dem neuen Nutzer |
| normaler Nutzer ruft Adminaktion auf | Backend lehnt Zugriff ab |

---

## Was wurde gegenüber dem vorherigen Stand verbessert?

- Listen besitzen nun einen fachlichen Eigentümer.
- Private und gemeinsame Listen werden unterschieden.
- Adminfunktionen wurden um Listenverwaltung erweitert.
- Die API berücksichtigt Sichtbarkeit und Besitz.

---

## Grenzen dieser Version

- Gemeinschaftslisten sind noch nicht an Haushalte gebunden.
- Es gibt noch keine Familien-/Gruppenstruktur.
- Keine Einladung oder Selbstzuordnung zu Gruppen.
- Keine feingranularen Rechte wie nur lesen oder bearbeiten.

---

## Ausblick auf den nächsten Teil

Teil 09 führt Familien und Haushalte ein. Gemeinsame Listen werden dadurch nicht mehr global, sondern haushaltsbezogen.

---

## Einordnung für die Praxis

Dieser Teil ist ein Lernschritt, kein fertiges Produkt. Genau darin liegt der Wert der Reihe: Jeder Stand ist klein genug, um verstanden zu werden, aber konkret genug, um später erweitert zu werden.

In einem professionellen Umfeld würde man zusätzlich auf saubere Release-Stände, automatisierte Tests, Konfigurationsbeispiele, Datenmigrationen, Deployment-Dokumentation und eine klare Trennung zwischen Demo- und Produktivbetrieb achten. Die Tutorialreihe führt diese Themen schrittweise ein, ohne den Einstieg unnötig zu überladen.

---

## Navigation

[← Teil 07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md) | [README / Übersicht](../README.md) | [Teil 09 – Familien und Haushalte →](teil-09-familien-und-haushalte.md)
