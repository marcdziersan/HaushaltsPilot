# Teil 16 – Kalender vertiefen: Wiederholungen, Erinnerungen, Todo-Verknüpfung, Filter und Wochenansicht

Teil 16 erweitert den Kalender aus Teil 15 von einem einfachen CRUD-Modul zu einem deutlich praxisnäheren Kalenderbereich. Der Kalender bleibt bewusst frameworkfrei, wirkt aber weiterhin wie eine kleine FullCalendar-inspirierte Eigenbau-Lösung innerhalb der HaushaltsPilot-Dashboard-Oberfläche.

---

## Ziel dieses Kapitels

Nach Teil 16 kann HaushaltsPilot:

- persönliche Termine und Familientermine verwalten,
- Termine wiederholen,
- einfache Erinnerungshinweise speichern und anzeigen,
- Kalendertermine mit Todos verknüpfen,
- Termine nach Bereich und Eigenschaften filtern,
- zwischen Monatsansicht und Wochenansicht wechseln,
- alle Kalenderzugriffe weiterhin serverseitig über Besitzer oder Haushalt prüfen.

Damit wird der Kalender nicht nur eingeführt, sondern fachlich vertieft.

---

## Warum dieser Schritt jetzt sinnvoll ist

Teil 15 hat das Grundmodul geschaffen: Termin anlegen, bearbeiten, löschen und anzeigen. In echten Haushalts- oder Familienanwendungen reicht das aber nicht lange. Viele Termine wiederholen sich, manche Termine brauchen eine Erinnerung und Aufgaben stehen oft in direktem Zusammenhang mit Terminen.

Typische Beispiele:

- Mülltonne jede Woche rausstellen,
- Elternabend als Familientermin,
- Arzttermin mit vorbereitender Todo-Aufgabe,
- Einkauf oder Besorgung an einen Termin koppeln,
- nur eigene Termine oder nur Familientermine anzeigen,
- aktuelle Woche statt kompletten Monat prüfen.

Teil 16 bildet genau diese nächste fachliche Stufe ab.

---

## Ausgangssituation

Vor Teil 16 gab es:

- persönliche Termine,
- gemeinsame Familientermine,
- Monatsansicht,
- CRUD über Modal,
- Titel, Beginn, Ende, Ort und Beschreibung,
- Rechteprüfung für private Termine und Familientermine.

Es fehlten aber:

- Wiederholungen,
- Erinnerungen,
- Todo-Verknüpfung,
- Filter,
- Wochenansicht.

---

## Technische Umsetzung

### Neue Kalenderfelder

Kalendertermine wurden um folgende Felder erweitert:

- `recurrence`
- `recurrenceUntil`
- `reminderAt`
- `todoId`

`recurrence` legt fest, ob ein Termin nicht wiederholt, täglich, wöchentlich oder monatlich wiederholt wird. `recurrenceUntil` begrenzt die Wiederholung. `reminderAt` speichert einen einfachen Erinnerungszeitpunkt. `todoId` verknüpft einen Termin optional mit einer sichtbaren Aufgabe.

---

## Wiederholungstermine

Wiederholungstermine werden als ein Termin gespeichert, aber in der Oberfläche als mehrere Vorkommen dargestellt. Dadurch bleibt das Datenmodell einfach und nachvollziehbar.

Unterstützte Wiederholungen:

- keine Wiederholung,
- täglich,
- wöchentlich,
- monatlich.

Die Anzeige berechnet die konkreten Vorkommen für den sichtbaren Zeitraum. In der Monatsansicht werden die Vorkommen für die Kalendertage berechnet. In der Wochenansicht werden nur die Vorkommen der ausgewählten Woche berechnet.

### Warum nicht direkt einzelne Wiederholungstermine speichern?

Man könnte aus einer wöchentlichen Wiederholung sofort zehn einzelne Termine erzeugen. Das wäre einfacher anzuzeigen, aber schwerer zu ändern. Wenn später die Wiederholung angepasst werden soll, müsste man entscheiden, ob nur ein einzelnes Vorkommen oder die ganze Serie geändert wird. Für den Lernstand ist eine gespeicherte Serie mit berechneten Vorkommen sauberer.

---

## Einfache Erinnerungslogik

Teil 16 speichert pro Termin optional einen Erinnerungszeitpunkt. Die Anwendung zeigt fällige oder bald fällige Erinnerungen im Kalenderbereich an.

Wichtig: Das ist noch keine echte Push-, Mail- oder Systembenachrichtigung. Es ist eine sichtbare Erinnerungslogik innerhalb der Oberfläche. Damit bleibt die Funktion verständlich und ohne externe Dienste nutzbar.

---

## Todo-Verknüpfung

Termine können mit sichtbaren Todos verknüpft werden. Dadurch entsteht eine praktische Verbindung zwischen Aufgabenplanung und Kalender.

Beispiel:

- Termin: „Kinderarzt“
- Todo: „Versichertenkarte einpacken“

Die API prüft serverseitig, ob die verknüpfte Aufgabe für den angemeldeten Nutzer sichtbar ist. Ein Nutzer kann also keinen fremden Todo-Datensatz blind an einen Termin koppeln.

---

## Kalenderfilter

Der Kalender besitzt neue Filter:

