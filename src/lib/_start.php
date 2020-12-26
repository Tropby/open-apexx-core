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

// Setup base directory and execution type
define('MODE','public');
define('BASEDIR',dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);

//Setup suchen
if ( file_exists(BASEDIR.'setup/index.php') ) die('Bitte l&ouml;schen Sie zuerst den Ordner "setup"!');

//Globale Module und Funktionen laden
require_once(BASEDIR.'lib/_deprecated.vars.php');
require_once(BASEDIR.'lib/config.php');
require_once(BASEDIR.'lib/functions.php');
require_once(BASEDIR.'lib/functions.public.php');

// Load Global Classes
require_once(BASEDIR.'lib/autoload.class.php');

// set database prefix
define('PRE',$set['mysql_pre']);

/**
 * Apexx System
 * @var apexx_public
 */
$apx = apexx::startApexxPublic();

if( $apx->config('debugCheck') )
{
    $apx->debugCheck();
}

?>