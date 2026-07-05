# Teil 13 – Einzelchat: direkter 1:1-Chat zwischen Nutzern

Teil 13 erweitert das in Teil 12 eingeführte Nachrichtenmodell zu einem direkten 1:1-Chat. Die Anwendung besitzt weiterhin private Nachrichtenverläufe, nutzt diese aber nun in einer deutlich chatähnlicheren Oberfläche: mit eigenem Menüpunkt **Chats**, einer Auflistung aller eigenen Chatverläufe, einem aktiven Chatbereich, Emoji-Auswahl und einem schwebenden Mini-Chat unten rechts.

Damit wird aus dem eher klassischen Personal-Message-Modul ein direkter Kommunikationsbereich, ohne die bisherige Architektur wegzuwerfen.

---

## Ziel dieses Kapitels

Nach diesem Teil kann die Anwendung:

- alle eigenen 1:1-Chats in einem eigenen Chat-Menü anzeigen,
- einen neuen Chat mit einem aktiven Nutzer starten,
- bestehende private Verläufe als Chat öffnen,
- Nachrichten direkt im Chatverlauf beantworten,
- eine Emoji-Auswahl in neue Nachrichten und Antworten einfügen,
- einen schwebenden Schnellchat unten rechts öffnen,
- ungelesene Nachrichten weiterhin als Badge anzeigen,
- die gleiche Thread-Struktur aus Teil 12 weiterverwenden,
- Chat, Nachrichten und Administration getrennt in der Dashboard-Shell darstellen.

Der Lernschwerpunkt liegt auf der Frage, wie aus einem vorhandenen Nachrichtenmodell eine zweite, stärker interaktive Oberfläche gebaut wird.

---

## Warum dieser Schritt jetzt kommt

Teil 12 hat die fachlich notwendige Basis geschaffen:

- Nutzer als Absender und Empfänger,
- private Threads zwischen genau zwei Nutzern,
- einzelne Nachrichten,
- Lesestatus je Nutzer,
- Ungelesen-Zähler,
- Zugriffskontrolle pro Verlauf.

Ein direkter Chat benötigt genau diese Grundlagen. Würde man den Chat ohne Thread-, Nachrichten- und Lesestatusmodell bauen, müsste man später fast alles wieder umbauen. Teil 13 nutzt deshalb bewusst die vorhandenen Strukturen und erweitert vor allem die Bedienoberfläche.

---

## Ausgangssituation

Vor Teil 13 besitzt HaushaltsPilot bereits:

- Login und Registrierung,
- aktive und deaktivierte Benutzer,
- Rollen und Adminbereich,
- Haushalte/Familien,
- Listen und Todos,
- Personal Messages mit Threads,
- `messageThreads`, `messages` und `lastReadAt`,
- Sidebar, Topbar und Dashboard-Module im AdminLTE-inspirierten Stil.

Was noch fehlt, ist eine Chat-Oberfläche, die sich nicht wie ein Formular mit Nachrichtenliste anfühlt, sondern wie eine laufende Unterhaltung.

---

## Fachliche Grundlagen

Ein Chat ist technisch nicht zwingend etwas völlig anderes als ein Nachrichtensystem. Der Unterschied liegt häufig in der Bedienung und Erwartung:

- Nachrichtenmodul: eher schreiben, lesen, später antworten.
- Chatmodul: laufender Verlauf, schnelle Antwort, kompakte Darstellung.

Teil 13 bleibt bewusst ohne WebSockets oder Long Polling. Die Anwendung speichert Nachrichten serverseitig und aktualisiert die Oberfläche nach Aktionen über die bestehende API. Dadurch bleibt die Version für Lernende nachvollziehbar und passt zur bisherigen PHP-/JSON-/PDO-Struktur.

---

## Technische Umsetzung

Teil 13 ergänzt im Frontend:

- neuen Sidebar-Menüpunkt **Chats**,
- neuen Arbeitsbereich `chatsPage`,
- Chatliste mit allen eigenen 1:1-Verläufen,
- Formular zum Starten eines neuen Chats,
- aktiven Chatbereich mit Bubble-Darstellung,
- Emoji-Leisten für Startnachricht, Antwort und Schnellchat,
- schwebendes Chatfenster unten rechts,
- Schnellantwort im Mini-Chat,
- Badges für ungelesene Nachrichten im Nachrichten- und Chat-Menü.

Im Backend bleibt das bestehende Nachrichtenmodell erhalten. Die API-Aktion `send_message` wird weiterverwendet; zusätzlich ist `send_chat_message` als semantischer Alias vorbereitet. Dadurch kann die Tutorialreihe später leichter zwischen klassischen Nachrichten und Chat-Aktionen unterscheiden, ohne Daten doppelt zu speichern.

