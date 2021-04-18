# Open Apexx 2 - Core

## Was ist "Open Apexx Core"?

Open Apexx Core entspricht einer Grundinstallation von Open Apexx ohne zusatzmodule. Die verwendeten Module werden nachinstalliert. Dafür steht der Modulemanager zur Verfügung. Der Modulemanager kann automatisch Module von Github laden und diese auf dem Webspace in Open Apexx Core installieren. Dadurch ist ein einfaches updaten der Module nun möglich.

Zurzeit wird von mit die alte Open Apexx Struktur überarbeitet. Aus den PHP 4 strukturen werden moderne Klassenmodelle erstellt. Dies nenne ich "Open Apexx 2". Die Datenbankstruktur und die Templates bleiben dabei bestehen. Dadurch ist es möglich "Open Apexx 2" auf einer "Open Apexx" installation zu verwenden.
Die Struktur kann [HIER](doc/structure.md) eingesehen werden.

Die open-apexx Module werden nach und nach auf die neue Struktur überführt. Zu jedem Modul wird es ein eigenes Repository geben welche in der der [Modulliste](MODULES.md) verlinkt sind.

## Module

|Modul|Beschreibung|Stand|Source Code|Releases|
|-----|------------|-----|-----------|--------|
|Hello World|Ein kleines Paket was die Grundlagen eines Paketes darstellt.|alt|[src](https://github.com/Tropby/open-apexx-helloworld)|[releases](https://github.com/Tropby/open-apexx-helloworld/releases)|
|Sitemap|Zeigt den inhalt der kompletten Seite an (Modul support vorrausgesetzt). Kann den Inhalt im Google Sitemap XML-Format anzeigen.|alt|[src](https://github.com/Tropby/open-apexx-sitemap)|[releases](https://github.com/Tropby/open-apexx-sitemap/releases)|
|Content|Modul zur anzeige von Inhalten.|alt|[src](https://github.com/Tropby/open-apexx-content)|[releases](https://github.com/Tropby/open-apexx-content/releases)|
|FAQ|  |alt|[src](https://github.com/Tropby/open-apexx-faq)|[releases](https://github.com/Tropby/open-apexx-faq/releases)|
|Kalender|  |alt|[src](https://github.com/Tropby/open-apexx-calendar)|[releases](https://github.com/Tropby/open-apexx-calendar/releases)|
|Kommentare|  |alt|[src](https://github.com/Tropby/open-apexx-comments)|[releases](https://github.com/Tropby/open-apexx-comments/releases)|
|News|  |alt|[src](https://github.com/Tropby/open-apexx-news)|[releases](https://github.com/Tropby/open-apexx-news/releases)|
|Besucherstatistiken|  ||||
|Galerie|  ||||
|Navigation|  ||||

## Status

### Open Apexx und PHP 8

Die neue PHP-Version fordert einige Überarbeitetungen am Open Apexx System. Zurzeit werden die Core-Module überarbeitet so dass diese ohne Fehler oder Warnungen lauffähig sind. Schaltet man die Warnungen ab, so sind schon die meisten Funktionen unter PHP 8 funktionsfähig.

Dafür muss die Zeile 47 in /lib/class.apexx.php erweitert werden durch "`^ E_WARNING`"

```PHP
error_reporting(E_ALL ^ E_NOTICE);
```

zu

```PHP
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
```

## Original Open Apexx by Scheb

15 Jahre nach dem ersten offiziellen Release ist es an der Zeit anzuerkennen, dass die Software heutigen Ansprüchen
(und auch meinen eigenen) nicht mehr genügen kann und eine sinnvolle Weiterentwicklung - so der man sich auch bemühen
würde - auf dieser Code-Basis und mit meiner Beteiligung nicht mehr umzusetzen ist. Ich habe mich daher entschlossen
das Projekt offiziell zu beenden.

Interessierten Software-Archäologen, die ein Faible für Software aus dem PHP4-Zeitalter haben, steht es frei einen
Fork zu machen und sie an anderer Stelle weiterzuentwickeln. Dieses offizielle *Open Apexx* Repository wird
jedenfalls keine Issues und Pull Requests mehr annehmen.

### Was ist "Open Apexx"?

apexx ist ein CMS in PHP, das ursprünglich von Christian Scheb entwickelt und auf [stylemotion.de](http://www.stylemotion.de) als
kommerzielle Software vertrieben wurde.

Fast 8 Jahre nach dem ursprünglichen Release wurde die Weiterentwicklung beendet und er entschied die Software unter
einer Open-Source-Lizenz bereitzustellen.

Das CMS eignet sich für unterschiedlichen Arten von Webseiten, z.B. einfache Firmen-Seite oder Vereins-Seiten. Am besten
funktioniert Apexx für Seiten mit einem großen Anteil redaktioneller Inhalte, die darum herum eine Community-Platform
betreiben möglichen.

Das System ist nur auf Deutsch verfügbar, es besitzt jedoch Funktionen zur Lokalisierung.

### Systemanforderung

Bitte prüfen Sie die Systemanforderungen, bevor Sie die Software installieren:

* 25MB Festplattenspeicher
* PHP 7
* PHP Safe-Mode = OFF
* PHP Erweiterung gd
* PHP Erweiterung curl
* PHP Erweiterung zip (optional)
* MySQL 4.0+
