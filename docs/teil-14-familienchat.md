# Teil 14 – Familienchat: gemeinsamer Chat für den Haushalt

Teil 14 erweitert das Kommunikationsmodell von HaushaltsPilot um einen gemeinsamen **Familienchat pro Haushalt**. Nach Teil 12 mit klassischen privaten Nachrichten und Teil 13 mit direktem 1:1-Chat entsteht nun die dritte Kommunikationsform: ein Gruppenchat, dessen Teilnehmer nicht manuell gepflegt werden, sondern automatisch aus der Haushaltszuordnung der Benutzer entstehen.

Damit bleibt die Anwendung fachlich sauber getrennt:

- **Personal Messages**: klassische private Nachrichten zwischen zwei Nutzern,
- **Einzelchat**: direkter 1:1-Chat mit Messenger-ähnlicher Bedienung,
- **Familienchat**: gemeinsamer Gruppenchat für alle aktiven Mitglieder eines Haushalts.

---

## Ziel dieses Kapitels

In diesem Kapitel wird HaushaltsPilot um folgende Funktionen erweitert:

- eigener Menüpunkt **Familienchat**,
- ein gemeinsamer Chat pro Haushalt,
- automatische Teilnehmerliste über aktive Haushaltsmitglieder,
- Familienchat-Badge für ungelesene Gruppenchatnachrichten,
- automatische Hintergrund-Aktualisierung,
- Tippanzeige im Familienchat,
- Zustell-/Gelesen-Status für Gruppenchatnachrichten,
- klare Abgrenzung zwischen privatem 1:1-Chat und Gruppenchat,
- Rechteprüfung über die Haushaltszuordnung.

Das Ziel ist nicht nur eine weitere Nachrichtenansicht, sondern eine saubere fachliche Erweiterung des bestehenden Thread-/Message-Modells.

---

## Warum wird der Familienchat jetzt umgesetzt?

Die Reihenfolge ist bewusst gewählt. Ein Familienchat braucht mehrere Grundlagen, die vorher bereits eingeführt wurden:

- Benutzerkonten und Login aus Teil 07,
- Haushalte und Familienzuordnung aus Teil 09,
- private Nachrichten aus Teil 12,
- Chat-Oberfläche, Hintergrund-Refresh, Tippstatus und Lesestatus aus Teil 13.

Ohne diese Grundlagen müsste der Familienchat entweder zu simpel bleiben oder später komplett umgebaut werden. Teil 14 nutzt deshalb die vorhandene Kommunikationsbasis und erweitert sie um Gruppenlogik.

---

## Ausgangssituation

Vor Teil 14 gibt es bereits:

- private Nachrichtenverläufe,
- getrennte 1:1-Chatverläufe,
- Sidebar-Badges,
- automatische Aktualisierung im Hintergrund,
- Tippanzeigen,
- `lastReadAt` pro Nutzer und Thread,
- ein gemeinsames Nachrichtenmodell mit Threads und Messages.

Was noch fehlt, ist die gruppenbezogene Haushaltskommunikation. Genau diese Lücke schließt Teil 14.

---

## Fachliche Entscheidung: ein Chat pro Haushalt

Der Familienchat wird nicht als frei erstellbarer Gruppenchat umgesetzt. Stattdessen gilt:

> Jeder Haushalt besitzt genau einen Familienchat.

Das ist für diese Anwendung fachlich passend, weil HaushaltsPilot nicht als allgemeiner Messenger, sondern als Haushalts- und Familienanwendung gedacht ist.

Die Teilnehmer ergeben sich aus den aktiven Nutzern desselben Haushalts. Wird ein Nutzer einem Haushalt zugeordnet, kann er den Familienchat sehen. Wird er aus dem Haushalt entfernt, verliert er den Zugriff.

---

## Technische Umsetzung

Das bestehende Thread-Modell wird erweitert. Neben den bisherigen Thread-Typen gibt es nun einen neuen Typ:

```txt
family_chat
```

Ein Familienchat-Thread enthält zusätzlich:

```txt
familyId
```

Damit kann der Server eindeutig prüfen, zu welchem Haushalt der Chat gehört.

Die wichtigsten API-Erweiterungen sind:

