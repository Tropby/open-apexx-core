<?php

namespace Modules\User\PublicAction;

class Register extends \PublicAction
{
	public function execute()
	{
		$apx = $this->publicModule()->module()->apx();
		$db = $apx->db();
		$user = $apx->get_registered_object('user');

		if (isset($user->info['userid']) && $user->info['userid'])
		{
			(new Index($this->publicModule()))->execute();
			return;
		}	

		$apx->lang->drop('register');
		headline($apx->lang->get('HEADLINE_REGISTER'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
		titlebar($apx->lang->get('HEADLINE_REGISTER'));

		if (isset($_POST['send']))
		{
			$_POST['email1'] = trim($_POST['email1']);
			$_POST['email2'] = trim($_POST['email2']);
			$check = $check2 = false;
			list($check) = $db->first("SELECT username_login FROM " . PRE . "_user WHERE LOWER(username_login)='" . addslashes(strtolower($_POST['username'])) . "' LIMIT 1");
			if (!$apx->config('user')['mailmultiacc']) list($check2) = $db->first("SELECT email FROM " . PRE . "_user WHERE LOWER(email)='" . addslashes(strtolower($_POST['email1'])) . "' LIMIT 1");
			$blockname = $user->block_username($_POST['username']);

			//Captcha prüfen
			if ($apx->config('user')['captcha'])
			{
				require(BASEDIR . 'lib/class.captcha.php');
				$captcha = new captcha;
				$captchafailed = $captcha->check();
			}

			if ($captchafailed) message($apx->lang->get('MSG_WRONGCODE'), 'javascript:history.back()');
			elseif (!$_POST['username'] || !$_POST['pwd1'] || !$_POST['pwd2'] || !$_POST['email1'] || !$_POST['email2']) message('back');
			elseif ($_POST['pwd1'] != $_POST['pwd2']) message($apx->lang->get('MSG_PWNOMATCH'), 'javascript:history.back()');
			elseif ($apx->config('user')['userminlen'] && strlen($_POST['username']) < $apx->config('user')['userminlen']) message($apx->lang->get('MSG_USERLENGTH', array('LENGTH' => $apx->config('user')['userminlen'])), 'javascript:history.back()');
			elseif ($apx->config('user')['pwdminlen'] && strlen($_POST['pwd1']) < $apx->config('user')['pwdminlen']) message($apx->lang->get('MSG_PWDLENGTH', array('LENGTH' => $apx->config('user')['pwdminlen'])), 'javascript:history.back()');
			elseif ($_POST['email1'] != $_POST['email2']) message($apx->lang->get('MSG_EMAILNOMATCH'), 'javascript:history.back()');
			elseif (!checkmail($_POST['email1'])) message($apx->lang->get('MSG_NOMAIL'), 'javascript:history.back()');
			elseif ($blockname) message($apx->lang->get('MSG_USERNOTALLOWED', array('STRING' => $blockname)), 'javascript:history.back()');
			elseif ($check) message($apx->lang->get('MSG_USEREXISTS'), 'javascript:history.back()');
			elseif (!$apx->config('user')['mailmultiacc'] && $check2) message($apx->lang->get('MSG_MAILEXISTS'), 'javascript:history.back()');
			else
			{

				//Captcha löschen
				if ($apx->config('user')['captcha'])
				{
					$captcha->remove();
				}

				if (substr($_POST['homepage'], 0, 4) == 'www.') $_POST['homepage'] = 'http://' . $_POST['homepage'];

				if ($_POST['bd_day'] && $_POST['bd_month'] && $_POST['bd_year']) $_POST['birthday'] = sprintf('%02d-%02d-%04d', $_POST['bd_day'], $_POST['bd_month'], $_POST['bd_year']);
				elseif ($_POST['bd_day'] && $_POST['bd_day']) $_POST['birthday'] = sprintf('%02d-%02d', $_POST['bd_day'], $_POST['bd_month']);
				else $_POST['birthday'] = '';

				//Location bestimmen
				$_POST['locid'] = user_get_location($_POST['plz'], $_POST['city'], $_POST['country']);

				if ($apx->config('user')['useractivation'] == 2) $_POST['reg_key'] = 'BYADMIN';
				elseif ($apx->config('user')['useractivation'] == 3) $_POST['reg_key'] = random_string();

				$_POST['salt'] = random_string();
				$_POST['password'] = md5(md5($_POST['pwd1']) . $_POST['salt']);
				$_POST['groupid'] = $apx->config('user')['defaultgroup'];
				$_POST['email'] = $_POST['reg_email'] = $_POST['email1'];
				$_POST['reg_time'] = time();
				$_POST['lastonline'] = time();
				$_POST['lastactive'] = time();
				$_POST['username_login'] = $_POST['username'];
				$_POST['admin_editor'] = 1;

				$db->dinsert(PRE . '_user', 'username_login,username,password,salt,reg_email,reg_time' . iif($apx->config('user')['useractivation'] != 1, ',reg_key') . ',lastonline,lastactive,email,groupid,homepage,icq,aim,yim,msn,skype,realname,gender,birthday,city,plz,country,interests,locid,work,signature,pub_invisible,pub_hidemail,pub_poppm,pub_mailpm,pub_showbuddies,pub_usegb,pub_gbmail,pub_lang,pub_theme,admin_editor,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10');

				//eMail-Benachrichtigung
				if ($apx->config('user')['mailonnew'])
				{
					$input = array(
						'URL' => HTTP,
						'USERNAME' => $_POST['username']
					);
					sendmail($apx->config('user')['mailonnew'], 'NEWREG', $input);
					unset($input);
				}

				//Message + eMail verschicken
				$input['USERNAME'] = $_POST['username'];
				$input['PASSWORD'] = $_POST['pwd1'];
				$input['WEBSITE'] = $set['main']['websitename'];

				//Bestätigungs-eMail verschicken und ENDE
				if ($apx->config('user')['useractivation'] == 2)
				{
					sendmail($_POST['email'], 'REGADMINACTIVATION', $input);
					message($apx->lang->get('MSG_OK_ADMINACTIVATE'), mklink('user.php', 'user.html'));
				}
				elseif ($apx->config('user')['useractivation'] == 3)
				{
					$input['URL'] = HTTP_HOST . mklink(
						'user.php?action=activate&userid=' . $db->insert_id() . '&key=' . $_POST['reg_key'],
						'user,activate.html?userid=' . $db->insert_id() . '&key=' . $_POST['reg_key']
					);

					sendmail($_POST['email'], 'REGACTIVATION', $input);
					message($apx->lang->get('MSG_OK_ACTIVATE'), mklink('user.php', 'user.html'));
				}
				else
				{
					sendmail($_POST['email'], 'REG', $input);
					message($apx->lang->get('MSG_OK'), mklink('user.php', 'user.html'));
				}
			}
		}

		//Formular anzeigen
		elseif (!$apx->config('user')['acceptrules'] || ($_POST['accept'] ?? 0))
		{

			//Sprachen
			$langlist = '<option value="">' . $apx->lang->get('USEDEFAULT') . '</option>';
			$i = 0;
			foreach ($apx->languages as $id => $name)
			{
				$langlist .= '<option value="' . $id . '"' . iif(($user->info['pub_lang'] ?? 0) == $id, ' selected="selected"') . '>' . replace($name) . '</option>';
				++$i;
				$langdata[$i] = array(
					'ID' => $id,
					'TITLE' => $name
				);
			}

			//Themes
			$handle = opendir(BASEDIR . getpath('tmpldir'));
			while ($file = readdir($handle))
			{
				if ($file == '.' || $file == '..' || !is_dir(BASEDIR . getpath('tmpldir') . $file)) continue;
				$themes[] = $file;
			}
			closedir($handle);
			sort($themes);

			$themelist = '<option value="">' . $apx->lang->get('USEDEFAULT') . '</option>';
			foreach ($themes as $themeid)
			{
				$themelist .= '<option value="' . $themeid . '"' . iif($themeid == ($user->info['pub_theme'] ?? NULL), ' selected="selected"') . '>' . $themeid . '</option>';
				++$i;
				$themedata[$i] = array(
					'ID' => $themeid,
					'TITLE' => $themeid
				);
			}

			//Custom-Felder
			for ($i = 1; $i <= 10; $i++)
			{
				$apx->tmpl->assign('CUSTOM' . $i . '_NAME', $apx->config('user')['cusfield_names'][($i - 1)] ?? NULL);
			}

			$postto = mklink(
				'user.php?action=register',
				'user,register.html'
			);

			//Captcha erstellen
			if ($apx->config('user')['captcha'])
			{
				require(BASEDIR . 'lib/class.captcha.php');
				$captcha = new captcha;
				$captchacode = $captcha->generate();
			}

			//Alte Variablen für Abwärtskompatiblität
			$apx->tmpl->assign('LANGLIST', $langlist);
			$apx->tmpl->assign('THEMELIST', $themelist);

			$apx->tmpl->assign('LANG', $langdata);
			$apx->tmpl->assign('THEME', $themedata);
			$apx->tmpl->assign('CAPCHA', $captchacode); //Abwärtskompatiblität
			$apx->tmpl->assign('CAPTCHA', $captchacode);
			$apx->tmpl->assign('POSTTO', $postto);
			$apx->tmpl->assign('USERLENGTH', $apx->config('user')['userminlen']);
			$apx->tmpl->assign('PWDLENGTH', $apx->config('user')['pwdminlen']);
			$apx->tmpl->parse('register');
		}

		//Regeln akzeptieren
		else
		{
			$postto = mklink(
				'user.php?action=register',
				'user,register.html'
			);

			$apx->tmpl->assign('POSTTO', $postto);
			$apx->tmpl->parse('register_rules');
		}
	}
}
