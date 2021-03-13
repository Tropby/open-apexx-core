<?php

/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|             (c) Copyright 2005, Christian Scheb               |
|                  http://www.stylemotion.de                    |
|                                                               |
|---------------------------------------------------------------|
| DO NOT REMOVE ANY COPYRIGHTS WITHOUT PERMISSION!              |
| SOFTWARE BELONGS TO ITS AUTHORS!                              |
\***************************************************************/

class Session
{
	private string $sessioName;
	private int $now;
	private string $sessionId = '';
	private string $ownerId = '';

	//Session erzeugen
	function __construct($sessioName = 'sid')
	{
		if( !isset($_COOKIE['acceptCookies']) || !$_COOKIE['acceptCookies'] )
		{
			return;
		}

		$this->sessioName = $sessioName;
		session_name($this->sessioName);
		$this->now = time();
		$this->ownerId = $this->getOwnerId();		

		//Versuch aktuelle Session zu übernehmen
		$this->resumeSession();

		//Neue Session erzeugen wenn Übernahme gescheitert oder keine Sid
		if (!$this->sessionId)
		{
			$this->createSession();
		}
	}

	//Neue Session erzeugen
	function createSession()
	{
		session_start();
		$this->sessionId = session_id();
		$_SESSION['__ownerid'] = $this->getOwnerId();
	}

	//Session wiederaufnehmen
	function resumeSession()
	{
		session_start();
		$this->sessionId = session_id();

		//Anscheinend eine neue Session
		if (!isset($_SESSION['__ownerid']))
		{
			$_SESSION['__ownerid'] = $this->getOwnerId();
		}

		//Session kann nicht aufgenommen werden => Neu erzeugen
		while( $_SESSION['__ownerid'] != $this->getOwnerId() )
		{
			$this->destroy();

			//Neue Session starten
			$this->sessionId = md5(uniqid('newsession') . microtime());
			session_id($this->sessionId);
			session_start();
			$_SESSION['__ownerid'] = $this->getOwnerId();
		}
	}

	//Session-ID zurückgeben
	function getSid()
	{
		return $this->sessionId;
	}

	//Session-Variable setzen
	function set($sessioName, $value)
	{
		$_SESSION['_apxses_' . $sessioName] = $value;
	}

	// Show session data
	function debug()
	{
		$sessiondata = print_r($_SESSION, true);
		$ret = "<h1>Session data:</h1>";
		$ret .= "<h2>".$this->session_id."</h2>";
		$ret .= "<h3>" . $this->session_name . "</h3>";
		$ret .= "<pre>".$sessiondata."</pre>";		
		return $ret;
	}

	//Session-Variable auslesen
	function get($sessioName)
	{
		if (isset($_SESSION['_apxses_' . $sessioName])) return $_SESSION['_apxses_' . $sessioName];
		else return null;
	}

	//Session-Variable löschen
	function clear($sessioName, $value)
	{
		unset($_SESSION['_apxses_' . $sessioName]);
	}

	//Session-Daten speichern
	function save()
	{
	}

	//Session beenden
	function destroy()
	{
		@session_destroy();
		$_SESSION = array();
	}

	//Owner-ID erzeugen
	function getOwnerId() : string
	{
		$ip = implode('.', array_slice(explode('.', get_remoteaddr()), 0, 3));
		return md5(getenv('HTTP_USER_AGENT') . $ip);
	}
}
