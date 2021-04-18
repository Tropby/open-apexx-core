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

namespace Modules\User;

//Diese Klasse dient zur Initialisierung des Benutzersystems!
class User
{
	// TODO: Should be private
	/* private */
	/**
	 * @deprecated This variable will be private in the future!
	 */
	public $info = array();

	// Zugriff aufs Apexx System
	private $apx;

	public function __construct(\Apexx &$apx)
	{
		$this->apx = &$apx;

		if ($this->apx->config('high_security'))
			unset($this->info);
	}

	//////////////////////////////////////////////////////////////////////////////////////////// STARTUP
	function init()
	{
		if( $this->apx->session()->get($this->apx->config('main')['cookie_pre'] . '_userid') )
		{
			$this->info = $this->apx->db()->first(
				"SELECT * 
				FROM " . PRE . "_user AS a 
				LEFT JOIN " . PRE . "_user_groups AS b USING (groupid) 
				WHERE ( userid='" . intval($this->apx->session()->get($this->apx->config('main')['cookie_pre'] . '_userid')) . "' ) LIMIT 1", 1);

			if ((!$this->info['userid'] || !$this->info['active'] || $this->info['reg_key']) && $this->apx->module() != 'user' && $_REQUEST['action'] != 'logout')
			{
				$link = str_replace('&amp;', '&', mklink(
					'user.php?action=logout',
					'user,logout.html'
				));
				header('location:' . $link);
				exit;
			}

			$this->update_lastonline();
		}
		else
		{
			$this->info = $this->apx->db()->first("SELECT * FROM " . PRE . "_user_groups WHERE groupid='3' LIMIT 1", 1);
		}

		$this->apx->lang->langid(isset($this->info['pub_lang']) ? $this->info['pub_lang'] : "");
		if ($this->apx->config('user')['onlinelist'])
		{
			$this->update_onlinelist();
		}
	}

	public function id() : ?int
	{
		return $this->info["userid"]??NULL;
	}

	public function signature() : ?string
	{
		return $this->info["signature"]??NULL;
	}

	//Buddie-Liste holen
	function get_buddies()
	{
		$db = $this->apx->db();
		if (!($this->info['userid'] ?? 0)) return array();
		if (isset($this->info['friends'])) return $this->info['friends'];
		$data = $db->fetch("SELECT friendid FROM " . PRE . "_user_friends WHERE userid='" . $this->info['userid'] . "'");
		$this->info['friends'] = get_ids($data, 'friendid');
		return $this->info['friends'];
	}



	//Zuletzt online aktualisieren
	function update_lastonline()
	{
		$db = $this->apx->db();

		if (($this->info['lastactive'] + $this->apx->config('user')['timeout'] * 60) < time())
		{
			$db->query("UPDATE " . PRE . "_user SET lastonline=lastactive,lastactive='" . time() . "' WHERE userid='" . $this->info['userid'] . "' LIMIT 1");
			$this->info['lastonline'] = $this->info['lastactive'];
			$this->info['lastactive'] = time();
		}
		else
		{
			$db->query("UPDATE " . PRE . "_user SET lastactive='" . time() . "' WHERE userid='" . $this->info['userid'] . "' LIMIT 1");
			$this->info['lastactive'] = time();
		}
	}



	//Onlineliste
	function update_onlinelist()
	{
		$db = $this->apx->db();
		$db->query("DELETE FROM " . PRE . "_user_online WHERE ( time<'" . (time() - $this->apx->config('user')['timeout'] * 60) . "' OR ip='" . ip2integer(get_remoteaddr()) . "' " . iif(isset($this->info['userid']), " OR userid='" . (isset($this->info['userid']) ? $this->info['userid'] : "") . "' ") . ")");
		$db->query("INSERT IGNORE INTO " . PRE . "_user_online VALUES ('" . (isset($this->info['userid']) ? $this->info['userid'] : "") . "','" . ip2integer(get_remoteaddr()) . "','" . time() . "','" . (isset($this->info['pub_invisible']) ? $this->info['pub_invisible'] : "") . "','" . addslashes($_SERVER['REQUEST_URI']) . "')");
	}



	//Hat der User Admin-Rechte?
	function is_team_member($userid = false)
	{
		$db = $this->apx->db();
		if ($userid === false)
		{
			if ($this->info['gtype'] == 'admin' || $this->info['gtype'] == 'indiv') return true;
			else return false;
		}

		$userid = (int)$userid;
		if (!$userid) return false;

		$res = $db->first("SELECT a.userid,b.gtype FROM " . PRE . "_user LEFT JOIN " . PRE . "_user_groups USING(groupid) WHERE userid='" . $userid . "' LIMIT 1");
		if (!$res['userid']) return false;
		if ($res['gtype'] == 'admin' || $res['gtype'] == 'indiv') return true;
		return false;
	}



	//Ist der Nutzer Admin?
	function is_admin($userid = false)
	{
		$db = $this->apx->db();
		if ($userid === false)
		{
			return $this->info['gtype'] == 'admin';
		}
		elseif (!$userid)
		{
			return false;
		}
		else
		{
			$res = $db->first("SELECT b.gtype FROM " . PRE . "_user LEFT JOIN " . PRE . "_user_groups USING(groupid) WHERE userid='" . $userid . "' LIMIT 1");
			return $this->info['gtype'] == 'admin';
		}
	}



	//Wird der User von einem anderen ignoriert?
	function ignore($userid, &$reasonvar)
	{
		$db = $this->apx->db();
		$userid = (int)$userid;
		list($check, $reason) = $db->first("SELECT userid,reason FROM " . PRE . "_user_ignore WHERE userid='" . $userid . "' AND ignored='" . $this->info['userid'] . "' LIMIT 1");
		$reasonvar = $reason;
		if ($check) return true;
		else return false;
	}

	//////////////////////////////////////////////////////////////////////////////////////////// AUSGABE GENERIEREN

	//Signatur
	function mksig($info, $nospacer = false)
	{
		$text = $info['signature'];
		if (!$text) return '';

		if ($this->apx->config('user')['sig_badwords']) $text = badwords($text);
		$text = replace($text, 1);
		if ($this->apx->config('user')['sig_allowsmilies']) $text = dbsmilies($text);
		if ($this->apx->config('user')['sig_allowcode']) $text = dbcodes($text, 1);
		if (!$nospacer) $text = $this->apx->config('user')['sigspace'] . $text;

		return $text;
	}

	//Profil-Link erzeugen
	function mkprofile($userid, $username = '')
	{
		$userid = (int)$userid;
		if (!$userid) return '#';

		$link = mklink(
			'user.php?action=profile&amp;id=' . $userid,
			'user,profile,' . $userid . urlformat($username) . '.html'
		);

		return $link;
	}

	//Avatar
	function mkavatar($info)
	{
		if (!$info['avatar']) return '';
		$path = HTTPDIR . getpath('uploads') . 'user/' . $info['avatar'];
		return $path;
	}

	//Avatar-Titel
	function mkavtitle($info)
	{
		$title = $info['avatar_title'];
		if (!$title) return '';

		if ($this->apx->config('user')['avatar_badwords']) $title = badwords($title);
		return compatible_hsc($title);
	}

	//////////////////////////////////////////////////////////////////////////////////////////// BENUTZERVERWALTUNG

	//User Info
	function get_info($userid = false, $fields = '*')
	{
		$db = $this->apx->db();
		if ($userid === false) return $this->info;
		$userid = (int)$userid;

		$res = $db->first("SELECT " . $fields . " FROM " . PRE . "_user WHERE userid='" . $userid . "' LIMIT 1");
		$res['buddies'] = $this->get_buddies($res['buddies']);

		return $res;
	}

	//User Multi Info
	function get_info_multi($userids, $fields = '*')
	{
		$db = $this->apx->db();
		if (!is_array($userids)) return array();
		$userids = array_map('intval', $userids);

		$data = $db->fetch_index("SELECT userid," . $fields . " FROM " . PRE . "_user WHERE userid IN (" . implode(',', $userids) . ")", 'userid');
		foreach ($data as $key => $res)
		{
			if (!isset($res['buddies'])) break;
			$data[$key]['buddies'] = $this->get_buddies($res['buddies']);
		}

		return $data;
	}

	//Username checken
	function block_username($username)
	{
		if (!count($this->apx->config('user')['blockusername'])) return false;

		foreach ($this->apx->config('user')['blockusername'] as $string)
		{
			$strpos = strpos(strtolower($username), strtolower($string));
			if ($strpos === false) continue;
			return substr($username, $strpos, strlen($string));
		}

		return false;
	}

	//Pr�fen ob Benutzer ein Buddy ist
	function is_buddy($id)
	{
		$friends = $this->get_buddies();
		return in_array($id, $friends);
	}

	//Pr�fen ob Benutzer Buddy eines anderen Benutzers ist
	function is_buddy_of($id)
	{
		$db = $this->apx->db();
		$id = (int)$id;
		if (!$id) return false;
		list($check) = $db->first("SELECT userid FROM " . PRE . "_user_friends WHERE userid='" . $id . "' AND friendid='" . $this->info['userid'] . "' LIMIT 1");
		return $check ? true : false;
	}

	//Hat der User das Recht diese Aktion auszuf�hren?
	function has_right($action)
	{
		$this->give_default_rights();
		$this->get_rights();

		if (isset($this->rights['global']) && $this->rights['global'] == 'global') return true;
		if (is_array($this->rights) && in_array($action, $this->rights)) return true;

		return false;
	}

	//Rechte holen
	function get_rights()
	{
		global $db;

		//Admin -> alle Rechte
		if ($this->info['gtype'] == 'admin')
		{
			$this->rights['global'] = $this->sprights['global'] = 'global';
			return;
		}

		$this->rights = unserialize($this->info['rights']);
		$this->sprights = unserialize($this->info['sprights']);
	}


	//Standard-Rechte setzen
	function give_default_rights()
	{
		global $apx;
		foreach ($apx->actions as $module => $info)
		{
			foreach ($info as $action => $ainfo)
			{
				if (!$ainfo[3] == 1) continue;
				$this->rights[] = $module . '.' . $action;
			}
		}
	}

	//Hat der User Sonderrechte f�r diese Aktion?
	function has_spright($action)
	{
		$this->give_default_rights();
		$this->get_rights();

		if (is_array($this->sprights) && $this->sprights['global'] == 'global') return true;
		if (is_array($this->sprights) && in_array($action, $this->sprights)) return true;

		return false;
	}

} //END CLASS
