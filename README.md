# Open Apexx Core

## Was ist "Open Apexx Core"?

Open Apexx Core entspricht einer Grundinstallation von Open Apexx ohne zusatzmodule. Die verwendeten Module werden nachinstalliert. Dafür steht der Modulemanager zur Verfügung. Der Modulemanager kann automatisch Module von Github laden und diese auf dem Webspace in Open Apexx Core installieren. Dadurch ist ein einfaches updaten der Module nun möglich.

## Status

### Open Apexx Erweiterung by Tropby

Ich verwende schon seit sehr langer Zeit das Apexx bzw. Open Apexx. Daher habe ich beschlossen dieses weiter zu entwickeln und versuche dabei die Kompatibilität mit dem Open Apexx soweit wie möglich zu behalten. Die Änderungen werden sich auf neue PHP-Versionen und Verbesserungen bzw. Sicherheitsupdate belaufen. Um besser mit den Modulen arbeiten zu können werde ich pro Modul ein Repository anlegen und einen kleinen Modul-Manager schreiben der es ermöglicht Module nachzuinstallieren. Der Status der Module kann in der [Modulliste](MODULES.md) eingesehen werden.

Da ich dabei bin open-apexx auseinander zu nehmen und dadurch eine besseres weiter arbeiten an einzelnen Modulen ermögliche habe ich mich entschieden das Repository von dem open-apexx zu trennen. Mein neues Repository nennt sich nun open-apexx-core und beinhaltet nur die Core-Module. Für jedes weitere Modul wird dann ein eigenes Repositorx angelegt und in der Modulliste verlinkt. 

#### Open Apexx und PHP 8
Die neue PHP-Version fordert einige Überarbeitetungen am Open Apexx System. Zurzeit werden die Core-Module überarbeitet so dass diese ohne Fehler oder Warnungen lauffähig sind. Schaltet man die Warnungen ab, so sind schon die meisten Funktionen unter PHP 8 funktionsfähig. 

Dafür muss die Zeile 47 in /lib/class.apexx.php erweitert werden durch `^ E_WARNING`
```PHP
error_reporting(E_ALL ^ E_NOTICE);
```
zu
```PHP
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
```

### Open Apexx by Scheb

15 Jahre nach dem ersten offiziellen Release ist es an der Zeit anzuerkennen, dass die Software heutigen Ansprüchen
(und auch meinen eigenen) nicht mehr genügen kann und eine sinnvolle Weiterentwicklung - so der man sich auch bemühen
würde - auf dieser Code-Basis und mit meiner Beteiligung nicht mehr umzusetzen ist. Ich habe mich daher entschlossen
das Projekt offiziell zu beenden.

Interessierten Software-Archäologen, die ein Faible für Software aus dem PHP4-Zeitalter haben, steht es frei einen
Fork zu machen und sie an anderer Stelle weiterzuentwickeln. Dieses offizielle *Open Apexx* Repository wird
jedenfalls keine Issues und Pull Requests mehr annehmen.

# Was ist "Open Apexx"?

apexx ist ein CMS in PHP, das ursprünglich von Christian Scheb entwickelt und auf http://www.stylemotion.de als
kommerzielle Software vertrieben wurde.

Fast 8 Jahre nach dem ursprünglichen Release wurde die Weiterentwicklung beendet und er entschied die Software unter
einer Open-Source-Lizenz bereitzustellen.

Das CMS eignet sich für unterschiedlichen Arten von Webseiten, z.B. einfache Firmen-Seite oder Vereins-Seiten. Am besten
funktioniert Apexx für Seiten mit einem großen Anteil redaktioneller Inhalte, die darum herum eine Community-Platform
betreiben möglichen.

Das System ist nur auf Deutsch verfügbar, es besitzt jedoch Funktionen zur Lokalisierung.


# Systemanforderung

Bitte prüfen Sie die Systemanforderungen, bevor Sie die Software installieren:

 * 25MB Festplattenspeicher
 * PHP 7
 * PHP Safe-Mode = OFF
 * PHP Erweiterung gd
 * PHP Erweiterung curl
 * PHP Erweiterung zip (optional)
 * MySQL 4.0+
 
# Module im Modul-Manager

|Modul|Beschreibung|Source Code|Releases|
|-----|------------|-----------|--------|
|Hello World|Ein kleines Paket was die Grundlagen eines Paketes darstellt.|[src](https://github.com/Tropby/open-apexx-helloworld)|[releases](https://github.com/Tropby/open-apexx-helloworld/releases)|
|Sitemap|Zeigt den inhalt der kompletten Seite an (Modul support vorrausgesetzt). Kann den Inhalt im Google Sitemap XML-Format anzeigen.|[src](https://github.com/Tropby/open-apexx-sitemap)|[releases](https://github.com/Tropby/open-apexx-sitemap/releases)|
|Content|Modul zur anzeige von Inhalten.|[src](https://github.com/Tropby/open-apexx-content)|[releases](https://github.com/Tropby/open-apexx-content/releases)|
|FAQ|  |[src](https://github.com/Tropby/open-apexx-faq)|[releases](https://github.com/Tropby/open-apexx-faq/releases)|
|Kalender|  |[src](https://github.com/Tropby/open-apexx-calendar)|[releases](https://github.com/Tropby/open-apexx-calendar/releases)|
|Kommentare|  |[src](https://github.com/Tropby/open-apexx-comments)|[releases](https://github.com/Tropby/open-apexx-comments/releases)|
|News|  |[src](https://github.com/Tropby/open-apexx-news)|[releases](https://github.com/Tropby/open-apexx-news/releases)|
