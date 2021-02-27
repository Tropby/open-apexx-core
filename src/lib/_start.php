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

//////////////////////////////////////////////////////////////////////////////////////////// SYSTEMSTART

//BENCHMARK
$_BENCH=microtime();

// Ab PHP 5.6 muss das Charset ISO-8859-1 erzwungen werden
if (version_compare(PHP_VERSION, '5.6.0') >= 0) {
	ini_set("default_charset", "ISO-8859-1");
}

// Setup base directory and execution type
define('MODE','public');
define('BASEDIR',dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);

// Load Global Classes
require_once(BASEDIR.'lib/autoload.class.php');

//Globale Module und Funktionen laden
require_once(BASEDIR.'lib/_deprecated.vars.php');
require_once(BASEDIR.'lib/config.php');
require_once(BASEDIR.'lib/functions.php');
require_once(BASEDIR.'lib/functions.public.php');

// set database prefix
define('PRE', $set['mysql_pre']??"");

/**
 * Apexx System
 * @var ApexxPublic
 */
$apx = Apexx::startApexxPublic();

if( $apx->config('debugCheck') )
{
    $apx->debugCheck();
}

?>