- `send_family_chat_message`
- erweiterte `set_typing`-Logik für den Kanal `family`
- erweiterte Badge-Berechnung für `totalUnreadFamilyChats`
- erweiterte Thread-Filterung für `familyChatThreads`

Die normale Nachrichten- und Chatlogik bleibt erhalten.

---

## Architekturentscheidung

Der Familienchat nutzt weiterhin das vorhandene Thread-/Message-Prinzip. Das ist absichtlich so gewählt.

**Warum kein separates Datenmodell?**

Ein separates Modell für Familienchatnachrichten würde kurzfristig einfach wirken, langfristig aber zu doppelter Logik führen:

- doppelte Nachrichtenrendering-Logik,
- doppelte Lesestatuslogik,
- doppelte Löschlogik,
- doppelte Tippstatuslogik,
- doppelte Badge-Berechnung.

Stattdessen wird das vorhandene Kommunikationsmodell fachlich erweitert. Die Unterscheidung erfolgt über den Thread-Typ.

---

## Abgrenzung zu privaten Nachrichten und 1:1-Chat

Teil 14 trennt die Kommunikationsformen bewusst:

| Bereich | Thread-Typ | Teilnehmer | Sichtbarkeit |
| --- | --- | --- | --- |
| Private Nachrichten | `personal_message` | zwei Nutzer | Nachrichtenmodul |
| Einzelchat | `one_to_one_chat` | zwei Nutzer | Chatmodul und Mini-Chat |
| Familienchat | `family_chat` | alle aktiven Haushaltsmitglieder | Familienchatmodul |

Dadurch landen Familienchatnachrichten nicht im 1:1-Chat und auch nicht in den klassischen privaten Nachrichten.

---

## Rechteprüfung

Die wichtigste Regel lautet:

> Ein Nutzer darf einen Familienchat nur sehen und nutzen, wenn seine `familyId` zur `familyId` des Familienchat-Threads passt.

Die Rechteprüfung passiert serverseitig. Die Oberfläche blendet zwar nur passende Daten ein, aber die Sicherheit hängt nicht vom Frontend ab.

Dadurch ist sichergestellt:

- Nutzer ohne Haushalt sehen keinen Familienchat,
- Nutzer aus anderen Haushalten sehen fremde Familienchats nicht,
- gelöschte oder verschobene Nutzer verlieren den Zugriff,
- Admins können weiterhin administrative Daten prüfen.

---

## Badge-Funktion

Der Familienchat besitzt einen eigenen Badge. Dieser zählt nur ungelesene Familienchatnachrichten.

Damit gibt es jetzt drei getrennte Kommunikationszähler:

- ungelesene private Nachrichten,
- ungelesene 1:1-Chatnachrichten,
- ungelesene Familienchatnachrichten.

Das Dashboard fasst diese Werte zusammen, aber die Sidebar zeigt sie getrennt an.

---

## Hintergrund-Aktualisierung

Wie in Teil 13 wird kein WebSocket verwendet. Stattdessen fragt das Frontend regelmäßig die bestehende API ab.

Das ist für den Lernstand sinnvoll, weil dadurch keine zusätzliche Servertechnik notwendig ist. Die Anwendung bleibt lokal auf klassischen PHP-Hosting-Umgebungen lauffähig.

Wenn der Familienchat geöffnet ist, wird der Verlauf beim Aktualisieren automatisch als gelesen markiert.

---

## Pro

- sauberer Chat pro Haushalt,
- klare Trennung von Personal Messages, 1:1-Chat und Familienchat,
- Rechteprüfung über bestehende Haushaltsstruktur,
- kein manuelles Teilnehmermanagement nötig,
- Badge für ungelesene Familienchatnachrichten,
- vorhandenes Thread-/Message-Modell wird sinnvoll wiederverwendet,
- vorbereitet für spätere Kalender- und Dashboard-Integration.

---

## Kontra

- noch kein WebSocket-Echtzeitbetrieb,
- Teilnehmer werden über Haushaltszuordnung gesteuert und nicht frei ausgewählt,
- keine Dateianhänge,
- keine Moderationsfunktionen,
- keine Archivierung,
- keine Push-Benachrichtigungen,
- kein produktives Datenschutz- und Löschkonzept.

Diese Einschränkungen sind für die Tutorialreihe bewusst akzeptiert. Ziel ist eine verständliche, stabile Gruppenchat-Basis.

