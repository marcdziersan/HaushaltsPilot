# Teil 07 – Login, Benutzer und Rollen

Teil 07 ergänzt Benutzerkonten, Registrierung, Login, Logout, Sessions und ein einfaches Rollenmodell mit Admin und Nutzer.

> **Rolle in der Reihe:** Dieser Teil macht aus der Anwendung erstmals ein Mehrbenutzersystem. Ab hier geht es nicht nur darum, Daten zu speichern, sondern zu entscheiden, wer welche Daten sehen und verändern darf.

<!-- tutorial-nav:start -->

## Tutorial-Navigation

**Aktueller Teil:** Teil 07 – Login, Benutzer und Rollen  
**Quelltext zu diesem Teil:** [versions/07-login-roles](../versions/07-login-roles/)

[← Teil 06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [README / Übersicht](../README.md) | [Teil 08 – Persönliche und gemeinsame Listen →](teil-08-persoenliche-und-gemeinsame-listen.md)

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [01 – Einfache Einkaufsliste](teil-01-einfache-einkaufsliste.md) | [Version 01](../versions/01-simple-shopping-list/) | Artikel hinzufügen und löschen |
| 02 | [02 – Einkaufsliste mit LocalStorage](teil-02-localstorage.md) | [Version 02](../versions/02-localstorage/) | Speicherung im Browser |
| 03 | [03 – Mengen, Kategorien und Status](teil-03-kategorien-mengen-status.md) | [Version 03](../versions/03-categories-status/) | strukturierte Artikeldaten |
| 04 | [04 – Mehrere Einzellisten](teil-04-mehrere-einzellisten.md) | [Version 04](../versions/04-multiple-lists/) | mehrere getrennte Listen |
| 05 | [05 – PHP-JSON-Backend](teil-05-php-json-backend.md) | [Version 05](../versions/05-json-backend/) | serverseitige JSON-Speicherung |
| 06 | [06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [Version 06](../versions/06-configurable-storage/) | JSON, SQLite oder MySQL/MariaDB |
| 07 | **[07 – Login, Benutzer und Rollen](teil-07-login-benutzer-rollen.md)** | **[Version 07](../versions/07-login-roles/)** | Registrierung, Login, Sessions und Rollen |
| 08 | [08 – Persönliche und gemeinsame Listen](teil-08-persoenliche-und-gemeinsame-listen.md) | [Version 08](../versions/08-personal-shared-lists/) | private und gemeinsame Listenrechte |
| 09 | [09 – Familien und Haushalte](teil-09-familien-und-haushalte.md) | [Version 09](../versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | [10 – Gemeinschaftslisten und Admin-Tabs](teil-10-gemeinschaftslisten-und-admin-tabs.md) | [Version 10](../versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |
| 11 | [11 – Todos](teil-11-todos.md) | [Version 11](../versions/11-todos/) | persönliche und gemeinsame Aufgaben |

<!-- tutorial-nav:end -->

---

## Ziel dieses Kapitels

Am Ende dieses Teils kann die Anwendung:

- Benutzer registrieren
- Benutzer anmelden und abmelden
- Passwörter sicher hashen
- Sessions verwenden
- zwischen Admin und Nutzer unterscheiden
- Adminfunktionen für Benutzerverwaltung bereitstellen
- Installer um Benutzer- und Rollentabellen erweitern

---

## Warum, weshalb, wieso dieser Schritt?

Eine Haushaltsanwendung ohne Login kann keine persönlichen Daten schützen und keine Verantwortlichkeiten abbilden.
Rollen werden jetzt eingeführt, weil spätere Funktionen wie private Listen, Gemeinschaftslisten, Haushalte und Adminbereiche darauf aufbauen.
Der Schritt ist bewusst einfach gehalten: Zwei Rollen reichen, um Rechteprüfung verständlich zu machen, ohne direkt ein komplexes Berechtigungssystem zu bauen.

Der didaktische Gedanke der Reihe bleibt dabei gleich: Jede neue Funktion löst ein konkretes Problem des vorherigen Standes. Dadurch entsteht keine Sammlung isolierter Codebeispiele, sondern eine fortlaufende Anwendung mit wachsender fachlicher und technischer Tiefe.

---

## Ausgangspunkt aus dem vorherigen Teil

- Die Anwendung hat jetzt Benutzerkonten.
- Sessions bilden angemeldete Nutzer ab.
- Admin- und Nutzerrechte werden unterschieden.
- Der Installer berücksichtigt die neue Benutzerbasis.

Dieser Ausgangspunkt bestimmt, warum die Erweiterung in diesem Kapitel sinnvoll ist und welche Grenzen weiterhin bewusst stehen bleiben.

---

## Fachliche Grundlagen

- **Authentifizierung** beantwortet die Frage: Wer ist angemeldet?
- **Autorisierung** beantwortet die Frage: Was darf diese Person tun?
- **Session** hält den angemeldeten Zustand serverseitig zwischen Requests fest.
- **Passwort-Hashing** speichert keine Klartextpasswörter, sondern sichere Hashwerte.
- **Rollenmodell** trennt Adminrechte von normalen Nutzerrechten.

---

## Technische Umsetzung im Überblick

- `login.php` enthält Formulare für Anmeldung und Registrierung.
- `auth.php` verarbeitet Login, Registrierung und Logout.
- `api.php` prüft angemeldete Nutzer und Rollen vor geschützten Aktionen.
- `storage.php` speichert nun zusätzlich Benutzerinformationen.
- Adminaktionen sind u. a. `admin_update_user_role`, `admin_toggle_user_active` und `admin_delete_user`.
- Der Installer kann die Datenbasis neu erstellen und einen administrativen Startzustand vorbereiten.

### Projektstand nach Teil 07

```txt
versions/
└── 07-login-roles/
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

Authentifizierung wird als eigene Schicht behandelt und nicht in die Oberfläche gemischt.
Die API wird zur Schutzgrenze: Auch wenn die Oberfläche Buttons versteckt, muss das Backend Rechte prüfen.
Das Rollenmodell ist klein, aber tragfähig. Es reicht für die nächsten Kapitel und bleibt für Lernende nachvollziehbar.

**Wichtig:** Die Reihe bleibt bewusst ohne Framework-Overkill. Ziel ist nicht, ein modernes Framework zu umgehen, sondern zuerst die Grundmechanik einer Webanwendung zu verstehen: Datenmodell, Oberfläche, Anfrage, Antwort, Speicherung, Validierung und Rechteprüfung.

---

## Pro und Kontra

### Vorteile

- echtes Mehrbenutzerfundament
- Passwörter werden nicht im Klartext gespeichert
- Admin- und Nutzerbereiche können getrennt werden
- spätere Rechteprüfung wird vorbereitet
- Installer erleichtert Neuaufbau der Datenbasis

### Nachteile

- Login erhöht die Komplexität deutlich
- Sessionfehler können schwerer zu debuggen sein
- Rollenmodell ist noch bewusst grob
- noch keine Haushalts- oder Besitzerlogik für Listen
- kein Passwort-Reset und keine E-Mail-Verifikation

Diese Nachteile sind in diesem Kapitel nicht automatisch Fehler. Viele davon sind bewusste Zwischenstände, die in späteren Teilen gezielt aufgelöst werden.

---

## Sicherheits- und Qualitätsaspekte

- Passwörter werden mit PHP-Mechanismen wie `password_hash()` gespeichert.
- Sessions müssen gegen Fixation und unbefugte Nutzung geschützt werden.
- Schreibende Aktionen bleiben CSRF-geschützt.
- Adminrechte werden serverseitig geprüft. UI-Ausblendung allein ist keine Sicherheit.
- Registrierung kann über Konfiguration erlaubt oder gesperrt werden.

---

## Typische Fehlerquellen

- Rollen nur im Frontend prüfen
- Passwörter selbst verschlüsseln statt Hashfunktionen zu nutzen
- inaktive Benutzer weiterhin API-Aktionen ausführen lassen
- Logout nur optisch durchführen, aber Session nicht zerstören
- Installer nach der Einrichtung ungeschützt erreichbar lassen

---

## Testcheckliste

| Test | Erwartetes Ergebnis |
| --- | --- |
| neuen Nutzer registrieren | Nutzer kann sich anmelden |
| falsches Passwort verwenden | Login wird abgelehnt |
| Logout ausführen | geschützte Seite ist nicht mehr erreichbar |
| Admin ändert Rolle | Nutzer erhält neue Rechte |
| Admin deaktiviert Nutzer | Nutzer kann nicht mehr sinnvoll arbeiten |
| normaler Nutzer ruft Adminaktion auf | Backend lehnt Zugriff ab |

---

## Was wurde gegenüber dem vorherigen Stand verbessert?

- Die Anwendung hat jetzt Benutzerkonten.
- Sessions bilden angemeldete Nutzer ab.
- Admin- und Nutzerrechte werden unterschieden.
- Der Installer berücksichtigt die neue Benutzerbasis.

---

## Grenzen dieser Version

- Listen gehören noch nicht konsequent einzelnen Nutzern
- keine Haushalte oder Familien
- kein Einladungssystem
- kein Passwort-Reset
- keine feingranularen Berechtigungen pro Liste

---

## Ausblick auf den nächsten Teil

Teil 08 nutzt das Login-Fundament, um persönliche und gemeinsame Listen sauber voneinander zu trennen.

---

## Einordnung für die Praxis

Dieser Teil ist ein Lernschritt, kein fertiges Produkt. Genau darin liegt der Wert der Reihe: Jeder Stand ist klein genug, um verstanden zu werden, aber konkret genug, um später erweitert zu werden.

In einem professionellen Umfeld würde man zusätzlich auf saubere Release-Stände, automatisierte Tests, Konfigurationsbeispiele, Datenmigrationen, Deployment-Dokumentation und eine klare Trennung zwischen Demo- und Produktivbetrieb achten. Die Tutorialreihe führt diese Themen schrittweise ein, ohne den Einstieg unnötig zu überladen.

---

## Navigation

[← Teil 06 – Konfigurierbare Speicherung](teil-06-konfigurierbare-speicherung.md) | [README / Übersicht](../README.md) | [Teil 08 – Persönliche und gemeinsame Listen →](teil-08-persoenliche-und-gemeinsame-listen.md)
