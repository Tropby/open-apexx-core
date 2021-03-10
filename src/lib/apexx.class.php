<?php

/*
	Open Apexx Core
	(c) Copyright 2005-2009, Christian Scheb
	(c) Copyright 2020 Carsten Grings

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 2.1 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//Security-Check
if (!defined('APXRUN')) die('You are not allowed to execute this file directly!');

class Apexx
{

	var $modules = array();
	var $actions = array();
	var $functions = array();
	var $functions_admin = array();
	var $sections = array();
	var $languages = array();

	var $active_module;
	var $language_default;

	var $section_default = 0;
	var $section = array('id' => 0);

	var $coremodules = array('main', 'mediamanager', 'user');

	private Parameters $parameters;
	private DatabaseMysqli $db;
	private Session $session;


	private array $config = array();
	private array $registered_objects = array();
	private array $module_objects = array();

	private Path $path;

	static private apexx $instance;

	////////////////////////////////////////////////////////////////////////////////// -> STARTUP

	static public function &startApexxAdmin(): \Apexx
	{
		if (!isset(apexx::$instance))
			apexx::$instance = new ApexxAdmin();
		return apexx::$instance;
	}

	static public function &startApexxPublic(): \Apexx
	{
		if (!isset(apexx::$instance))
			apexx::$instance = new ApexxPublic();
		return apexx::$instance;
	}

	//System starten
	protected function __construct()
	{
		global $set;

		$this->session = new Session('apexx_public_sid');

		Autoload::setApx($this);

		// Make APX object gloabl
		{
			$GLOBALS["apx"] = &$this;
		}

		// Setup Error-Reporting
		error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT ^ E_DEPRECATED ^ E_WARNING);

		// Setup Version and general defines
		$version = file(BASEDIR . 'lib/version.info');
		define('VERSION', array_shift($version));
		define('HTTP_HOST', $this->get_http());
		define('HTTPDIR', $this->get_dir());
		define('HTTP', HTTP_HOST . HTTPDIR);

		$this->path = Path::instance();

		//Variablen vorbereiten
		$this->prepare_vars();
		$this->parameters = new Parameters();

		$this->config = $set;
		if ($this->config('high_security'))
			unset($set);

		// Startup mysql server connection ONLY if configuration is awailable
		if (
			$this->config('mysql_server') !== NULL &&
			$this->config('mysql_user') !== NULL &&
			$this->config('mysql_pwd') !== NULL &&
			$this->config('mysql_db') !== NULL &&
			$this->config('mysql_utf8') !== NULL
		) {
			$this->db = new DatabaseMysqli($this->config['mysql_server'], $this->config['mysql_user'], $this->config['mysql_pwd'], $this->config['mysql_db'], $this->config['mysql_utf8']); {
				$GLOBALS["db"] = $this->db();
			}

			//Module auslesen
			$this->get_modules();
			$this->get_config(); {
				$GLOBALS["user"] = $this->get_registered_object('user');
			}

			//Sprachpakete
			$this->get_languages();

			//Sektionen auslesen
			$this->get_sections();

			//Module + Actions sortieren
			$this->sort_modules();
			$this->sort_actions();

			//Sprach-Klasse
			$this->lang = new language($this);
			$this->lang->langid($this->language_default);
		}

		//Zeitzone
		define('TIMEDIFF', (date('Z') / 3600 - $this->config['main']['timezone'] - date('I')) * 3600);
	}

	public function session(): Session
	{
		return $this->session;
	}

	//Übergebene Variable vorbereiten (für alte Module die noch nicht das neue System verwenden)
	private function prepare_vars()
	{
		if (isset($_REQUEST) && is_array($_REQUEST)) $_REQUEST = $this->strpsl($_REQUEST);
		if (isset($_POST) && is_array($_POST)) $_POST = $this->strpsl($_POST);
		if (isset($_GET) && is_array($_GET)) $_GET = $this->strpsl($_GET);
		if (isset($_COOKIE) && is_array($_COOKIE)) $_COOKIE = $this->strpsl($_COOKIE);
		if (isset($_SESSION) && is_array($_SESSION)) $_SESSION = $this->strpsl($_SESSION);

		//Fehlendes REQUEST_URI auf IIS-Server fixen
		if (!isset($_SERVER['REQUEST_URI'])) {
			$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
			if ($_SERVER['QUERY_STRING']) {
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			} elseif ($_SERVER['argv'][0] != '') {
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['argv'][0];
			}
		}
	}

	public function param(): Parameters
	{
		return $this->parameters;
	}

	public function db(): DatabaseMysqli
	{
		if (!isset($this->db))
			throw new Exception("Can not access database. Database not initialized!");
		return $this->db;
	}

	public function config($settingName)
	{
		return $this->config[$settingName] ?? NULL;
	}

	public function &get_config_array(): array
	{
		return $this->config;
	}

	//Headline
	public function headline($text, $url = '')
	{
		$this->tmpl->headline(strip_tags($text), $url);
	}

	//Titelleiste
	public function titlebar($text)
	{
		$this->tmpl->titlebar(strip_tags($text));
	}

	public function path()
	{
		return $this->path;
	}

	public function register_object(string $name, $object)
	{
		$this->registered_objects[$name] = $object;
	}

	public function &get_registered_object(string $name)
	{
		return $this->registered_objects[$name] ?? NULL;
	}

	public function init_modules()
	{
		foreach ($this->module_objects as $module) {
			$module->init();
		}
	}

	public function start_modules()
	{
		foreach ($this->module_objects as $module) {
			$module->startup();
		}
	}

	public function shutdown_modules()
	{
		foreach ($this->module_objects as $module) {
			$module->shutdown();
		}
	}

	//Stripslashes von Variablen
	function strpsl($array)
	{
		static $trimvars, $magicquotes;
		if (!isset($trimvars)) $trimvars = iif(isset($_REQUEST['apx_notrim']) && (int)$_REQUEST['apx_notrim'] && MODE == 'admin', 0, 1);

		foreach ($array as $key => $val) {
			if (is_array($val)) {
				$array[$key] = $this->strpsl($val);
				continue;
			}

			if ($trimvars) $val = trim($val);
			$val = stripslashes($val);
			if (is_string($val)) {
				if (substr($val, -6, 6) == '<br />') {
					$val = substr($val, 0, -6);
				}
			}
			$array[$key] = $val;
		}

		return $array;
	}

	//HTTP-URL
	function get_http()
	{
		if ($this->is_https()) {
			$port = iif($_SERVER['SERVER_PORT'] != 443, ':' . $_SERVER['SERVER_PORT']);
			$host = preg_replace('#:.*$#', '', $_SERVER['HTTP_HOST']); //Port entfernen
			return 'https://' . $host . $port;
		} else {
			$port = iif($_SERVER['SERVER_PORT'] != 80, ':' . $_SERVER['SERVER_PORT']);
			$host = preg_replace('#:.*$#', '', $_SERVER['HTTP_HOST']); //Port entfernen
			return 'http://' . $host . $port;
		}
	}

	function is_https()
	{
		return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
	}


	//Ordner
	function get_dir()
	{

		//Quellvariable auswählen
		if (isset($_SERVER['SCRIPT_NAME'])) $source = $_SERVER['SCRIPT_NAME'];
		else {
			$source = $_SERVER['PHP_SELF'];
		}
		if (substr($source, -1) == '/') $source .= 'file.php'; //Dateiname fehlt im Pfad (=> Fehler abfangen)
		$dir = dirname($source) . '/';

		//Relation zur Basis
		if (defined('BASEREL')) {
			$dir .= BASEREL;
		}

		$dir = str_replace('\\', '/', $dir);
		$dir = preg_replace('#/{2,}#', '/', $dir);
		while (preg_match('#/[A-Za-z0-9%_-]+/\.\.#im', $dir)) {
			$dir = preg_replace('#/[A-Za-z0-9%_-]+/\.\.#im', '', $dir);
		}
		$dir = str_replace('./', '', $dir);

		return $dir;
	}

	public function executePublicTemplateFunction(string $funcname, array $params): bool
	{
		$done = false;

		// Check all loaded Modules for Template function
		foreach ($this->module_objects as $id => $module) {
			if ($module->executePublicTemplateFunction($funcname, $params)) {
				$done = true;
				break;
			}
		}

		// If no new Module is available test old modules
		// @depicated
		if (!$done) {
			foreach ($this->functions as $m => $func) {
				if (file_exists(BASEDIR . $this->path->getmodulepath($m) . "tfunctions.php")) {
					if (isset($func[$funcname])) {
						include_once(BASEDIR . $this->path->getmodulepath($m) . "tfunctions.php");
						$f = $func[$funcname][0];

						if (function_exists($f)) {
							call_user_func_array($f, $params);
							$done = true;
						} else {
							ApexxError::ERROR("Can not call Template function: " . $f);
						}
					}
				}
			}
		}
		return $done;
	}

	///////////////////////////////////////////////// MODULE / AKTIONEN

	//Modul-Informationen holen
	private function get_modules()
	{
		if (!$this->config("installed")) return;

		$data = $this->db->fetch("SELECT * FROM " . PRE . "_modules WHERE active='1'");

		if (count($data)) {
			foreach ($data as $res) {
				$module = $action = $modset = array();
				list($modulename) = $res;

				// if module directory exists
				if (!is_dir(BASEDIR . getmodulepath($modulename))) continue;

				// if a class exists load the class and execute it!
				if (file_exists(BASEDIR . getmodulepath($modulename) . "module.class.php")) {
					$module_type = "\\Modules\\" . strtoupper($modulename[0]) . substr($modulename, 1) . "\\Module";
					$module = new $module_type($this);
				}

				// Old style module initialisation (Do not use anymore!)
				else {
					$module = new \Modules\Dummy\PublicModule($this, $modulename);
					$this->module_objects[$modulename] = $module;
				}

				$this->module_objects[$modulename] = $module;

				//Old style Modul-INIT
				$this->register_module($modulename, $module->get_info());

				$this->register_actions($modulename, $module->get_admin_actions());

				$this->register_functions($modulename, $module->get_template_functions());
				$this->register_functions($modulename, $module->get_admin_template_functions(), 'admin');
			}
		}
	}

	/**
	 * @deprecated
	 */
	public function get_module($module_name)
	{
		return $this->getModule($module_name);
	}

	public function getModuleList(): array
	{
		$result = array();
		foreach ($this->module_objects as $key => $module) {
			$result[] = $key;
		}
		return $result;
	}

	public function getModule($moduleName): ?Module
	{
		return $this->module_objects[$moduleName] ?? NULL;
	}

	//Ist ein Modul aktiv?
	function is_module($modulename): bool
	{
		return (isset($this->module_objects[$modulename]));
	}


	//Modul registieren
	function register_module($modulename, $info)
	{
		$this->modules[$modulename] = $info;
	}


	//Aktion registieren
	function register_actions($modulename, $info)
	{
		$this->actions[$modulename] = $info;
	}


	//Funktion registieren
	function register_functions($modulename, $info, $in = 'public')
	{
		if (!is_array($info) || !count($info)) return;
		if ($in == 'admin') $this->functions_admin[$modulename] = $info;
		else $this->functions[$modulename] = $info;
	}


	//Modul-Konfiguration auslesen
	function get_config()
	{
		if (!$this->config("installed")) return;

		$data = $this->db->fetch("SELECT * FROM " . PRE . "_config");
		if (!count($data)) return;

		foreach ($data as $res) {
			$modulename = $res['module'];
			$varname = $res['varname'];

			//Switch
			if ($res['type'] == 'switch') {
				$thevalue = iif($res['value'], 1, 0);
			}

			//String
			elseif ($res['type'] == 'string') {
				$thevalue = $res['value'];
			}

			//Multiline
			elseif ($res['type'] == 'multiline') {
				$thevalue = $res['value'];
			}

			//Arrays
			elseif ($res['type'] == 'array' || $res['type'] == 'array_keys') {
				$thevalue = unserialize($res['value']);
				if (!is_array($thevalue)) $thevalue = array();
			}

			//Integer
			elseif ($res['type'] == 'int') {
				$thevalue = (int)$res['value'];
			}

			//Float
			elseif ($res['type'] == 'float') {
				$thevalue = (float)$res['value'];
			}

			//Select
			elseif ($res['type'] == 'select') {
				$possible = unserialize($res['addnl']);

				foreach ($possible as $value => $descr) {
					if ($value == $res['value']) {
						$thevalue = $value;
						break;
					}
				}
			}

			if (!isset($thevalue)) continue;
			$this->config[$modulename][$varname] = $thevalue;
			unset($thevalue);
		}

		// Set the config to the global variable
		if (!$this->config("high_security"))
			$GLOBALS["set"] = &$this->config;
	}


	//Module sortieren
	function sort_modules()
	{
		uasort($this->modules, array($this, 'do_sort_modules'));
	}


	//Actions sortieren
	function sort_actions()
	{
		foreach ($this->modules as $module => $module_info) {
			uasort($this->actions[$module], array($this, 'do_sort_actions'));
		}
	}


	//Module sortieren (Navigation)
	function do_sort_modules($a, $b)
	{
		if ($a[1] == $b[1]) return 0;
		return ($a[1] > $b[1]) ? 1 : -1;
	}


	//Aktionen sortieren (Navigation)
	function do_sort_actions($a, $b)
	{
		if ($a[2] == $b[2]) return 0;
		return ($a[2] > $b[2]) ? 1 : -1;
	}



	////////////////////////////////////////////////////////////////////////////////// -> SPRACHPAKETE

	//Sprachpakete registrieren
	function get_languages()
	{
		if (!$this->config("installed")) return;

		$langinfo = &$this->config['main']['languages'];
		if (!is_array($langinfo) || !count($langinfo)) die('no langpack registered!');

		foreach ($langinfo as $dir => $res) {
			if ($res['default']) $this->language_default = $dir;
			$this->languages[$dir] = $res['title'];
		}

		if (!isset($this->language_default)) {
			reset($this->languages);
			$this->language_default = key($this->languages);
		}
	}



	////////////////////////////////////////////////////////////////////////////////// -> SEKTIONEN

	//Sektionen auslesen
	function get_sections()
	{
		if (!$this->config("installed")) return;
		$db = $this->db();

		$data = $this->db->fetch("SELECT * FROM " . PRE . "_sections ORDER BY title ASC", 1);
		if (!count($data)) return;

		foreach ($data as $res) {
			$this->sections[$res['id']] = $res;
			if ($res['default']) $this->section_default = $res['id'];
		}

		if (!$this->section_default) {
			reset($this->sections);
			$this->section_default = key($this->sections);
		}
	}


	//Aktuelle Sektion
	function section_id($id = false)
	{
		if ($id === false) return $this->section['id'];

		$id = (int)$id;
		$this->section = $this->sections[$id];
	}


	//Sektion aktiviert?
	function section_is_active($id)
	{
		$id = (int)$id;
		if ($this->sections[$id]['active']) return true;
		return false;
	}



	////////////////////////////////////////////////////////////////////////////////// -> INTERNE VARIABLEN SETZEN(AUSLESEN

	//Aktives Module
	function module($module = false)
	{
		if ($module === false) return $this->active_module;
		if (!$this->is_module($module)) die('"' . $module . '" is not a valid/active module-ID!');
		$this->active_module = $module;
	}

	/**
	 * Dummy function for Admin 
	 */
	function action()
	{
		return "";
	}

	public function shutdown()
	{
		global $GLOBALS;

		//Shutdown durchführen
		foreach ($this->modules as $module => $info) {
			if (!file_exists(BASEDIR . getmodulepath($module) . 'shutdown.php')) continue;
			include_once(BASEDIR . getmodulepath($module) . 'shutdown.php');
		}

		///////////////////////////////////////////////////////////////////////////////////////// SCRIPT BEENDEN

		//Ausgabe vorbereiten
		$this->tmpl->out();

		//MySQL Verbindung schließen
		try {
			$this->db()->close();
		} catch (Exception  $ex) {
			// Ignore: PHP will close the connection if the script ends!
		}

		//Renderzeit
		if ($this->config('rendertime')) {
			list($usec, $sec) = explode(' ', microtime());
			$b2 = ((float)$usec + (float)$sec);
			list($usec, $sec) = explode(' ', $_BENCH);
			$b1 = ((float)$usec + (float)$sec);
			echo '<div style="font-size:11px;">Processing: ' . ($b2 - $b1) . ' sec.</div>';
		}

		if ($this->config('debug_session')) {
			echo $this->session()->debug();
		}

		//Script beenden, nachfolgenden Code nicht ausführen!
		exit;
	}

	public function debugCheck()
	{
		ApexxError::DEBUG("debugCheck");
		foreach ($this->module_objects as $module) {
			$module->debugCheck();
		}
		ApexxError::DEBUG("debugCheck DONE");
		exit;
	}

	/**
	 * This function will show a message with a link
	 * @param string $text Text of the message ('back' if it should show the generic back message)
	 * @param string $link Link in the message ('back' if it should do a history.back(), false if no link should be included)
	 */
	public function message(string $text, string $link = ""): void
	{
		//Standard-Back-Message
		if ($text == 'back') {
			$text = $this->lang->get('CORE_BACK');
			$link = 'javascript:history.back()';
		}

		//Standard-Back-Link
		if ($link == 'back') {
			$link = 'javascript:history.back()';
		}

		$this->tmpl->loaddesign('message');
		if ($link) {
			$this->tmpl->assign_static('REDIRECT', $link);
		}

		echo $text;

		if (MODE != 'admin') require(BASEDIR . 'lib/_end.php');
	}
} //END CLASS
