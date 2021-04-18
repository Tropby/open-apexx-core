<?php

namespace Modules\User\AdminAction;


/**
 * 
 */
class myprofile extends \AdminAction
{
    public function execute()
    {
        global $set, $apx, $db;

        //Weiterleisten auf Loginseite, wenn nicht angemeldet
        if (!$apx->user->info['userid'])
        {
            header('Location: index.php');
        }

        if ($_POST['send'] == 1)
        {
            if (!checkToken()) infoInvalidToken();
            elseif (!$_POST['username'] || (($_POST['pwd1'] || $_POST['pwd2']) && (!$_POST['pwd1'] || !$_POST['pwd2'])) || !$_POST['email']) infoNotComplete();
            elseif ($_POST['pwd1'] != $_POST['pwd2']) info($apx->lang->get('INFO_PWNOMATCH'));
            elseif (strlen($_POST['signature']) > $set['user']['sigmaxlen']) info($apx->lang->get('INFO_SIGTOOLONG'));
            elseif (!checkmail($_POST['email'])) info($apx->lang->get('INFO_NOMAIL'));
            else
            {
                if (substr($_POST['homepage'], 0, 4) == 'www.') $_POST['homepage'] = 'http://' . $_POST['homepage'];

                if ($_POST['pwd1'])
                {
                    $_POST['salt'] = random_string();
                    $_POST['password'] = md5(md5($_POST['pwd1']) . $_POST['salt']);
                }

                if ($_POST['bd_day'] && $_POST['bd_mon'] && $_POST['bd_year']) $_POST['birthday'] = sprintf('%02d-%02d-%04d', $_POST['bd_day'], $_POST['bd_mon'], $_POST['bd_year']);
                elseif ($_POST['bd_day'] && $_POST['bd_day']) $_POST['birthday'] = sprintf('%02d-%02d', $_POST['bd_day'], $_POST['bd_mon']);
                else $_POST['birthday'] = '';

                //Location bestimmen
                $_POST['locid'] = user_get_location($_POST['plz'], $_POST['city'], $_POST['country']);

                $db->dupdate(PRE . '_user', iif($_POST['pwd1'], 'password,salt,') . 'username,email,homepage,icq,aim,yim,msn,skype,realname,gender,birthday,city,plz,country,locid,interests,work,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10,signature,pub_lang,pub_invisible,pub_hidemail,pub_poppm,pub_usegb,pub_gbmail,pub_profileforfriends,pub_showbuddies,pub_theme,admin_lang,admin_editor' . iif($apx->is_module('forum'), ',forum_autosubscribe'), "WHERE userid='" . $apx->user->info['userid'] . "'");
                logit('USER_MYPROFILE');

                if ($_POST['pwd1'])
                {
                    $apx->session->destroy();
                    setcookie($set['main']['cookie_pre'] . '_admin_userid', 0, time() - 99999, '/');
                    setcookie($set['main']['cookie_pre'] . '_admin_password', 0, time() - 99999, '/');
                    unset(
                        $_COOKIE[$set['main']['cookie_pre'] . '_admin_userid'],
                        $_COOKIE[$set['main']['cookie_pre'] . '_admin_password']
                    );
                }
                printJSRedirect('action.php?action=user.myprofile');
            }
        }

        //Erster Durchlauf
        else
        {
            $ex = array('userid', 'password', 'birthday', 'reg_time', 'reg_email', 'lastonline', 'lastactive');
            foreach ($apx->user->info as $key => $val)
            {
                if (in_array($key, $ex)) continue;
                $_POST[$key] = $val;
            }

            list($_POST['bd_day'], $_POST['bd_mon'], $_POST['bd_year']) = explode('-', $apx->user->info['birthday']);

            //Sprache
            $lang_admin = "";
            $lang_pub = "";
            foreach ($apx->languages as $id => $name)
            {
                $lang_admin .= '<option value="' . $id . '"' . iif($_POST[' admin_lang'] == $id, ' selected="selected"') . '>' . $name . '</option>';
                $lang_pub .= '<option value="' . $id . '"' . iif($_POST[' pub_lang'] == $id, ' selected="selected"') . '>' . $name . '</option>';
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
            $themelist = "";
            foreach ($themes as $themeid)
            {
                $themelist .= '<option value="' . $themeid . '"' . iif($themeid == $_POST[' pub_theme'], ' selected="selected"') . '>' . $themeid . '</option>';
            }

            //Custom-Felder
            for ($i = 1; $i <= 10; $i++)
            {
                $fieldname = $set['user']['cusfield_names'][$i - 1];
                $apx->tmpl->assign('CUSFIELD' . $i . '_NAME', replace($fieldname));
                $apx->tmpl->assign('CUSTOM' . $i, compatible_hsc($_POST['custom' . $i]));
            }

            $apx->tmpl->assign('USERNAME_LOGIN', replace($_POST['username_login']));
            $apx->tmpl->assign('USERNAME', compatible_hsc($_POST['username']));
            $apx->tmpl->assign('EMAIL', compatible_hsc($_POST['email']));
            $apx->tmpl->assign('HOMEPAGE', compatible_hsc($_POST['homepage']));
            $apx->tmpl->assign('ICQ', (int)$_POST['icq']);
            $apx->tmpl->assign('AIM', compatible_hsc($_POST['aim']));
            $apx->tmpl->assign('YIM', compatible_hsc($_POST['yim']));
            $apx->tmpl->assign('MSN', compatible_hsc($_POST['msn']));
            $apx->tmpl->assign('SKYPE', compatible_hsc($_POST['skype']));
            $apx->tmpl->assign('REALNAME', compatible_hsc($_POST['realname']));
            $apx->tmpl->assign('CITY', compatible_hsc($_POST['city']));
            $apx->tmpl->assign('COUNTRY', compatible_hsc($_POST['country']));
            $apx->tmpl->assign('PLZ', compatible_hsc($_POST['plz']));
            $apx->tmpl->assign('INTERESTS', compatible_hsc($_POST['interests']));
            $apx->tmpl->assign('WORK', compatible_hsc($_POST['work']));
            $apx->tmpl->assign('GENDER', (int)$_POST['gender']);
            $apx->tmpl->assign('BD_DAY', (int)$_POST['bd_day']);
            $apx->tmpl->assign('BD_MON', (int)$_POST['bd_mon']);
            $apx->tmpl->assign('BD_YEAR', (int)$_POST['bd_year']);
            $apx->tmpl->assign('SIGNATURE', compatible_hsc($_POST['signature']));
            $apx->tmpl->assign('MAXLEN', $set['user']['sigmaxlen']);
            $apx->tmpl->assign('PUB_INVISIBLE', (int)$_POST['pub_invisible']);
            $apx->tmpl->assign('PUB_HIDEMAIL', (int)$_POST['pub_hidemail']);
            $apx->tmpl->assign('PUB_POPPM', (int)$_POST['pub_poppm']);
            $apx->tmpl->assign('PUB_SHOWBUDDIES', (int)$_POST['pub_showbuddies']);
            $apx->tmpl->assign('PUB_USEGB', (int)$_POST['pub_usegb']);
            $apx->tmpl->assign('PUB_GBMAIL', (int)$_POST['pub_gbmail']);
            $apx->tmpl->assign('PUB_THEME', $themelist);
            $apx->tmpl->assign('PUB_LANG', $lang_pub);
            $apx->tmpl->assign('PUB_PROFILEFORFRIENDS', (int)$_POST['pub_profileforfriends']);
            $apx->tmpl->assign('FORUM_AUTOSUBSCRIBE', (int)$_POST['forum_autosubscribe']);
            $apx->tmpl->assign('ADMIN_LANG', $lang_admin);
            $apx->tmpl->assign('ADMIN_EDITOR', (int)$_POST['admin_editor']);

            $apx->tmpl->parse('myprofile');
        }
    }
}
