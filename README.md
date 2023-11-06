# ToDo-Anwendung

Eine einfache Webanwendung als Symfony REST-API zur Verwaltung von Aufgaben und Unteraufgaben.

## Inhaltsverzeichnis

- [Anforderungen](#anforderungen)
- [Installation](#installation)
- [Konfiguration](#konfiguration)
- [Verwendung](#verwendung)
- [Beispiele](#beispiele)
- [Tests](#tests)
- [Kontakt](#kontakt)

## Anforderungen

- PHP 7.4 oder höher
- Symfony 4.4 oder höher
- MySQL-Datenbank
- Composer (Paketmanager)

## Installation

1. Klone das Repository:

   ```shell
   git clone git@github.com:gitniiise/todo-application.git
   cd todo-list-app
   ```
2. Installiere die Abhängigkeiten:

   ```shell
   composer install
   ```
3. Konfiguriere die Datenbankverbindung in .env:

   3.1 Erstelle einen Datenbankbenutzer

   ```shell
   sudo mysql -u root
   CREATE USER 'benutzername'@localhost IDENTIFIED BY 'password'
   CREATE DATABASE 'datenbankname'
   GRANT ALL ON datenbankname.* TO 'benutzername'@localhost
   ```
   3.2 Ändere die DATABASE_URL-Variable in der .enventsprechend deiner Datenbankkonfiguration.
   ```shell
   DATABASE_URL=mysql://benutzername:passwort@127.0.0.1:3306/deine_datenbank
   ```
5. Führe die Migrationsdateien aus:

   ```shell
   php bin/console doctrine:migrations:migrate
   ```
   => yes
6. Starte den Symfony-Entwicklungsserver:

   ```shell
   symfony server:start
   ```
Die ToDo-Liste-Anwendung sollte nun unter http://localhost:8000 verfügbar sein.

## Konfiguration
Alle Konfigurationsdateien befinden sich im config/-Verzeichnis. Du kannst die Konfiguration je nach Bedarf anpassen, z. B. für die Datenbankverbindung, Sicherheitsoptionen und mehr.

## Verwendung
1. Nachdem du die Anwendung installiert und gestartet hast, kannst du Aufgaben und Unter-Aufgaben über die Benutzeroberfläche hinzufügen, bearbeiten und löschen.

2. Die REST-API-Endpunkte sind unter /api verfügbar, z. B. /api/todos und /api/subtodos.

## Beispiele
Hier sind einige Beispiele zur Verwendung der API-Endpunkte:

- Hinzufügen einer Aufgabe:

```shell
POST /api/todos
{"name": "Einkaufen"}
```

- Hinzufügen einer Unter-Aufgabe zu einer Aufgabe:

```shell
POST /api/subtodos
{"name": "Milch kaufen", "parentToDo": 1}
```

- Abrufen von Aufgaben und Unter-Aufgaben:

```shell
GET /api/todos
GET /api/subtodos
```

- Aktualisieren einer Aufgabe oder Unter-Aufgabe:

```shell
PUT /api/todos/1
{"name": "Einkaufen für die Woche"}
PUT /api/subtodos/2
{"name": "H-Milch kaufen"}
```

- Löschen einer Aufgabe oder Unter-Aufgabe:

```shell
DELETE /api/todos/1
DELETE /api/subtodos/2
```

## Tests
Um die Tests auszuführen, verwende PHPUnit:

```shell
php bin/phpunit
```

## Autorin
Denise Bebenroth

## Kontakt
Bei Fragen oder Anliegen kannst du mir unter denise.bebenroth@web.de erreichen.
