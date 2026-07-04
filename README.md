# HaushaltsPilot

ist eine mehrteilige Lern- und Tutorial-Reihe, die zeigt, wie aus einer einfachen Einkaufsliste Schritt für Schritt eine vollständige Familien- und Haushaltsanwendung entsteht. Beginnend mit HTML, CSS und JavaScript werden später PHP, JSON, Datenbankanbindung, Benutzerlogin, persönliche Listen, Gemeinschaftslisten, Todos, private Nachrichten, Einzelchat, Familienchat sowie persönliche und gemeinsame Kalenderfunktionen ergänzt. Der Fokus liegt auf nachvollziehbarer Entwicklung ohne Framework-Overkill.

## Planung

| Teil | Thema                      | Ergebnis                               |
| ---: | -------------------------- | -------------------------------------- |
|   01 | Einfache Einkaufsliste     | Artikel hinzufügen/löschen             |
|   02 | Speicherung im Browser     | LocalStorage                           |
|   03 | Mengen, Kategorien, Status | bessere Einkaufsliste                  |
|   04 | Mehrere Einzellisten       | Einkauf, Haushalt, Schule usw.         |
|   05 | PHP + JSON                 | serverseitige Speicherung              |
|   06 | Datenbank                  | SQLite/MySQL statt JSON                |
|   07 | Login & Benutzer           | Registrierung, Login, Sessions         |
|   08 | Persönliche Listen         | jeder Nutzer hat eigene Listen         |
|   09 | Familien/Haushalte         | Nutzer werden einer Familie zugeordnet |
|   10 | Gemeinschaftslisten        | gemeinsame Einkaufs-/Haushaltslisten   |
|   11 | Todos                      | Aufgaben für sich selbst oder Familie  |
|   12 | Personal Messages          | private Nachrichten zwischen Nutzern   |
|   13 | Einzelchat                 | direkter 1:1-Chat                      |
|   14 | Familienchat               | gemeinsamer Chat für den Haushalt      |
|   15 | Persönlicher Kalender      | eigene Termine                         |
|   16 | Familienkalender           | gemeinsame Familientermine             |
|   17 | Dashboard                  | Listen, Todos, Nachrichten, Termine    |
|   18 | Rechte & Sicherheit        | Rollen, CSRF, XSS-Schutz usw.          |
|   19 | Export & Backup            | JSON/CSV-Export                        |
|   20 | Abschlussversion           | saubere Finalversion                   |

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

v0.1.0-simple-shopping-list
v0.2.0-localstorage
v0.3.0-categories-status
v0.4.0-multiple-lists
v0.5.0-json-backend
v0.6.0-database
v0.7.0-login
v0.8.0-personal-lists
v0.9.0-family-groups
v1.0.0-shared-lists
v1.1.0-todos
v1.2.0-personal-messages
v1.3.0-direct-chat
v1.4.0-family-chat
v1.5.0-personal-calendar
v1.6.0-family-calendar
v1.7.0-dashboard
v1.8.0-security-cleanup
v1.9.0-export-backup
v2.0.0-final
