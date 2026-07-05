# Teil 15 – Persönlicher Kalender: eigene Termine und Familientermine

Teil 15 erweitert HaushaltsPilot um einen eigengebauten Kalender. Die Oberfläche orientiert sich bewusst an FullCalendar: Monatsansicht, Vor-/Zurück-Navigation, Heute-Schalter, Termine als farbige Chips und ein Bearbeitungsdialog. Technisch bleibt die Umsetzung aber vollständig frameworkfrei und passt damit zum Stil der Tutorialreihe.

In diesem Kapitel werden nicht nur persönliche Termine eingeführt. Weil der Kalender fachlich direkt zum Haushalt gehört, unterstützt Teil 15 bereits auch gemeinsame Familientermine.

---

## Ziel dieses Kapitels

In diesem Kapitel wird HaushaltsPilot um folgende Funktionen erweitert:

- eigener Menüpunkt **Kalender**,
- FullCalendar-inspirierte Monatsansicht im Eigenbau,
- persönliche Termine,
- gemeinsame Familientermine,
- Termin anlegen,
- Termin bearbeiten,
- Termin löschen,
- Modal-Dialog für Terminbearbeitung,
- Terminliste für anstehende Termine,
- Felder für Titel, Beginn, Ende, Ort und Beschreibung,
- serverseitige Rechteprüfung über Besitzer und Haushaltszuordnung,
- Speicherung für JSON, SQLite und MySQL/MariaDB.

Das Ziel ist eine stabile Kalenderbasis, die später in Dashboard, Erinnerungen, Export und weitere Haushaltsfunktionen eingebunden werden kann.

---

## Warum wird der Kalender jetzt umgesetzt?

Die Reihenfolge ist bewusst gewählt. Ein Kalender benötigt mehrere Grundlagen, die bereits vorhanden sind:

- Benutzerkonten und Sessions aus Teil 07,
- persönliche und gemeinsame Daten aus Teil 08 bis Teil 10,
- Haushaltszuordnung aus Teil 09,
- Aufgaben und Fälligkeitsdaten aus Teil 11,
- Dashboard-Shell aus Teil 12,
- Kommunikationsmodule aus Teil 12 bis Teil 14.

Erst mit diesen Grundlagen kann ein Kalender sinnvoll zwischen privaten und gemeinsamen Terminen unterscheiden.

---

## Ausgangssituation

Vor Teil 15 besitzt HaushaltsPilot bereits:

- eine modulare Sidebar-Navigation,
- ein Dashboard,
- persönliche Listen,
- Gemeinschaftslisten,
- persönliche Aufgaben,
- Familienaufgaben,
- private Nachrichten,
- 1:1-Chats,
- Familienchat.

Was bisher fehlt, ist ein eigenständiger Terminbereich. Teil 15 schließt diese Lücke.

---

## Fachliche Entscheidung: private Termine und Familientermine

Der Kalender unterscheidet zwei Terminarten:

| Bereich | Sichtbarkeit | Rechtebasis |
| --- | --- | --- |
| Eigener Termin | nur Besitzer | `ownerId` |
| Familientermin | alle Mitglieder des Haushalts | `familyId` |

Dadurch bleibt das bestehende Haushaltsmodell konsistent. Es gibt keine frei definierbaren Teilnehmerlisten. Ein Familientermin gehört dem Haushalt, nicht einer einzelnen Chatgruppe.

---

## Technische Umsetzung

Das neue Datenmodell heißt:

```txt
calendarEvents
```

Ein Termin enthält:

```txt
id
ownerId
familyId
scope
title
startsAt
endsAt
location
description
createdAt
updatedAt
```

Die API wurde um drei Aktionen erweitert:

```txt
create_calendar_event
update_calendar_event
delete_calendar_event
```

Damit ist vollständiges CRUD vorhanden.

---

## Architekturentscheidung

Der Kalender wird nicht als externe JavaScript-Bibliothek eingebunden. Stattdessen wird die Monatsansicht selbst berechnet:

- aktueller Monat,
- Starttag der Kalenderwoche,
- 42 Tageszellen für eine stabile 6-Wochen-Ansicht,
- Gruppierung der Termine nach Datum,
- farbliche Unterscheidung zwischen privaten Terminen und Familienterminen.

Das ist didaktisch sinnvoll, weil die Lernreihe zeigen soll, wie ein Kalender grundsätzlich funktioniert.

---

## Pro