---

## Sicherheitsaspekte

Teil 14 vertieft besonders diese Punkte:

- serverseitige Haushaltsprüfung,
- Trennung der Thread-Typen,
- CSRF-Schutz für Chataktionen,
- sichere Textausgabe gegen XSS,
- keine direkte HTML-Ausgabe aus Nachrichteninhalten,
- Lesestatus nur für berechtigte Teilnehmer,
- kein Vertrauen auf reine Frontend-Filter.

---

## Typische Fehlerquellen

Beim Familienchat entstehen typische Fehler:

- Gruppenchatnachrichten werden versehentlich im 1:1-Chat angezeigt,
- private Nachrichten landen im Familienchat,
- Badges zählen alle Kommunikationsformen zusammen,
- Nutzer aus einem anderen Haushalt können fremde Chats sehen,
- neue Haushaltsmitglieder werden nicht als Teilnehmer berücksichtigt,
- entfernte Mitglieder behalten Zugriff.

Teil 14 vermeidet diese Fehler durch eigene Thread-Typen und eine serverseitige Haushaltsprüfung. Zusätzlich wird der Logout vor datenabhängigen Auth-Aktionen verarbeitet, damit ein beschädigter oder gerade aktualisierter Datenstand den Logout nicht blockieren kann.

---

## Testcheckliste

Nach der Installation sollten mindestens diese Punkte geprüft werden:

- zwei aktive Nutzer im selben Haushalt anlegen,
- mit Nutzer A eine Familienchatnachricht senden,
- mit Nutzer B anmelden und Familienchat-Badge prüfen,
- Familienchat öffnen und prüfen, ob der Badge verschwindet,
- prüfen, ob die Nachricht nicht im Modul **Nachrichten** erscheint,
- prüfen, ob die Nachricht nicht im Modul **Chats** erscheint,
- normale private Nachricht senden und prüfen, dass sie nicht im Familienchat landet,
- 1:1-Chatnachricht senden und prüfen, dass sie nicht im Familienchat landet,
- Nutzer in einen anderen Haushalt verschieben und Zugriff erneut prüfen,
- Nutzer ohne Haushalt testen,
- Tippanzeige im Familienchat mit zwei Browserfenstern prüfen,
- Hintergrund-Aktualisierung mit zwei Browserfenstern prüfen,
- Logout während laufender Hintergrund-Aktualisierung testen,
- Nachrichteninhalt mit HTML-Zeichen testen und sichere Textausgabe prüfen.

---

## Grenzen dieser Version

Teil 14 ist ein sauberer Familienchat, aber noch kein produktiver Messenger:

- kein WebSocket,
- kein Push,
- keine Medienanhänge,
- keine Reaktionen pro Nachricht,
- keine Erwähnungen,
- keine Suche,
- keine Archivierung,
- keine Ende-zu-Ende-Verschlüsselung.

Diese Grenzen sind passend, weil die Tutorialreihe zuerst das Datenmodell, die Rechteprüfung und die Oberfläche nachvollziehbar aufbaut.

---

## Ausblick auf den nächsten Teil

Teil 15 führt den persönlichen Kalender ein. Damit wechselt die Reihe vom Kommunikationsbereich in den Terminbereich:

- persönliche Termine,
- Terminformular,
- Datum/Uhrzeit,
- eigene Kalenderansicht,
- spätere Vorbereitung auf Familienkalender und Dashboard-Zusammenführung.

Der Familienchat bleibt dafür wichtig, weil spätere Termine und Familienereignisse fachlich mit Haushaltskommunikation zusammengeführt werden können.

---

## Einordnung für die Praxis

In einer produktiven Anwendung würde man zusätzlich Datenschutz, Löschfristen, Moderation, Protokollierung, Rate-Limits, Push-Benachrichtigungen und eventuell WebSockets einplanen. Für diese Tutorialreihe ist die gewählte Lösung bewusst bodenständig: Sie zeigt, wie man aus einem einfachen Nachrichtenmodell eine saubere, haushaltsgebundene Gruppenkommunikation entwickelt.

---

## Navigation

[← Teil 13 – Einzelchat](teil-13-einzelchat.md) | [README / Übersicht](../README.md) | [Teil 15 – Persönlicher Kalender →](#ausblick-auf-den-naechsten-teil)
