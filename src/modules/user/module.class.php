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

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');

class Module extends \Module
{
	public function __construct(\apexx &$apx)
	{
		parent::__construct($apx,
			'user',
			array(),
			array('main' => '1.2.0'),
			'1.2.4',
			'Christian Scheb, Carsten Grings',
			'http://www.stylemotion.de'
		);

		$this->registerPublicModule(new PublicModule($this));
		$this->registerSetup(new Setup());


		// Admin Aktionen registrieren 
		$this->register_admin_action('login', 0, 0, 1, 1);
		$this->register_admin_action('logout', 0, 0, 2, 1);
		$this->register_admin_action('autologout', 0, 0, 3, 1);

		$this->register_admin_action('show', 0, 1, 4, 0);
		$this->register_admin_action('add', 0, 1, 5, 0);
		$this->register_admin_action('edit', 0, 0, 6, 0);
		$this->register_admin_action('del', 0, 0, 7, 0);
		$this->register_admin_action('enable', 0, 0, 8, 0);

		$this->register_admin_action('gshow', 0, 1, 12, 0);
		$this->register_admin_action('gadd', 0, 0, 13, 0);
		$this->register_admin_action('gedit', 0, 0, 14, 0);
		$this->register_admin_action('gclean', 0, 0, 15, 0);
		$this->register_admin_action('gdel', 0, 0, 16, 0);

		$this->register_admin_action('profile', 0, 0, 98, 0);
		$this->register_admin_action('myprofile', 0, 1, 99, 1);

		$this->register_admin_action('guestbook', 0, 1, 991, 0);
		$this->register_admin_action('blog', 0, 1, 992, 0);
		$this->register_admin_action('gallery', 0, 1, 993, 0);

		$this->register_admin_action('sendmail', 0, 1, 2000, 0);
		$this->register_admin_action('sendpm', 0, 1, 2001, 0);		

		$this->register_admin_template_function('USER', 'user_team', true);
		$this->register_admin_template_function('USERGROUPS', 'user_groups', true);
		
	}

	public function init()
	{
		// Klasse sofort initialisieren für Sprachpaket und Userinfos
		$user = new User($this->apx);
		$user->init();
		$this->apx->register_object('user', $user);

		{
			/**
			 * @deprecated Global variables will be removed
			 */
			$GLOBALS["user"] = $user;
		}		
	}

	public function startup()
	{
		$user = $this->apx->get_registered_object('user');

		//Statische Variablen setzen
		$this->apx->tmpl->assign_static('LOGGED_ID', isset($user->info['userid']) ? $user->info['userid'] : 0);
		$this->apx->tmpl->assign_static('LOGGED_GROUPID', $user->info['groupid']??0);
		$this->apx->tmpl->assign_static('LOGGED_GROUPNAME', replace($user->info['name']));
		if (isset($user->info['userid']) && $user->info['userid'])
		{

			$this->apx->tmpl->assign_static('LOGGED_USERNAME', replace($user->info['username']));
			$this->apx->tmpl->assign_static('LOGGED_EMAIL', replace($user->info['email']));
			$this->apx->tmpl->assign_static('LOGGED_EMAIL_ENCRYPTED', replace(cryptMail($user->info['email'])));
			$this->apx->tmpl->assign_static('LOGGED_ISTEAM', $user->is_team_member());
			$this->apx->tmpl->assign_static('LOGGED_PROFILE', $user->mkprofile($user->info['userid'], $user->info['username']));

			//Noch mehr davon
			$this->apx->tmpl->assign_static('LOGGED_ICQ', replace($user->info['icq']));
			$this->apx->tmpl->assign_static('LOGGED_AIM', replace($user->info['aim']));
			$this->apx->tmpl->assign_static('LOGGED_YIM', replace($user->info['yim']));
			$this->apx->tmpl->assign_static('LOGGED_MSN', replace($user->info['msn']));
			$this->apx->tmpl->assign_static('LOGGED_SKYPE', $user->info['skype']);
			$this->apx->tmpl->assign_static('LOGGED_HOMEPAGE', replace($user->info['homepage']));
			$this->apx->tmpl->assign_static('LOGGED_REALNAME', replace($user->info['realname']));
			$this->apx->tmpl->assign_static('LOGGED_GENDER', $user->info['gender']);
			$this->apx->tmpl->assign_static('LOGGED_CITY', replace($user->info['city']));
			$this->apx->tmpl->assign_static('LOGGED_PLZ', replace($user->info['plz']));
			$this->apx->tmpl->assign_static('LOGGED_COUNTRY', $user->info['country']);
			$this->apx->tmpl->assign_static('LOGGED_INTERESTS', replace($user->info['interests']));
			$this->apx->tmpl->assign_static('LOGGED_WORK', replace($user->info['work']));
			$this->apx->tmpl->assign_static('LOGGED_LASTVISIT', $user->info['lastonline']);
			$this->apx->tmpl->assign_static('LOGGED_SIGNATURE', $user->mksig($user->info));
			$this->apx->tmpl->assign_static('LOGGED_AVATAR', $user->mkavatar($user->info));
			$this->apx->tmpl->assign_static('LOGGED_AVATAR_TITLE', $user->mkavtitle($user->info));
		}

		//Theme erzwingen
		if (isset($user->info['pub_theme']) && $user->info['pub_theme'])
		{
			$this->apx->tmpl->set_theme($user->info['pub_theme']);
		}
	}

