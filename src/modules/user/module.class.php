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
			'Christian Scheb',
			'http://www.stylemotion.de'
		);

		$this->registerPublicModule(new PublicModule($this));

		//$this->register_template_function('USER_INFO', 'user_info', true);
		$this->register_template_function('USERONLINE', 'user_online', false);
		$this->register_template_function('NEWPMS', 'user_newpms', false);
		$this->register_template_function('NEWGBENTRIES', 'user_newgbs', false);
		$this->register_template_function('ONLINELIST', 'user_onlinelist', true);
		$this->register_template_function('LOGINBOX', 'user_loginbox', false);
		$this->register_template_function('BIRTHDAYS', 'user_birthdays', true);
		$this->register_template_function('BIRTHDAYS_TOMORROW', 'user_birthdays_tomorrow', true);
		$this->register_template_function('BIRTHDAYS_NEXTDAYS', 'user_birthdays_nextdays', true);
		$this->register_template_function('BUDDYLIST', 'user_buddylist', true);
		$this->register_template_function('NEWUSER', 'user_new', true);
		$this->register_template_function('RANDOMUSER', 'user_random', true);
		$this->register_template_function('PROFILE', 'user_profile', true);
		$this->register_template_function('BOOKMARK', 'user_bookmarklink', false);
		$this->register_template_function('SHOWBOOKMARKS', 'user_bookmarks', true);
		$this->register_template_function('ONLINERECORD', 'user_onlinerecord', true);
		$this->register_template_function('USERBLOGS', 'user_blogs_last', true);
		$this->register_template_function('USERGALLERY_LAST', 'user_gallery_last', true);
		$this->register_template_function('USERGALLERY_UPDATED', 'user_gallery_updated', true);
		$this->register_template_function('USERGALLERY_LASTPICS', 'user_gallery_lastpics', true);
		$this->register_template_function('USERGALLERY_POTM', 'user_gallery_potm', true);		
		//$this->register_template_function('USERSTATUS', 'user_status', true);

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
		// Klasse sofort initialisieren f�r Sprachpaket und Userinfos
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

	/*
	public function execute()
	{
		$this->apx->headline($this->apx->lang->get('HEADLINE'),mklink('user.php','user.html'));
		$this->apx->titlebar($this->apx->lang->get('HEADLINE'));
		
		//Alte PNs der User löschen
		$this->apx->db()->query("DELETE FROM ".PRE."_user_pms WHERE ( del_to='1' AND del_from='1' )");

		//Funktionen laden
		include(BASEDIR.getmodulepath('user').'func/func.citymatch.php');
		include(BASEDIR.getmodulepath('user').'functions.php');

		if( file_exists(BASEDIR.getmodulepath('comments').'functions.php') )
			include(BASEDIR.getmodulepath('comments').'functions.php');

		////////////////////////////////////////////////////////////////////////////////////////// LOGOUT

		$publicFunc = array(
			'logout',
			'profile',
			'newmail',
			'guestbook',
			'blog',
			'gallery',
			'collection',
			'report',
			'list',
			'search',
			'online',
			'usermap'
		);

		$userFunc = array(
			'myprofile',
			'setstatus',
			'signature',
			'avatar',
			'pms',
			'newpm',
			'readpm',
			'delpm',
			'ignorelist',
			'friends',
			'addbuddy',
			'delbuddy',
			'addbookmark',
			'delbookmark',
			'myblog',
			'mygallery',
			'subscriptions',
			'subscribe'
		);

		$guestFunc = array(
			'register',
			'activate',
			'getregkey',
			'getpwd'
		);

		////////////////////////////////////////////////////////////////////////////////////////// 

		// Extract action from request parameters
		$action = NULL;
		if( $this->apx->param()->requestIf('action') )
			$action = $this->apx->param()->requestString('action');
			
		$user = $this->apx->get_registered_object('user');

		////////////////////////////////////////////////////////////////////////////////////////// ÖFFENTLICHE FUNKTIONEN

		// set old style variables!
		$apx = $this->apx;
		$set = $this->apx->get_config_array();
		$db = $this->apx->db();

		if ( in_array($action, $publicFunc) ) 
		{
			require(BASEDIR.getmodulepath('user').'pub/'.$this->apx->param()->requestString('action').'.php');
		}	

		////////////////////////////////////////////////////////////////////////////////////////// USER-FUNKTIONEN

		elseif ( isset($user->info['userid']) && $user->info['userid'] ) 
		{
			if ( in_array($action, $userFunc) ) 
			{
				require(BASEDIR.getmodulepath('user').'pub/'.$action.'.php');
			}
			else 
			{
				require(BASEDIR.getmodulepath('user').'pub/index.php');
			}
		}

		////////////////////////////////////////////////////////////////////////////////////////// GAST-FUNKTIONEN

		elseif ( !isset($user->info['userid']) || !$user->info['userid'] )
		{	
			if ($this->apx->param()->requestIf('action') && in_array($this->apx->param()->requestString('action'), $guestFunc) )
			{
				require(BASEDIR.getmodulepath('user').'pub/'. $this->apx->param()->requestString('action').'.php');
			}
			else {
				require(BASEDIR.getmodulepath('user').'pub/login.php');
			}
		}

		////////////////////////////////////////////////////////////////////////////////////////// 404

		else
		{
			filenotfound();
		}
	}
	*/

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
}