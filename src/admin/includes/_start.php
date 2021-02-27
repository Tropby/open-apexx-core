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

//BENCHMARK
$_BENCH=microtime();

define('MODE','admin');
define('BASEDIR',dirname(dirname(dirname(__FILE__))).'/');
define('BASEREL','../');
$set=array(); //Variable schützen

//Globale Module und Funktionen laden
require_once(BASEDIR.'lib/autoload.class.php');
require_once(BASEDIR.'lib/config.php');
require_once(BASEDIR.'lib/functions.php');
require_once(BASEDIR.'lib/functions.admin.php');

//Datenbank Verbindung aufbauen
define('PRE',$set['mysql_pre']);

/**
 * Apexx System
 * @var ApexxPublic
 */
$apx = Apexx::startApexxAdmin();
$db = $apx->db();

$apx->lang = new Language($apx);   //Sprache
$apx->lang->langid($apx->language_default); //Standard-Sprachpaket


//Session starten
$apx->session = new Session('sid');
$token = $apx->session->get('sectoken');
if ( !$token ) {
	$apx->session->set('sectoken', md5(microtime().rand()));
}

//Sektionswähler
if ( isset($_GET['selectsection']) ) {
	if ( isset($apx->sections[$_GET['selectsection']]) ) {
		$apx->session->set('section', $_GET['selectsection']);
	}
	else {
		$apx->session->set('section', 0);
	}
}


//Modul-Funktionen laden
foreach ( $apx->modules AS $module => $info ) {
	if ( !file_exists(BASEDIR.getmodulepath($module).'admin_system.php') ) continue;
	include_once(BASEDIR.getmodulepath($module).'admin_system.php');
}


$apx->lang->init();          //Sprachpakete initialisieren
$apx->tmpl = new TemplatesAdmin;  //Templates
$html = new html;            //HTML Klasse für Admin

?>