---

## Architekturentscheidung

Die wichtigste Entscheidung lautet:

> Teil 13 baut den Chat nicht als neues, getrenntes Datenmodell, sondern als neue Oberfläche auf dem bestehenden Thread-/Message-Modell.

Das ist bewusst gewählt, weil private Nachrichten und 1:1-Chats dieselbe fachliche Beziehung besitzen: Zwei Nutzer schreiben in einem gemeinsamen Verlauf. Der Unterschied liegt in der Darstellung und Interaktion.

### Vorteile dieser Entscheidung

- Keine doppelte Speicherung von Nachrichten.
- Ungelesen-Badges funktionieren weiter.
- Lesestatus bleibt zentral.
- Adminübersicht bleibt nutzbar.
- Teil 14 kann Familienchat als Erweiterung des Thread-Prinzips umsetzen.

### Nachteile dieser Entscheidung

- Der Code muss klar erklären, dass Messages und Chats dieselbe Datenbasis verwenden.
- Es gibt zwei Oberflächen für ähnliche Inhalte.
- Ohne automatische Aktualisierung ist es noch kein echter Live-Chat.
- Die Trennung zwischen „Nachricht“ und „Chat“ ist didaktisch erklärungsbedürftig.

---

## Chat-Menü

Der neue Menüpunkt **Chats** zeigt alle Verläufe, an denen der aktuelle Nutzer beteiligt ist. Jeder Eintrag enthält:

- den anderen Teilnehmer,
- einen Auszug der letzten Nachricht,
- den Aktualisierungszeitpunkt,
- einen Badge für ungelesene Nachrichten.

Beim Öffnen eines Chats wird der Verlauf als gelesen markiert. Dadurch verschwinden ungelesene Zähler für diesen Verlauf.

---

## Schwebendes Chatfenster

Teil 13 ergänzt ein Mini-Chatfenster unten rechts. Dieses ist bewusst an typische Messenger-Oberflächen angelehnt:

- runder Chatbutton,
- ungelesene Zahl direkt am Button,
- kleine Verlaufsliste,
- aktiver Chatbereich,
- Schnellantwort,
- Emoji-Leiste.

Das Fenster nutzt denselben aktiven Thread wie die große Chatansicht. Dadurch entsteht keine doppelte Logik. Wird links ein Chat geöffnet, kann derselbe Verlauf auch im Mini-Chat beantwortet werden.

---

## Emoji-Funktion

Die Emoji-Funktion ist bewusst einfach umgesetzt:

- feste Emoji-Auswahl im Frontend,
- Einfügen an der aktuellen Cursorposition,
- Speicherung als normaler Text,
- sichere Ausgabe über Textausgabe statt HTML.

Das ist für den Lernstand sinnvoll, weil es zeigt, dass Komfortfunktionen nicht immer eine externe Bibliothek benötigen.

---

## Vorteile

- Der Bereich Nachrichten wird deutlich benutzerfreundlicher.
- Alle eigenen Chats sind zentral auffindbar.
- Die Anwendung wirkt durch Chatfenster und Bubbles moderner.
- Badges bleiben konsistent zwischen Nachrichten und Chats.
- Der bestehende Code aus Teil 12 wird sinnvoll weiterverwendet.
- Die Lösung bleibt frameworkfrei und verständlich.

---

## Nachteile

- Noch keine automatische Aktualisierung im Hintergrund.
- Noch keine Tippanzeige.
- Noch keine Zustell- oder Gelesen-Häkchen pro Nachricht.
- Noch keine Dateianhänge.
- Noch keine Suche im Chatverlauf.
- Noch keine Chat-Archivierung.
- Das Chatfenster ist UI-Komfort, aber noch kein Echtzeit-Messenger.

---

## Sicherheitsaspekte

Teil 13 übernimmt die Sicherheitsbasis aus Teil 12:

- Loginpflicht für API-Zugriffe,
- CSRF-Schutz bei schreibenden Aktionen,
- serverseitige Prüfung des Empfängers,
- keine Nachrichten an sich selbst,
- Prüfung aktiver Benutzer,
- Zugriffskontrolle pro Thread,
- sichere Textausgabe im Frontend,
- keine HTML-Ausgabe aus Nachrichteninhalten.

Wichtig ist: Auch wenn die Oberfläche wie ein Chat wirkt, darf der Browser niemals allein entscheiden, welche Nachrichten sichtbar sind. Die API muss weiterhin serverseitig prüfen, ob der Nutzer Teilnehmer des Verlaufs ist.

---

## Typische Fehlerquellen

