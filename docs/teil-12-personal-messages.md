# Teil 12 – Personal Messages: private Nachrichten zwischen Nutzern

Teil 12 erweitert HaushaltsPilot um ein eigenes Nachrichtenmodul und stellt die Oberfläche gleichzeitig auf eine AdminLTE-inspirierte Dashboard-Shell um. Nutzer können sich private Nachrichten senden, vorhandene Nachrichtenverläufe öffnen und ungelesene Nachrichten über Badge-Zähler erkennen. Die wachsenden Module werden nicht mehr untereinander gestapelt, sondern über Sidebar, Topbar, Dashboard-Kacheln und getrennte Arbeitsbereiche organisiert.

Damit beginnt der im Grundplan vorgesehene Bereich **Nachrichten**. Wichtig: Diese Version ist noch kein Live-Chat mit WebSocket, Tippanzeige oder permanenter Aktualisierung. Sie ist bewusst als solides Personal-Message-Modul umgesetzt, auf dem Teil 13 den direkten 1:1-Chat weiter vertiefen kann.

---

## Ziel dieses Kapitels

Nach diesem Teil kann die Anwendung:

- private Nachrichten zwischen zwei aktiven Nutzern speichern,
- bestehende Nachrichtenverläufe wiederverwenden,
- Antworten in einem privaten Verlauf schreiben,
- ungelesene Nachrichten pro Verlauf zählen,
- eine Gesamtzahl ungelesener Nachrichten als Badge anzeigen,
- Nachrichten als gelesen markieren,
- Nachrichten löschen,
- Nachrichten im Adminbereich einsehen und verwalten.
- die Anwendung in eine modulare Dashboard-Oberfläche mit Sidebar, Topbar, Kennzahlen und Arbeitsbereichen überführen.

Der Lernschwerpunkt liegt auf einem neuen fachlichen Modul mit eigener Datenstruktur und eigener Zugriffskontrolle.

---

## Warum dieser Schritt jetzt kommt

Die Reihenfolge bleibt bewusst fachlich sauber:

1. Erst wurden Benutzer, Rollen und Haushalte eingeführt.
2. Danach kamen persönliche und gemeinsame Listen.
3. Danach wurden Todos als eigenes Modul ergänzt.
4. Jetzt folgt Kommunikation zwischen Nutzern.

Private Nachrichten benötigen zwingend Benutzerkonten. Ohne Login, Nutzer-IDs und aktive Konten gäbe es keinen sauberen Absender, keinen Empfänger und keine Rechteprüfung. Teil 12 baut deshalb direkt auf Teil 07 bis 11 auf.

---

## Ausgangssituation

Vor Teil 12 besitzt HaushaltsPilot bereits:

- Registrierung und Login,
- aktive und deaktivierte Benutzer,
- Rollen für Admins und normale Nutzer,
- Haushaltszuordnung,
- persönliche und gemeinsame Listen,
- persönliche und gemeinsame Todos,
- CSRF-Schutz,
- serverseitige Validierung,
- JSON-/SQLite-/MySQL-Speicherung über eine zentrale Storage-Schicht.

Was noch fehlt, ist eine Möglichkeit, dass Nutzer sich direkt innerhalb der Anwendung kontaktieren können.

---

## Fachliche Grundlagen

Ein Nachrichtensystem besteht nicht nur aus einer Tabelle `messages`. Für eine sinnvolle Erweiterbarkeit werden zwei Ebenen getrennt:

- **Nachrichtenverlauf / Thread**: beschreibt, welche Nutzer miteinander schreiben.
- **Nachricht / Message**: beschreibt die einzelne gesendete Nachricht.

Zusätzlich wird ein Lesestatus benötigt. Der Lesestatus wird nicht direkt an der Nachricht gespeichert, sondern pro Nutzer am Verlauf. Dadurch kann der Empfänger ungelesene Nachrichten sehen, während der Absender dieselben Nachrichten bereits als gelesen betrachtet.

---

## Technische Umsetzung

Teil 12 ergänzt die Anwendung um:

- `messageThreads` für private Nachrichtenverläufe,
- `messages` für einzelne Nachrichten,
- `lastReadAt` pro Nutzer und Verlauf,
- API-Aktion `send_message`,
- API-Aktion `mark_thread_read`,
- API-Aktion `delete_message`,
- sichtbare Badge-Zähler in der Oberfläche,
- AdminLTE-inspirierte Dashboard-Shell mit linker Modulnavigation, Topbar, Kennzahlen und getrennten Arbeitsbereichen,
- moderner Adminbereich mit Side-Menü und getrenntem Nachrichtenbereich.

