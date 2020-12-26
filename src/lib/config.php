<?php 

/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|           (c) Copyright 2005-2009, Christian Scheb            |
|                  http://www.stylemotion.de                    |
|                                                               |
|---------------------------------------------------------------|
| THIS SOFTWARE IS NOT FREE! MAKE SURE YOU OWN A VALID LICENSE! |
| DO NOT REMOVE ANY COPYRIGHTS WITHOUT PERMISSION!              |
| SOFTWARE BELONGS TO ITS AUTHORS!                              |
\***************************************************************/


//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


// MYSQL ///////////////////////////////////////////////////////////////////////////////////

// Mysql-API, zur Auswahl stehen "mysql" und "mysqli"
// In der Regel gen�gt "mysql", "mysqli" sollten Sie probieren, wenn Sie PHP5 oder PHP4.1+ verwenden
$set['mysql_api'] = 'mysqli';

// IP oder Adresse des MySQL-Servers
$set['mysql_server'] = 'localhost';

// Benutzername f�r MySQL-Login
$set['mysql_user'] = 'root';

// Passwort f�r MySQL-Login
$set['mysql_pwd'] = '';

// Name der Datenbank
$set['mysql_db'] = 'open-apexx';

// Vorangestellte Tabellenbezeichnung
$set['mysql_pre'] = 'apx';

// Wird UTF8 als Zeichencodierung in der Datenbank verwenden?
// (Standardm��ig auf false lassen, au�er Sie wissen was Sie tun)
$set['mysql_utf8'] = false;



// SESSION ///////////////////////////////////////////////////////////////////////////////////

// Session-Management
// Standardm��ig werden die Sessions von PHP verwaltet ("php"), sollte dies nicht problemlos
// funktionieren, versuchen sie es mit der Einstellung "db"
$set['session_api'] = 'db';

// This will fail on all older modules! Try only for development!
$set['high_security'] = false;

// DEBUG ///////////////////////////////////////////////////////////////////////////////////

// show auto include file names
$set['debug_autoload'] = false;

// show session data
$set['debug_session'] = false;

// Will check if all modules + actions + templates are executable
$set['debugCheck'] = false;

// Kritische Fehlermeldungen anzeigen (true/false)
$set['showerror'] = true;

// Fehler-Report am Ende zeigen (true/false)
$set['errorreport'] = true;

// Cache immer ausgeben (true/false)
$set['outputcache'] = true;

// Renderzeit anzeigen (true/false)
$set['rendertime'] = false;

// Anfang und Ende der Templates anzeigen
// 0 = aus
// 1 = durch HTML-Kommentare
// 2 = sichtbare Rahmen
$set['tmplwhois'] = 0;

// Ab PHP 5.6 muss das Charset ISO-8859-1 erzwungen werden
if (version_compare(PHP_VERSION, '5.6.0') >= 0) {
	ini_set("default_charset", "ISO-8859-1");
}

?>