- Für den Chat ein zweites Nachrichtensystem bauen und dadurch Daten doppelt speichern.
- Ungelesen-Badges für Chat und Nachrichten unterschiedlich berechnen.
- Emoji-Inhalte als HTML ausgeben statt als Text.
- Chatverläufe nur im Frontend filtern und fremde Nachrichten ausliefern.
- Einen Chat mit deaktivierten Nutzern erlauben.
- Das schwebende Chatfenster ohne Bezug zum aktiven Thread bauen.

---

## Was wurde gegenüber Teil 12 verbessert?

Teil 12 führte private Nachrichten ein. Teil 13 verbessert daraus die Bedienung:

- eigener Menüpunkt **Chats**,
- Auflistung aller eigenen Chats,
- direkter Chatbereich mit Bubble-Ansicht,
- Schnellantwort im aktiven Verlauf,
- Emoji-Auswahl,
- schwebendes Mini-Chatfenster unten rechts,
- Chat-Badge in der Sidebar,
- Vorbereitung auf Teil 14 Familienchat.

---

## Testcheckliste

Nach der Installation sollten mindestens diese Punkte geprüft werden:

- Zwei aktive Nutzer anlegen.
- Mit Nutzer A einen Chat mit Nutzer B starten.
- Prüfen, ob der Chat im Menü **Chats** erscheint.
- Mit Nutzer B anmelden und Ungelesen-Badge prüfen.
- Chat öffnen und prüfen, ob der Badge verschwindet.
- Antwort im großen Chatbereich schreiben.
- Antwort im schwebenden Mini-Chat schreiben.
- Emojis in Startnachricht, Antwort und Schnellchat einfügen.
- Prüfen, ob Nachrichten als Text und nicht als HTML ausgegeben werden.
- Prüfen, ob ein Nutzer keinen Chat mit sich selbst starten kann.
- Prüfen, ob deaktivierte Nutzer nicht als Chatpartner verwendet werden können.
- Zwischen Dashboard, Nachrichten, Chats und Administration wechseln.
- Prüfen, ob freigegebene Einkaufslisten nicht mehr unter "Meine Listen", sondern unter "Gemeinsame Einkaufslisten" erscheinen.
- Prüfen, ob freigegebene Haushaltslisten unter "Gemeinsame Haushaltslisten" erscheinen.

---

## Grenzen dieser Version

Teil 13 ist ein vollständiger 1:1-Chat auf Anfragebasis, aber noch kein Echtzeit-Messenger:

- kein WebSocket,
- kein Long Polling,
- keine automatische Aktualisierung im Sekundenintervall,
- keine Tippanzeige,
- keine Dateianhänge,
- keine Push-Benachrichtigung,
- keine Suchfunktion,
- keine Archivierung.

Diese Grenzen sind bewusst gesetzt. Die Tutorialreihe bleibt dadurch verständlich und baut zuerst ein solides Chatmodell, bevor Echtzeittechnik hinzukommt.

---

## Ausblick auf den nächsten Teil

Teil 14 erweitert das Kommunikationsmodell zum Familienchat. Dort geht es nicht mehr nur um zwei Teilnehmer, sondern um gemeinsame Haushaltskommunikation:

- Chat pro Haushalt,
- mehrere Teilnehmer,
- Familienchat-Badge,
- Abgrenzung zwischen privatem 1:1-Chat und Gruppenchat,
- Rechteprüfung über Haushaltszuordnung.

Teil 13 liefert dafür die wichtige UI- und Interaktionsbasis.

---

## Einordnung für die Praxis

In echten Anwendungen würde ein Chat oft mit WebSockets, Push-Benachrichtigungen, Rate-Limits, Moderation, Datenschutzkonzept, Löschfristen und eventuell Verschlüsselung kombiniert. Für diese Tutorialreihe ist die aktuelle Umsetzung bewusst bodenständig: Sie zeigt, wie man ein vorhandenes PHP-Nachrichtenmodell professionell zu einer Chatoberfläche erweitert, ohne sofort externe Frameworks oder Echtzeitserver einzuführen.

---

## Navigation

[← Teil 12 – Personal Messages](teil-12-personal-messages.md) | [README / Übersicht](../README.md) | [Teil 14 – Familienchat →](#ausblick-auf-den-naechsten-teil)


### Korrektur: Trennung von Nachrichten und Chats

Teil 13 trennt private Nachrichten und direkte 1:1-Chats nun technisch sauber voneinander. Klassische Personal Messages verwenden eigene Nachrichtenverläufe, während Chatverläufe einen separaten Thread-Typ besitzen. Dadurch erscheinen Chatnachrichten nicht mehr im Nachrichtenmodul und normale private Nachrichten nicht mehr im Chatmodul. Die Badges werden getrennt berechnet.