Bei JSON werden die Daten direkt in der zentralen Datendatei gespeichert. Bei SQLite/MySQL werden die vorhandenen vorbereiteten Tabellen für Nachrichten nun aktiv genutzt und um Lesestatus- bzw. Löschinformationen erweitert.

---

## Architekturentscheidung

Die private Nachricht wird nicht als einfache Liste von Texten umgesetzt, sondern über Threads.

Das ist etwas aufwendiger, aber langfristig sauberer:

- Teil 12 nutzt Threads für private Nachrichten.
- Teil 13 kann daraus einen echten 1:1-Chat ableiten.
- Teil 14 kann das Thread-Prinzip für Familienchats erweitern.
- Teil 17 kann ungelesene Nachrichten im Dashboard zusammenführen.

Damit entsteht keine Wegwerf-Lösung, sondern eine vorbereitende Kommunikationsstruktur.

---

## Rechte und Zugriffskontrolle

Normale Nutzer sehen nur Nachrichtenverläufe, an denen sie beteiligt sind. Ein Empfänger kann fremde private Nachrichten nicht lesen, wenn er nicht Teilnehmer des Threads ist.

Admins erhalten zusätzlich eine Nachrichtenübersicht im Adminbereich. Die Administration ist dafür klar als eigenes Admin-Side-Menü strukturiert: Haushalte, Benutzer, Listen, Todos und Nachrichten liegen in getrennten Verwaltungsbereichen. Zusätzlich führt Teil 12 eine App-Shell im Stil klassischer Admin-Dashboards ein: links die Hauptnavigation, oben Kontext und Benutzerstatus, in der Mitte jeweils genau ein aktiver Arbeitsbereich. Das verhindert eine unübersichtliche Ein-Fenster-Ansicht und zeigt gleichzeitig, wie ein wachsendes Backend-Modul sauber gegliedert werden kann. In einem echten Produkt müsste man die Admin-Sicht auf private Nachrichten datenschutzrechtlich und organisatorisch sehr sorgfältig prüfen.

### Warum die Dashboard-Shell bereits in Teil 12 eingeführt wird

Nach Listen, Haushalten, Todos und Nachrichten wäre eine reine Ein-Seiten-Ansicht fachlich zu überladen. Die Anwendung braucht ab diesem Punkt eine echte Navigationsstruktur. Die AdminLTE-inspirierte Oberfläche löst genau dieses Problem:

- Dashboard als Startpunkt mit Kennzahlen,
- Sidebar für die Hauptmodule,
- Topbar für Kontext und Benutzerstatus,
- getrennte Arbeitsbereiche für Listen, Todos, Nachrichten und Administration,
- Badge-Zähler für Listen, offene Aufgaben, ungelesene Nachrichten und Adminobjekte.

Teil 17 bleibt trotzdem als geplanter Dashboard-Teil erhalten. Teil 12 schafft zunächst die Layout- und Navigationsbasis. Teil 17 kann später fachlich verdichten: Kalendertermine, Chats, Aufgaben, Nachrichten und Listen werden dann zu einer inhaltlichen Gesamtübersicht zusammengeführt.


---

## Badge-Funktion

Die Badge-Funktion zählt ungelesene Nachrichten:

- pro Nachrichtenverlauf,
- zusätzlich als Gesamtzahl im Nachrichtenmodul.

Eine Nachricht gilt als ungelesen, wenn:

- sie zum Verlauf des aktuellen Nutzers gehört,
- sie nicht vom aktuellen Nutzer selbst gesendet wurde,
- ihr Erstellungszeitpunkt neuer ist als der gespeicherte `lastReadAt`-Wert des Nutzers.

Beim Öffnen eines Verlaufs wird dieser Verlauf als gelesen markiert.

---

## Vorteile

- Das Modul passt sauber in den bisherigen Grundplan.
- Die Nutzerkommunikation ist nicht an Todos oder Listen angeklebt.
- Ungelesene Nachrichten sind sofort sichtbar.
- Die Datenstruktur ist auf Teil 13 und 14 vorbereitet.
- Der Lesestatus ist nachvollziehbar und serverseitig gespeichert.
- Die Lösung funktioniert mit JSON, SQLite und MySQL/MariaDB.

---

## Nachteile

- Die Datenstruktur wird komplexer.
- Ein Thread-Modell ist für absolute Anfänger erklärungsbedürftiger als eine einfache Nachrichtenliste.
- Es gibt noch keine automatische Aktualisierung ohne Neuladen/API-Refresh.
- Es gibt noch keine Anhänge.
- Es gibt noch keine Suchfunktion.
- Es gibt noch keine Archivierung ganzer Verläufe.
- Datenschutzfragen werden technisch angedeutet, aber noch nicht organisatorisch gelöst.

---

## Sicherheitsaspekte

Teil 12 achtet weiterhin auf:

