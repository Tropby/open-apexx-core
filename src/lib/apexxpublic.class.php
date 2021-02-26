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
if (!defined('APXRUN')) die('You are not allowed to execute this file directly!');

class ApexxPublic extends Apexx
{
	// Current Language
	var $lang;

	/**
	 * @var tmpl Current template 
	 */
	var $tmpl;

	var $active_section;

	//STARTUP
	protected function __construct()
	{
		parent::__construct();

		//Template Engine
		$this->tmpl = new TemplatesPublic( $this );

		//Modul-Funktionen laden
		$this->init_modules();

		//Sektionen initialisieren
		$this->init_section();

		//Sprachpaket initialisieren
		if( isset( $this->lang ) )
			$this->lang->init();

		// Init all modules
		$this->start_modules();		
	}

	/**
	 * Execute Module
	 * Executes a module public class
	 * @param module Module to execute
	 */
	public function execute_module(string $module, string $action="index")
	{
		if ($this->is_module($module) ?? false)
		{
			// set active module
			$this->module($module);

			// drop language
			$this->lang->drop('all');

			// execute the module
			$this->get_module($module)->executePublic($action);
		}
		else
		{
			ApexxError::ERROR("Can not execute module \"" . $module . "\". Module unknown!");
		}
	}

	//Sektion wählen
	function init_section()
	{
		// get section id if possible
		$secId = 0;
		if( $this->param()->requestIf('sec') )
		{
			$secId = $this->param()->requestInt('sec');
		}

		//Sektion auswählen
		if ($secId && isset($this->sections[$secId]) && $this->sections[$secId]['active'])
		{
			$this->section_check($secId);
			$this->section_id($secId);
		}
		elseif ($this->config('main')['forcesection'])
		{
			$this->section_id($this->section_default);
		}

		//Theme erzwingen
		if ($this->section_id() && $this->section['theme'])
		{
			$this->tmpl->set_theme($this->section['theme']);
		}

		//Sprache erzwingen
		if ($this->section_id() && $this->section['lang'])
		{
			$this->lang->langid($this->section['lang']);
		}

		$this->tmpl->assign_static('WEBSITE_NAME', $this->config('main')['websitename']);
		$this->tmpl->assign_static('SECTION_ID', $this->section_id());
		$this->tmpl->assign_static('SECTION_TITLE', isset($this->section['title']) ? $this->section['title'] : "");
		$this->tmpl->assign_static('SECTION_LANG', isset($this->section['lang']) ? $this->section['lang'] : "");
		$this->tmpl->assign_static('SECTION_VIRTUAL', isset($this->section['virtual']) ? $this->section['virtual'] : "");
		$this->tmpl->assign_static('SECTION_THEME', isset($this->section['theme']) ? $this->section['theme'] : "");
	}

	//Darf man eine Sektion betreten?
	function section_check($id)
	{
		// Get user object. Return if no user object is registered (User-Module is missing)
		$user = $this->get_registered_object('user');
		if( !$user )return;

		if ($user->info['section_access'] == 'all') 
		{
			$secacc = 'all';
		}
		else
		{
			$secacc = unserialize($user->info['section_access']);
			if (!is_array($secacc)) $secacc = array();
		}

		//Beschränkung durch Benutzergruppe
		if ($secacc != 'all' && !in_array($id, $secacc) && $id != $this->section_default)
		{
			$this->lang->init(); //Sprachpaket ist noch nicht initialisiert!
			$indexpage = mklink('index.php', 'index.html', $this->section_default);
			message($this->sections[$id]['msg_noaccess'], $indexpage);
		}
	}

	/**
	 * Dummy function for apexx system. Used by Admin (apexx_admin)
	 */
	public function action()
	{		
		return;
	}

} //END CLASS

?>