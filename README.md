# HaushaltsPilot

ist eine mehrteilige Lern- und Tutorial-Reihe, die zeigt, wie aus einer einfachen Einkaufsliste Schritt für Schritt eine vollständige Familien- und Haushaltsanwendung entsteht. Beginnend mit HTML, CSS und JavaScript werden später PHP, JSON, Datenbankanbindung, Benutzerlogin, persönliche Listen, Gemeinschaftslisten, Todos, private Nachrichten, Einzelchat, Familienchat sowie persönliche und gemeinsame Kalenderfunktionen ergänzt. Der Fokus liegt auf nachvollziehbarer Entwicklung ohne Framework-Overkill.

<!-- tutorial-overview:start -->

## Tutorial-Navigation

Die erste Hälfte der Reihe ist bis Teil 10 ausgearbeitet. Jede Dokumentation ist mit dem passenden Quelltext-Ordner verlinkt.

| Teil | Dokumentation | Quelltext | Ergebnis |
| ---: | --- | --- | --- |
| 01 | [Einfache Einkaufsliste](docs/teil-01-einfache-einkaufsliste.md) | [versions/01-simple-shopping-list](versions/01-simple-shopping-list/) | Artikel hinzufügen/löschen |
| 02 | [Einkaufsliste mit LocalStorage](docs/teil-02-localstorage.md) | [versions/02-localstorage](versions/02-localstorage/) | Speicherung im Browser |
| 03 | [Mengen, Kategorien und Status](docs/teil-03-kategorien-mengen-status.md) | [versions/03-categories-status](versions/03-categories-status/) | strukturierte Artikeldaten |
| 04 | [Mehrere Einzellisten](docs/teil-04-mehrere-einzellisten.md) | [versions/04-multiple-lists](versions/04-multiple-lists/) | mehrere getrennte Listen |
| 05 | [PHP-JSON-Backend](docs/teil-05-php-json-backend.md) | [versions/05-json-backend](versions/05-json-backend/) | serverseitige JSON-Speicherung |
| 06 | [Konfigurierbare Speicherung](docs/teil-06-konfigurierbare-speicherung.md) | [versions/06-configurable-storage](versions/06-configurable-storage/) | JSON, SQLite oder MySQL/MariaDB |
| 07 | [Login, Benutzer und Rollen](docs/teil-07-login-benutzer-rollen.md) | [versions/07-login-roles](versions/07-login-roles/) | Registrierung, Login, Sessions, Rollen |
| 08 | [Persönliche und gemeinsame Listen](docs/teil-08-persoenliche-und-gemeinsame-listen.md) | [versions/08-personal-shared-lists](versions/08-personal-shared-lists/) | private und gemeinsame Listenrechte |
| 09 | [Familien und Haushalte](docs/teil-09-familien-und-haushalte.md) | [versions/09-families-households](versions/09-families-households/) | Haushaltszuordnung für Nutzer |
| 10 | [Gemeinschaftslisten und Admin-Tabs](docs/teil-10-gemeinschaftslisten-und-admin-tabs.md) | [versions/10-shared-lists-admin-tabs](versions/10-shared-lists-admin-tabs/) | vertiefte Gemeinschaftslisten und Admin-Tabs |

## Geplante Fortsetzung

| Teil | Thema | Ergebnis |
| ---: | --- | --- |
| 11 | Todos | Aufgaben für sich selbst oder Familie |
| 12 | Personal Messages | private Nachrichten zwischen Nutzern |
| 13 | Einzelchat | direkter 1:1-Chat |
| 14 | Familienchat | gemeinsamer Chat für den Haushalt |
| 15 | Persönlicher Kalender | eigene Termine |
| 16 | Familienkalender | gemeinsame Familientermine |
| 17 | Dashboard | Listen, Todos, Nachrichten, Termine |
| 18 | Rechte & Sicherheit | Rollen, CSRF, XSS-Schutz usw. |
| 19 | Export & Backup | JSON/CSV-Export |
| 20 | Abschlussversion | saubere Finalversion |

<!-- tutorial-overview:end -->
<pre>
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
</pre>
