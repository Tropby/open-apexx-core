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
	function __construct()
	{
		parent::__construct();

		//Template Engine
		$this->tmpl = new TemplatesAdmin( $this );

		//Modul-Funktionen laden
		$this->init_modules();

		//Sprachpaket initialisieren
		$this->lang->init();

		// Init all modules
		$this->start_modules();			
	}


	////////////////////////////////////////////////////////////////////////////////// -> AKTION AUSFÜHREN

	//Aktion ausführen
	function executeModule( $module, $action )
	{
		$user = $this->get_registered_object("user");			
		
		if( $module != "user" && $action != "login" )
		{
			if (!$user->has_right($module.".".$action)) 
			{
				if ($user->info['userid']) 
				{
					$this->message($this->lang->get('CORE_NORIGHT'));
					exit;
				} 
				else 
				{
					header('Location: action.php?action=user.login');
					exit;
				}
			} 	
		}

		if ($this->is_module($module) ?? false)
		{
			// set active module
			$this->module($module);

			// drop language
			$this->lang->dropaction($module, $action);

			// execute the module
			try
			{
				$this->getModule($module)->executeAdmin($action);
			}
			catch(Exception $ex)
			{
				if (!file_exists(BASEDIR . getmodulepath($this->module()) . 'admin.php'))
				{
					message($this->lang->get('CORE_MISSADMIN'));
				}
				elseif (!$user->has_right($_REQUEST['action']))
				{
					if ($user->info['userid'])
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

					if (method_exists($adminclass, $action))
					{
						$adminclass->$action();
					} 
					else
					{
						message($this->lang->get('CORE_METHODFAIL'));
					}
				}
			}
		}
	}
} //END CLASS