- Loginpflicht für alle API-Aktionen,
- CSRF-Schutz bei schreibenden Aktionen,
- serverseitige Prüfung des Empfängers,
- Verbot von Nachrichten an sich selbst,
- Prüfung aktiver Benutzerkonten,
- sichere Textbereinigung,
- sichere Ausgabe über DOM/Textausgabe im Frontend,
- Zugriffskontrolle pro Nachrichtenverlauf.

Gerade bei Nachrichten ist wichtig: Die Oberfläche darf nicht allein entscheiden, wer eine Nachricht sehen darf. Die API muss diese Entscheidung serverseitig treffen.

---

## Typische Fehlerquellen

- Nachrichten nur clientseitig filtern und dadurch versehentlich fremde Inhalte ausliefern.
- Ungelesen-Zähler im Browser berechnen, ohne den Lesestatus serverseitig zu speichern.
- Den Empfänger nicht gegen aktive Nutzer zu prüfen.
- Private Nachrichten mit Familienchat zu vermischen.
- Nachrichten ohne Thread-Struktur zu speichern und später schwer erweitern zu können.
- HTML ungefiltert in Nachrichten auszugeben.

---

## Was wurde gegenüber Teil 11 verbessert?

Teil 11 führte Aufgaben als eigenes Modul ein. Teil 12 ergänzt nun ein weiteres unabhängiges Modul:

- aus Aufgabenverwaltung wird Haushaltskommunikation,
- aus reiner Datenverwaltung wird Interaktion zwischen Nutzern,
- aus statischen Modulen entsteht eine Plattformstruktur,
- das Admin-Side-Menü wird um Nachrichten erweitert,
- die bisherige Ein-Seiten-Ansicht wird durch eine Dashboard-Shell mit Hauptnavigation ersetzt,
- das Frontend erhält sichtbare Badges für ungelesene Inhalte.

---

## Testcheckliste

Nach der Installation sollten mindestens diese Punkte geprüft werden:

- Zwei Nutzer anlegen.
- Mit Nutzer A eine Nachricht an Nutzer B senden.
- Mit Nutzer B anmelden und Badge-Zähler prüfen.
- Verlauf öffnen und prüfen, ob der Badge verschwindet.
- Als Nutzer B antworten.
- Als Nutzer A prüfen, ob der neue Badge erscheint.
- Nachricht löschen und prüfen, ob sie aus der Ansicht verschwindet.
- Prüfen, dass ein Nutzer keine Nachricht an sich selbst senden kann.
- Prüfen, dass deaktivierte Nutzer nicht als Empfänger genutzt werden können.
- Als Admin den Nachrichtenbereich im Admin-Side-Menü öffnen.
- Zwischen Dashboard, Listen, Todos, Nachrichten und Administration über die Sidebar wechseln.
- Prüfen, ob Sidebar-Badges für Listen, offene Todos und ungelesene Nachrichten korrekt aktualisiert werden.

---

## Grenzen dieser Version

Diese Version ist vollständig für private Nachrichten, aber bewusst noch kein fertiger Chat:

- kein WebSocket,
- kein Long Polling,
- keine Tippanzeige,
- keine Dateianhänge,
- keine Emoji-Reaktionen,
- keine Nachrichtensuche,
- keine Archivierung ganzer Verläufe,
- keine Push- oder E-Mail-Benachrichtigung.

Diese Grenzen sind passend für den Lernstand. Teil 12 führt private Nachrichten sauber ein. Teil 13 kann daraus den direkten 1:1-Chat vertiefen.

---

## Ausblick auf den nächsten Teil

Teil 13 baut auf dieser Grundlage den Einzelchat aus. Dort kann der private Nachrichtenverlauf stärker wie ein Chat wirken:

- kompaktere Chatansicht,
- aktualisierte Nachrichtenanzeige,
- stärkerer Fokus auf laufende Unterhaltung,
- bessere Trennung zwischen Thread-Liste und aktivem Chat.

Teil 12 liefert dafür die wichtige technische Basis: Nutzer, Thread, Nachricht und Lesestatus.

---

## Einordnung für die Praxis

In produktiven Anwendungen wäre ein Nachrichtensystem ein sensibler Bereich. Neben Technik müssten dort Datenschutz, Löschkonzepte, Moderation, Missbrauchsschutz, Aufbewahrungsfristen und Rollenrechte genauer definiert werden.

Für die Tutorialreihe ist die aktuelle Umsetzung bewusst praktisch: Sie zeigt die wichtigen Grundlagen, ohne sofort ein großes Echtzeit-Kommunikationssystem daraus zu machen.

---

## Navigation

[← Teil 11 – Todos](teil-11-todos.md) | [README / Übersicht](../README.md) | [Teil 13 – Einzelchat →](teil-13-einzelchat.md)
