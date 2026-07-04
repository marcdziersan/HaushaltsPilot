# HaushaltsPilot

**HaushaltsPilot** ist eine fortlaufende Lern- und Tutorialreihe, die aus einer einfachen Einkaufsliste Schritt für Schritt eine vollständige Haushalts- und Familienanwendung entwickelt.

Die Reihe beginnt bewusst klein mit HTML, CSS und JavaScript. Danach folgen lokale Speicherung, strukturierte Daten, mehrere Listen, PHP-Backend, JSON-Dateien, konfigurierbare Speicherung, SQLite/MySQL, Login, Rollen, persönliche Listen, Haushalte und Gemeinschaftslisten.

Der didaktische Kern lautet:

> Erst verstehen, dann erweitern. Erst die Mechanik, dann die Komfortschicht.

Die Reihe richtet sich an Lernende, die Webentwicklung praktisch verstehen wollen, ohne direkt in Frameworks, Buildsysteme oder abstrakte Toolketten gedrückt zu werden. Das Projekt zeigt, wie aus nachvollziehbaren Einzelschritten eine echte Anwendung entsteht.

---

## Was macht diese Reihe professionell?

- Jede Version ist ein nachvollziehbarer Entwicklungsstand.
- Jede Dokumentation erklärt nicht nur **was** geändert wurde, sondern auch **warum, weshalb und wieso**.
- Jede Stufe enthält Pro und Kontra, Sicherheitsaspekte, typische Fehlerquellen und Testideen.
- Der Quelltext bleibt bewusst nah an den Grundlagen: HTML, CSS, JavaScript, PHP, JSON, SQLite/MySQL.
- Die Anwendung wächst fachlich sinnvoll: Liste → Speicherung → Datenmodell → Backend → Login → Rechte → Haushalt.

---

<!-- tutorial-overview:start -->

## Tutorial-Navigation

Die erste Hälfte der Reihe ist bis Teil 10 ausgearbeitet. Jede Dokumentation ist mit dem passenden Quelltext-Ordner verlinkt.

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [Einfache Einkaufsliste](docs/teil-01-einfache-einkaufsliste.md) | [versions/01-simple-shopping-list](versions/01-simple-shopping-list/) | Artikel hinzufügen und löschen |
| 02 | [Einkaufsliste mit LocalStorage](docs/teil-02-localstorage.md) | [versions/02-localstorage](versions/02-localstorage/) | Speicherung im Browser |
| 03 | [Mengen, Kategorien und Status](docs/teil-03-kategorien-mengen-status.md) | [versions/03-categories-status](versions/03-categories-status/) | strukturierte Artikeldaten |
| 04 | [Mehrere Einzellisten](docs/teil-04-mehrere-einzellisten.md) | [versions/04-multiple-lists](versions/04-multiple-lists/) | mehrere getrennte Listen |
| 05 | [PHP-JSON-Backend](docs/teil-05-php-json-backend.md) | [versions/05-json-backend](versions/05-json-backend/) | serverseitige JSON-Speicherung |
| 06 | [Konfigurierbare Speicherung](docs/teil-06-konfigurierbare-speicherung.md) | [versions/06-configurable-storage](versions/06-configurable-storage/) | JSON, SQLite oder MySQL/MariaDB |
| 07 | [Login, Benutzer und Rollen](docs/teil-07-login-benutzer-rollen.md) | [versions/07-login-roles](versions/07-login-roles/) | Registrierung, Login, Sessions und Rollen |
| 08 | [Persönliche und gemeinsame Listen](docs/teil-08-persoenliche-und-gemeinsame-listen.md) | [versions/08-personal-shared-lists](versions/08-personal-shared-lists/) | private und gemeinsame Listenrechte |
| 09 | [Familien und Haushalte](docs/teil-09-familien-und-haushalte.md) | [versions/09-families-households](versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | [Gemeinschaftslisten und Admin-Tabs](docs/teil-10-gemeinschaftslisten-und-admin-tabs.md) | [versions/10-shared-lists-admin-tabs](versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |

## Geplante Fortsetzung

| Teil | Thema | Ergebnis |
| ---: | --- | --- |
| 11 | Todos | persönliche und gemeinsame Aufgaben |
| 12 | Personal Messages | private Nachrichten zwischen Nutzern |
| 13 | Einzelchat | direkter 1:1-Chat |
| 14 | Familienchat | gemeinsamer Chat für den Haushalt |
| 15 | Persönlicher Kalender | eigene Termine |
| 16 | Familienkalender | gemeinsame Familientermine |
| 17 | Dashboard | Listen, Todos, Nachrichten und Termine zusammenführen |
| 18 | Rechte & Sicherheit | Rollen, CSRF, XSS-Schutz, Zugriffskontrolle vertiefen |
| 19 | Export & Backup | JSON/CSV-Export und Sicherungslogik |
| 20 | Abschlussversion | bereinigte Finalversion mit Release-Dokumentation |

<!-- tutorial-overview:end -->

---

## Inhaltlicher Aufbau

```txt
HaushaltsPilot
├── Einkauf
│   ├── persönliche Listen
│   └── Gemeinschaftslisten
├── Todos
│   ├── eigene Aufgaben
│   └── Familienaufgaben
├── Nachrichten
│   ├── private Nachrichten
│   ├── Einzelchat
│   └── Familienchat
├── Kalender
│   ├── persönlicher Kalender
│   └── Familienkalender
├── Benutzer
│   ├── Login
│   ├── Registrierung
│   └── Profil
└── Familie
    ├── Haushaltsgruppe
    ├── Mitglieder
    └── Rollen
```

---

## Empfohlene Nutzung

Für Lernende ist die beste Reihenfolge:

1. Dokumentation eines Teils lesen.
2. Quelltext des passenden Versionsordners öffnen.
3. Anwendung lokal starten und testen.
4. Die Testcheckliste aus dem Kapitel durchgehen.
5. Erst danach zum nächsten Teil wechseln.

Wer die Reihe nur überfliegt, sieht viele einzelne Dateien. Wer sie der Reihe nach durcharbeitet, erkennt den roten Faden: Jede technische Entscheidung entsteht aus einem echten Problem des vorherigen Standes.

---

## Technischer Stil

HaushaltsPilot setzt bewusst auf klassische Webtechnologien:

- HTML5
- CSS3
- JavaScript ohne Framework
- PHP 8
- JSON
- SQLite
- MySQL/MariaDB über PDO

Das Ziel ist kein Anti-Framework-Statement. Das Ziel ist Grundlagenverständnis. Wer verstanden hat, wie Formular, DOM, API, Session, Datenbank und Rechteprüfung zusammenarbeiten, kann später Frameworks wesentlich besser einordnen.

---

## Status

Die erste Tutorialhälfte bis Teil 10 bildet eine solide Basis für eine Haushaltsanwendung mit Benutzerkonten, Rollen, persönlichen Listen, Haushalten und Gemeinschaftslisten. Die geplanten Teile 11 bis 20 bauen darauf weitere Module wie Todos, Nachrichten, Chat, Kalender, Dashboard, Export und Finalisierung auf.