	public function shutdown()
	{
		$user = $this->apx->get_registered_object('user');
		if(!$user)return;

		//PM-Popup
		if (isset($user->info['pmpopup']) && $user->info['pmpopup'])
		{
			$this->apx->lang->drop('pmpopup', 'user');
			$this->apx->db()->query("UPDATE " . PRE . "_user SET pmpopup='0' WHERE userid='" . $user->info['userid'] . "' LIMIT 1");

			$msgtext = addslashes($this->apx->lang->get('MSG_PMPOPUP'));
			$msglink = mklink('user.php?action=pms', 'user,pms.html');

			echo <<<CODE
				<script language="JavaScript" type="text/javascript">
				<!--

				var lang_pmpop='{$msgtext}';

				window.onload = function() {
					getopen=confirm(lang_pmpop);
					if ( getopen==true ) {
						win = window.open('{$msglink}','pmwindow');
						win.focus();
					}
				}

				//-->
				</script>

				CODE;
		}		
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//Download-Größe
	function getsize($fsize, $digits = 1)
	{
		$fsize = (float)$fsize;
		if ($digits) $format = '%01.' . $digits . 'f';
		else $format = '%01d';

		if ($fsize < 1024) return $fsize . ' Byte';
		if ($fsize >= 1024 && $fsize < 1024 * 1024) return  number_format($fsize / (1024), $digits, ',', '') . ' KB';
		if ($fsize >= 1024 * 1024 && $fsize < 1024 * 1024 * 1024) return number_format($fsize / (1024 * 1024), $digits, ',', '') . ' MB';
		if ($fsize >= 1024 * 1024 * 1024 && $fsize < 1024 * 1024 * 1024 * 1024) return number_format($fsize / (1024 * 1024 * 1024), $digits, ',', '') . ' GB';
		return number_format($fsize / (1024 * 1024 * 1024 * 1024), $digits, ',', '') . ' TB';
	}




	//Besuch zählen
	function count_visit($object, $id)
	{
		global $apx, $set, $db, $user;
		if (!$user->info['userid']) return;
		$db->query("DELETE FROM " . PRE . "_user_visits WHERE object='" . $object . "' AND userid='" . $user->info['userid'] . "'");
		$db->query("INSERT INTO " . PRE . "_user_visits (object,id,userid,time) VALUES ('" . $object . "','" . $id . "','" . $user->info['userid'] . "','" . time() . "')");
	}



	//Besucher assign
	function assign_visitors($object, $id, &$tmpl)
	{
		global $apx, $set, $db, $user;

		$userdata = array();
		$data = $db->fetch("SELECT u.userid,u.username,u.groupid,u.realname,u.gender,u.city,u.plz,u.country,u.city,u.lastactive,u.pub_invisible,u.avatar,u.avatar_title,u.custom1,u.custom2,u.custom3,u.custom4,u.custom5,u.custom6,u.custom7,u.custom8,u.custom9,u.custom10 FROM " . PRE . "_user_visits AS v LEFT JOIN " . PRE . "_user AS u USING(userid) WHERE v.object='" . addslashes($object) . "' AND v.id='" . intval($id) . "' AND v.time>='" . (time() - 24 * 3600) . "' ORDER BY u.username ASC");
		if (count($data))
		{
			$i = 0;
			foreach ($data as $res)
			{
				++$i;

				$userdata[$i]['ID'] = $res['userid'];
				$userdata[$i]['USERID'] = $res['userid'];
				$userdata[$i]['USERNAME'] = replace($res['username']);
				$userdata[$i]['GROUPID'] = $res['groupid'];
				$userdata[$i]['ONLINE'] = iif(!$res['pub_invisible'] && ($res['lastactive'] + $set['user']['timeout'] * 60) >= time(), 1, 0);
				$userdata[$i]['REALNAME'] = replace($res['realname']);
				$userdata[$i]['GENDER'] = $res['gender'];
				$userdata[$i]['CITY'] = replace($res['city']);
				$userdata[$i]['PLZ'] = replace($res['plz']);
				$userdata[$i]['COUNTRY'] = $res['country'];
				$userdata[$i]['LASTACTIVE'] = $res['lastactive'];
				$userdata[$i]['AVATAR'] = $user->mkavatar($res);
				$userdata[$i]['AVATAR_TITLE'] = $user->mkavtitle($res);

				//Custom-Felder
				for ($ii = 1; $ii <= 10; $ii++)
				{
					$tabledata[$i]['CUSTOM' . $ii . '_NAME'] = $set['user']['cusfield_names'][($ii - 1)];
					$tabledata[$i]['CUSTOM' . $ii] = compatible_hsc($res['custom' . $ii]);
				}
			}
		}

		$tmpl->assign('VISITOR', $userdata);
	}



	//Links zu Profil-Funktionen
	function assign_profile_links(&$tmpl, $userinfo)
	{
		global $apx, $set, $db, $user;

		$link_profile = mklink(
			'user.php?action=profile&amp;id=' . $userinfo['userid'],
			'user,profile,' . $userinfo['userid'] . urlformat($userinfo['username']) . '.html'
		);
		if ($set['user']['blog'])
		{
			$link_blog = mklink(
				'user.php?action=blog&amp;id=' . $userinfo['userid'],
				'user,blog,' . $userinfo['userid'] . ',1.html'
			);
		}
		if ($set['user']['gallery'])
		{
			$link_gallery = mklink(
				'user.php?action=gallery&amp;id=' . $userinfo['userid'],
				'user,gallery,' . $userinfo['userid'] . ',0,0.html'
			);
		}
		if ($set['user']['guestbook'] && $userinfo['pub_usegb'])
		{
			$link_guestbook = mklink(
				'user.php?action=guestbook&amp;id=' . $userinfo['userid'],
				'user,guestbook,' . $userinfo['userid'] . ',1.html'
			);
		}
		if ($apx->is_module('products') && $set['products']['collection'])
		{
			$link_collection = mklink(
				'user.php?action=collection&amp;id=' . $userinfo['userid'],
				'user,collection,' . $userinfo['userid'] . ',0,1.html'
			);
		}

		$tmpl->assign('LINK_PROFILE', $link_profile);
		$tmpl->assign('LINK_BLOG', $link_blog);
		$tmpl->assign('LINK_GALLERY', $link_gallery);
		$tmpl->assign('LINK_GUESTBOOK', $link_guestbook);
		$tmpl->assign('LINK_COLLECTION', $link_collection);
	}

}