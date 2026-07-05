# HaushaltsPilot

**HaushaltsPilot** ist eine fortlaufende Lern- und Tutorialreihe, die aus einer einfachen Einkaufsliste Schritt für Schritt eine vollständige Haushalts- und Familienanwendung entwickelt.

Die Reihe beginnt bewusst klein mit HTML, CSS und JavaScript. Danach folgen lokale Speicherung, strukturierte Daten, mehrere Listen, PHP-Backend, JSON-Dateien, konfigurierbare Speicherung, SQLite/MySQL, Login, Rollen, persönliche Listen, Haushalte, Gemeinschaftslisten und ab Teil 11 vertiefte persönliche sowie gemeinsame Todos. Teil 12 ergänzt private Nachrichten zwischen Nutzern und führt eine AdminLTE-inspirierte Dashboard-Shell ein. Teil 13 baut darauf einen direkten 1:1-Chat mit eigener Chatliste, schwebendem Chatfenster unten rechts, Emoji-Auswahl, Ungelesen-Badges, automatischer Hintergrund-Aktualisierung, Tippanzeige sowie Zustell- und Gelesen-Häkchen. Teil 14 ergänzt den gemeinsamen Familienchat pro Haushalt mit eigener Badge-Funktion und Rechteprüfung über die Haushaltszuordnung.

Der didaktische Kern lautet:

> Erst verstehen, dann erweitern. Erst die Mechanik, dann die Komfortschicht.

Die Reihe richtet sich an Lernende, die Webentwicklung praktisch verstehen wollen, ohne direkt in Frameworks, Buildsysteme oder abstrakte Toolketten gedrückt zu werden. Das Projekt zeigt, wie aus nachvollziehbaren Einzelschritten eine echte Anwendung entsteht.

---

## Was macht diese Reihe professionell?

- Jede Version ist ein nachvollziehbarer Entwicklungsstand.
- Jede Dokumentation erklärt nicht nur **was** geändert wurde, sondern auch **warum, weshalb und wieso**.
- Jede Stufe enthält Pro und Kontra, Sicherheitsaspekte, typische Fehlerquellen und Testideen.
- Der Quelltext bleibt bewusst nah an den Grundlagen: HTML, CSS, JavaScript, PHP, JSON, SQLite/MySQL.
- Die Anwendung wächst fachlich sinnvoll: Liste → Speicherung → Datenmodell → Backend → Login → Rechte → Haushalt → Todos → private Nachrichten → 1:1-Chat → Familienchat → modulare Dashboard-Oberfläche.

---

<!-- tutorial-overview:start -->

## Tutorial-Navigation

Die Reihe ist bis Teil 14 ausgearbeitet. Jede Dokumentation ist mit dem passenden Quelltext-Ordner verlinkt.

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
| 11 | [Todos](docs/teil-11-todos.md) | [versions/11-todos](versions/11-todos/) | vertiefte persönliche und gemeinsame Aufgaben |
| 12 | [Personal Messages](docs/teil-12-personal-messages.md) | [versions/12-personal-messages](versions/12-personal-messages/) | private Nachrichten, Ungelesen-Badges und AdminLTE-inspirierte Dashboard-Shell |
| 13 | [Einzelchat](docs/teil-13-einzelchat.md) | [versions/13-one-to-one-chat](versions/13-one-to-one-chat/) | direkter 1:1-Chat mit Mini-Chat, Emoji, Hintergrund-Refresh, Tippanzeige und Gelesen-Häkchen |
| 14 | [Familienchat](docs/teil-14-familienchat.md) | [versions/14-family-chat](versions/14-family-chat/) | gemeinsamer Chat pro Haushalt mit Gruppen-Badge, Tippanzeige und Rechteprüfung |

## Geplante Fortsetzung

| Teil | Thema | Ergebnis |
| ---: | --- | --- |
| 15 | Persönlicher Kalender | eigene Termine |
| 16 | Familienkalender | gemeinsame Familientermine |
| 17 | Dashboard | Dashboard fachlich mit Kalender, Nachrichten, Todos und Terminen final zusammenführen |
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

## Hinweise zur Veröffentlichung

Für eine öffentliche Veröffentlichung auf GitHub empfiehlt sich zusätzlich:

- `config.example.php` statt produktiv wirkender Beispielkonfigurationen
- keine echten oder dauerhaft wirkenden App-Keys im Repository
- klare Installationshinweise pro Version
- optional Demo-Daten für schnelle Tests
- Changelog oder Release-Notizen
- Lizenzdatei, falls das Projekt öffentlich weiterverwendet werden darf
- Hinweis, dass spätere Versionen mit Installer und Datenbank arbeiten

---

## Status

Die Tutorialreihe ist bis Teil 14 umgesetzt. Die Anwendung besitzt Benutzerkonten, Rollen, persönliche Listen, Haushalte, Gemeinschaftslisten, persönliche und gemeinsame Todos, private Nachrichten zwischen Nutzern, einen direkten 1:1-Chat sowie einen gemeinsamen Familienchat pro Haushalt. Private Nachrichten, Einzelchats und Familienchat sind fachlich und technisch getrennt. Zusätzlich wurde die Oberfläche ab Teil 12 auf eine modulare Dashboard-Shell im AdminLTE-Stil umgestellt: Sidebar, Topbar, Kennzahlen, Modulnavigation und ein eigener Admin-Arbeitsbereich. Teil 17 bleibt trotzdem sinnvoll, weil dort später Kalender, Chat, Termine und Zusammenfassungen fachlich zu einem vollständigen Dashboard verdichtet werden.


### Korrektur: Trennung von Nachrichten und Chats

Teil 13 trennt private Nachrichten und direkte 1:1-Chats nun technisch sauber voneinander. Klassische Personal Messages verwenden eigene Nachrichtenverläufe, während Chatverläufe einen separaten Thread-Typ besitzen. Dadurch erscheinen Chatnachrichten nicht mehr im Nachrichtenmodul und normale private Nachrichten nicht mehr im Chatmodul. Die Badges werden getrennt berechnet.


### Abschluss Teil 13: Live-Komfort ohne WebSocket

Teil 13 besitzt jetzt zusätzlich eine automatische Hintergrund-Aktualisierung für private Nachrichten und Chats. Offene Verläufe werden beim Nachladen als gelesen markiert. Der Chat zeigt eine einfache Tippanzeige sowie Zustell- und Gelesen-Häkchen für eigene Nachrichten. Die Lösung bleibt bewusst ohne WebSockets und ohne externe JavaScript-Bibliotheken, damit der technische Lernpfad weiterhin nachvollziehbar bleibt.


### Teil 14: Familienchat pro Haushalt

Teil 14 erweitert das Kommunikationsmodell um einen gemeinsamen Gruppenchat für jeden Haushalt. Die Teilnehmer ergeben sich automatisch aus den aktiven Haushaltsmitgliedern. Der Familienchat besitzt einen eigenen Thread-Typ, einen eigenen Sidebar-Badge und eine serverseitige Rechteprüfung über die Haushaltszuordnung. Dadurch bleiben klassische private Nachrichten, direkte 1:1-Chats und der Familienchat sauber voneinander getrennt.

- Logout bleibt auch bei laufender Hintergrund-Aktualisierung stabil.
