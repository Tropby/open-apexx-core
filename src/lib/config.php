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

if( file_exists( BASEDIR."lib/config.database.php" ) )
{
	require_once(BASEDIR."lib/config.database.php");
	$set = json_decode( $configJSON, true );
	unset($configJSON);
}
else
{
	$set['installed'] = false;
}

// SESSION ///////////////////////////////////////////////////////////////////////////////////

// Session-Management
// Standardmδίig werden die Sessions von PHP verwaltet ("php"), sollte dies nicht problemlos
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

?><?php $set['installed'] = true; ?>