- alle Bereiche,
- nur eigene Termine,
- nur Familientermine,
- nur Wiederholungen,
- nur Erinnerungen,
- nur Todo-Verknüpfungen,
- Textsuche über Titel, Ort, Beschreibung und verknüpfte Todo-Titel.

Dadurch bleibt der Kalender auch bei mehr Daten übersichtlich.

---

## Wochenansicht

Neben der Monatsansicht gibt es jetzt eine Wochenansicht. Diese ist besonders hilfreich, wenn ein Haushalt viele Termine hat und die Monatsansicht zu voll wird.

Die Wochenansicht zeigt sieben Spalten von Montag bis Sonntag. Termine werden in der jeweiligen Tages-Spalte dargestellt. Wiederholungstermine werden auch hier als berechnete Vorkommen angezeigt.

---

## Architekturentscheidung

Die Kalenderlogik bleibt zweigeteilt:

- **Server:** speichert, prüft und validiert Termine.
- **Frontend:** berechnet die sichtbaren Wiederholungs-Vorkommen und rendert Monats- oder Wochenansicht.

Das ist für diese Tutorialreihe sinnvoll, weil die Seriendaten zentral gespeichert bleiben und die Darstellung flexibel zwischen Monat und Woche wechseln kann.

---

## Vorteile

- deutlich praxisnäherer Kalender,
- keine externe Kalenderbibliothek nötig,
- Wiederholungstermine ohne Datenverdopplung,
- Termine und Todos wachsen fachlich zusammen,
- bessere Übersicht durch Filter,
- Wochenansicht entlastet die Monatsansicht,
- Rechteprüfung bleibt serverseitig.

---

## Nachteile

- Wiederholungslogik erhöht die Komplexität,
- einzelne Vorkommen einer Serie können noch nicht separat bearbeitet werden,
- Erinnerungen sind nur sichtbare Hinweise, keine echten Benachrichtigungen,
- keine Zeitzonenlogik,
- keine Konfliktprüfung,
- keine Einladungen oder Zusagen.

---

## Sicherheitsaspekte

Wichtig ist vor allem die serverseitige Prüfung:

- private Termine gehören dem Besitzer,
- Familientermine gehören zum Haushalt,
- Todo-Verknüpfungen dürfen nur auf sichtbare Todos zeigen,
- Eingaben werden bereinigt,
- CSRF-Schutz bleibt aktiv,
- IDs werden nicht blind vertraut.

Die Filter und die Wiederholungsanzeige sind Komfortfunktionen im Frontend. Die eigentliche Zugriffskontrolle bleibt im Backend.

---

## Typische Fehlerquellen

- Wiederholung ohne Enddatum speichern,
- Erinnerung nach dem Terminbeginn setzen,
- fremde Todo-ID an einen Termin hängen,
- Monats- und Wochenansicht unterschiedlich filtern,
- Wiederholungstermine mehrfach speichern statt nur berechnet anzeigen,
- Familientermin für Nutzer ohne Haushalt erlauben.

---

## Testcheckliste

Nach der Installation sollten mindestens diese Punkte geprüft werden:

- privaten Termin ohne Wiederholung anlegen,
- Familientermin anlegen,
- täglichen Wiederholungstermin mit Enddatum anlegen,
- wöchentlichen Wiederholungstermin in Monats- und Wochenansicht prüfen,
- Erinnerung vor Terminbeginn setzen,
- Erinnerung nach Terminbeginn testen und ablehnen lassen,
- Termin mit Todo verknüpfen,
- Filter „nur eigene Termine“ testen,
- Filter „nur Familientermine“ testen,
- Filter „nur Wiederholungen“ testen,
- Filter „nur Erinnerungen“ testen,
- Filter „nur Todo-Verknüpfungen“ testen,
- Suchfilter über Titel, Ort und Todo-Titel prüfen,
- Termin bearbeiten,
- Termin löschen,
- Nutzer ohne Haushalt testen und Familientermin verweigern lassen.

---

## Grenzen dieser Version

Teil 16 ist eine deutliche Kalender-Vertiefung, aber noch kein vollwertiges Produktiv-Kalendersystem.

Noch offen sind:

- einzelne Vorkommen einer Wiederholungsserie separat bearbeiten,
- echte Push-/Mail-Benachrichtigungen,
- Kalender-Einladungen,
- Zusagen und Absagen,
- Feiertage,
- ICS-Import und Export,
- Drag-and-Drop,
- Konfliktprüfung,
- Zeitzonenlogik.

Diese Grenzen sind passend für den Lernstand. Die Anwendung zeigt jetzt die wichtigsten Konzepte, ohne den Quellcode in ein schwer wartbares Spezialkalendersystem zu verwandeln.

---

## Ausblick auf den nächsten Teil

Teil 17 kann das Dashboard fachlich zusammenführen. Dann geht es darum, Listen, Todos, Nachrichten, Chats und Kalendertermine nicht nur als einzelne Module zu haben, sondern als echte Startseite mit relevanten Zusammenfassungen.

Sinnvoll wären:

- heutige Termine,
- fällige Todos,
- ungelesene Nachrichten,
- aktive Familienkommunikation,
- nächste Erinnerungen,
- kompakte Haushaltsübersicht.

---

## Navigation

[← Teil 15 – Persönlicher Kalender](teil-15-persoenlicher-kalender.md) | [README / Übersicht](../README.md) | [Teil 17 – Dashboard →](../README.md#geplante-fortsetzung)
