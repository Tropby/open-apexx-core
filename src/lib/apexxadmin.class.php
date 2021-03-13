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

class ApexxAdmin extends Apexx
{

	var $active_action;

	var $user;
	var $tmpl;
	var $lang;
	var $session;

	function __construct()
	{
		parent::__construct();

		if (!isset($_REQUEST['action']))
			$_REQUEST['action'] = 'main.index';

		$loadmodule = explode('.', $_REQUEST['action']);
		if (count($loadmodule) != 2) {
			die('WRONG SYNTAX OF ACTION PARAM!');
		}
		$this->module($loadmodule[0]);
		$this->action($loadmodule[1]);
	}


	////////////////////////////////////////////////////////////////////////////////// -> AKTION AUSFÜHREN

	//Aktion ausführen
	function executeAction()
	{
		if (!file_exists(BASEDIR . getmodulepath($this->module()) . 'admin.php'))
		{
			message($this->lang->get('CORE_MISSADMIN'));
		}
		elseif (!isset($this->actions[$this->module()][$this->action()]))
		{
			message($this->lang->get('CORE_NOTREG'));
		}
		elseif (!$this->user->has_right($_REQUEST['action'])) {
			if ($this->user->info['userid']) 
			{
				message($this->lang->get('CORE_NORIGHT'));
			} 
			else 
			{
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: action.php?action=user.login');
				exit;
			}
		} 
		else 
		{
			$this->lang->dropaction(); //Action-Sprachpaket des Moduls laden
			require_once(BASEDIR . getmodulepath($this->module()) . 'admin.php');
			$adminclass = new action;

			$action = $this->action();
			if (method_exists($adminclass, $action)) $adminclass->$action();
			else message($this->lang->get('CORE_METHODFAIL'));
		}
	}


	/*
//Multifunktion ausführen
function execute_multifunc(&$class) {
	if ( !is_array($_POST['multi']) ) return;
	
	foreach ( $_POST['multi'] AS $key => $val ) {
		if ( $val=='1' ) continue;
		unset($_POST['multi'][$key]);
	}
	
	if ( !count($_POST['multi']) ) return;
	
	foreach ( $this->actions[$this->module()] AS $action => $trash ) {
		if ( !$_POST['multi_'.$action] ) continue;
		if ( !$this->user->has_right($this->module().'.'.$action) ) continue;
	
		$callfunc='multi_'.$action;
		return $class->$callfunc();
	}
}
*/


	////////////////////////////////////////////////////////////////////////////////// -> INTERNE VARIABLEN SETZEN(AUSLESEN

	//Aktives Module
	//-> class apexx


	//Aktive Aktion
	function action($action = false)
	{
		if ($action === false) return $this->active_action;
		$this->active_action = $action;
	}
} //END CLASS