- eigener Kalender ohne externe Abhängigkeiten,
- FullCalendar-ähnliche Bedienung,
- private Termine und Familientermine in einem Modul,
- vollständiges CRUD,
- Modal-Dialog für Bearbeitung,
- gute Vorbereitung für Dashboard-Zusammenfassung,
- Rechteprüfung serverseitig,
- Speicherung in JSON, SQLite und MySQL/MariaDB.

---

## Kontra

- noch keine Wiederholungstermine,
- noch keine Erinnerungen per Push oder Mail,
- noch keine Einladung einzelner Nutzer,
- noch keine Drag-and-Drop-Verschiebung,
- noch keine Wochen- oder Tagesansicht,
- noch keine Exportfunktion nach iCal/ICS,
- noch keine Konfliktprüfung.

Diese Grenzen sind passend für den Lernstand. Die Basis ist bewusst nachvollziehbar und kann später vertieft werden.

---

## Sicherheitsaspekte

Teil 15 achtet besonders auf:

- CSRF-Schutz bei Terminaktionen,
- serverseitige Validierung von Titel, Datum, Ort und Beschreibung,
- Prüfung, ob das Enddatum nicht vor dem Startdatum liegt,
- private Termine nur für Besitzer,
- Familientermine nur für Mitglieder desselben Haushalts,
- sichere Textausgabe im Frontend,
- keine Speicherung von ungeprüftem HTML.

---

## Typische Fehlerquellen

Bei Kalendern entstehen schnell typische Fehler:

- Termine werden nur im Frontend gefiltert,
- private Termine werden anderen Nutzern angezeigt,
- Familientermine sind ohne Haushaltsprüfung sichtbar,
- Ende liegt vor Beginn,
- Datum aus `datetime-local` wird nicht sauber normalisiert,
- Monatsansicht beginnt am falschen Wochentag,
- Termine außerhalb des aktuellen Monats verschwinden vollständig,
- Modal speichert falsche Termin-ID.

Teil 15 vermeidet diese Fehler durch serverseitige Prüfung und eine klare Trennung von Rendering, API und Datenmodell.

---

## Testcheckliste

Nach der Installation sollten mindestens diese Punkte geprüft werden:

- persönlichen Termin anlegen,
- Familientermin anlegen,
- Termin im Kalender anklicken und bearbeiten,
- Termin löschen,
- Termin mit Ort und Beschreibung speichern,
- Ende vor Beginn testen,
- Nutzer ohne Haushalt testen und Familientermin verweigern lassen,
- zweiten Nutzer im selben Haushalt anlegen und Familientermin prüfen,
- Nutzer in anderen Haushalt verschieben und Sichtbarkeit prüfen,
- Monatsnavigation vor/zurück testen,
- Heute-Schalter testen,
- HTML-Zeichen im Titel oder in der Beschreibung testen.

---

## Grenzen dieser Version

Teil 15 ist ein vollständiger CRUD-Kalender, aber noch kein produktives Kalender-System:

- keine Wiederholungen,
- keine Erinnerungen,
- keine Zeitzonenlogik,
- keine Feiertage,
- keine Einladungen,
- keine Kalenderfreigaben außerhalb des Haushalts,
- kein ICS-Export,
- keine Drag-and-Drop-Bedienung.

Das ist bewusst so gehalten, damit der Kern sauber nachvollziehbar bleibt.

---

## Ausblick auf den nächsten Teil

Teil 16 vertieft den Kalender direkt weiter. Dort werden die bisher bewusst offenen Punkte ausgebaut:

- Wiederholungstermine,
- einfache Erinnerungslogik,
- Verknüpfung von Todos mit Kalenderterminen,
- Kalenderfilter,
- Wochenansicht.

Da Teil 15 bereits persönliche und gemeinsame Familientermine enthält, kann Teil 16 fachlich als Kalender-Vertiefung statt als reiner Erstkontakt mit dem Familienkalender genutzt werden.

---

## Einordnung für die Praxis

Produktiv würde man zusätzlich über iCal/ICS, Zeitzonen, Einladungen, Benachrichtigungen, Löschfristen, Änderungsverlauf und Konfliktprüfung nachdenken. Für diese Tutorialreihe ist die aktuelle Umsetzung jedoch passend: Sie zeigt die Kernmechanik eines Kalenders und integriert sie sauber in die bestehende Haushaltslogik.

---

## Navigation

[← Teil 14 – Familienchat](teil-14-familienchat.md) | [README / Übersicht](../README.md) | [Teil 16 – Kalender vertiefen →](teil-16-kalender-vertiefung.md)
