<?php

namespace Modules\User\AdminAction;

/**
 * 
 */
class Edit extends \AdminAction
{
    public function execute()
    {
        global $set, $apx, $db;

        //Mehrere
        if (
            is_array($_REQUEST['multiid'])
        )
        {
            $ids = array_map('intval', $_REQUEST['multiid']);
            $ids = array_diff($ids, array($apx->user->info['userid']));
            if (!count($ids))
            {
                printJSRedirect(get_index('user.show'));
                return;
            }

            if ($_POST['send'] == 1 && intval($_POST['groupid']))
            {
                if (!checkToken()) printInvalidToken();
                else
                {
                    if (count($ids))
                    {
                        $db->query("UPDATE " . PRE . "_user SET groupid='" . intval($_POST['groupid']) . "' WHERE userid IN (" . implode(',', $ids) . ")");
                        foreach ($ids as $id) logit('USER_EDIT', 'ID #' . $id);
                    }

                    printJSRedirect(get_index('user.show'));
                }
                return;
            }

            $data = $db->fetch("SELECT userid,username FROM " . PRE . "_user WHERE userid IN (" . implode(',', $ids) . ") ORDER BY username ASC");
            if (count($data))
            {
                foreach ($data as $res)
                {
                    $userlist[] = replace($res['username']) . '<input type="hidden" name="multiid[]" value="' . $res['userid'] . '" />';
                }
            }

            $grouplist = "";
            $data = $db->fetch("SELECT groupid,name FROM " . PRE . "_user_groups ORDER BY name ASC");
            if (count($data))
            {
                foreach ($data as $res) $grouplist .= '<option value="' . $res['groupid'] . '"' . iif($_POST['groupid'] == $res['groupid'], ' selected="selected"') . '>' . replace($res['name']) . '</option>';
            }

            $apx->tmpl->assign('USERS', implode(', ', $userlist));
            $apx->tmpl->assign('GROUP', $grouplist);

            tmessageOverlay('multi_edit');
        }


        //Einzeln
        else
        {
            $_REQUEST['id'] = (int)$_REQUEST['id'];
            if (!$_REQUEST['id']) die('missing ID!');

            if ($_POST['send'] == 1)
            {
                list($usercheck) = $db->first("SELECT userid FROM " . PRE . "_user WHERE ( LOWER(username_login)='" . addslashes(strtolower($_POST['username'])) . "' AND userid!='" . $_REQUEST['id'] . "' ) LIMIT 1");

                if (!checkToken()) infoInvalidToken();
                elseif (!$_POST['id'] || !$_POST['username_login'] || !$_POST['username'] || (($_POST['pwd1'] || $_POST['pwd2']) && (!$_POST['pwd1'] || !$_POST['pwd2'])) || !$_POST['email']) infoNotComplete();
                elseif ($_POST['pwd1'] != $_POST['pwd2']) info($apx->lang->get('INFO_PWNOMATCH'));
                elseif ($usercheck) info($apx->lang->get('INFO_USEREXISTS'));
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

                    //Avatar löschen
                    $avatarfield = '';
                    if ($_POST['delavatar'])
                    {
                        list($avatar) = $db->first("SELECT avatar FROM " . PRE . "_user WHERE userid='" . $_REQUEST['id'] . "' LIMIT 1");
                        if ($avatar)
                        {
                            require(BASEDIR . 'lib/class.mediamanager.php');
                            $mm = new \mediamanager;
                            $mm->deletefile('user/' . $avatar);
                        }
                        $avatarfield = 'avatar,';
                        $_POST['avatar'] = '';
                        $_POST['avatar_title'] = '';
                    }

                    //Location bestimmen
                    $_POST['locid'] = user_get_location($_POST['plz'], $_POST['city'], $_POST['country']);

                    $db->dupdate(PRE . '_user', 'username_login,username' . iif($_POST['pwd1'], ',password,salt') . ',groupid,active,reg_key,email,homepage,icq,aim,yim,msn,realname,gender,birthday,city,plz,country,locid,interests,work,' . $avatarfield . 'avatar_title,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10,signature,pub_lang,pub_invisible,pub_hidemail,pub_poppm,pub_usegb,pub_gbmail,pub_profileforfriends,pub_showbuddies,pub_theme,admin_lang,admin_editor' . iif($apx->is_module('forum'), ',forum_autosubscribe'), "WHERE userid='" . $_REQUEST['id'] . "'");
                    logit('USER_EDIT', 'ID #' . $_REQUEST['id']);
                    printJSRedirect(get_index('user.show'));
                }
            }

            //Erster Durchlauf
            else
            {
                $ures = $db->first("SELECT * FROM " . PRE . "_user WHERE userid='" . $_REQUEST['id'] . "'", 1);
                $ex = array('userid', 'password', 'birthday', 'reg_time', 'reg_email', 'lastonline', 'lastactive');
                foreach ($ures as $key => $val)
                {
                    if (in_array($key, $ex)) continue;
                    $_POST[$key] = $val;
                }
                list($_POST['bd_day'], $_POST['bd_mon'], $_POST['bd_year']) = explode('-', $ures['birthday']);

                //Sprache
                foreach ($apx->languages as $id => $name)
                {
                    $lang_admin .= '<option value="' . $id . '"' . iif($_POST['admin_lang'] == $id, ' selected="selected"') . '>' . $name . '</option>';
                    $lang_pub .= '<option value="' . $id . '"' . iif($_POST['pub_lang'] == $id, ' selected="selected"') . '>' . $name . '</option>';
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
                foreach ($themes as $themeid)
                {
                    $themelist .= '<option value="' . $themeid . '"' . iif($themeid == $_POST['pub_theme'], ' selected="selected"') . '>' . $themeid . '</option>';
                }

                //Custom-Felder
                for ($i = 1; $i <= 10; $i++)
                {
                    $fieldname = $set['user']['cusfield_names'][$i - 1];
                    $apx->tmpl->assign('CUSFIELD' . $i . '_NAME', replace($fieldname));
                    $apx->tmpl->assign('CUSTOM' . $i, compatible_hsc($_POST['custom' . $i]));
                }

                //Gruppe
                $data = $db->fetch("SELECT groupid,name FROM " . PRE . "_user_groups ORDER BY name ASC");
                if (count($data))
                {
                    foreach ($data as $res)
                    {
                        $grouplist .= '<option value="' . $res['groupid'] . '"' . iif($_POST['groupid'] == $res['groupid'], ' selected="selected"') . '>' . replace($res['name']) . '</option>';
                    }
                }

                //Avatar
                if ($_POST['avatar'])
                {
                    $avatar = HTTPDIR . getpath('uploads') . 'user/' . $_POST['avatar'];
                }

                $apx->tmpl->assign('USERNAME_LOGIN', compatible_hsc($_POST['username_login']));
                $apx->tmpl->assign('USERNAME', compatible_hsc($_POST['username']));
                $apx->tmpl->assign('EMAIL', compatible_hsc($_POST['email']));
                $apx->tmpl->assign('GROUP', $grouplist);
                $apx->tmpl->assign('ACTIVE', (int)$_POST['active']);
                $apx->tmpl->assign('REG_KEY', compatible_hsc($_POST['reg_key']));
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
                $apx->tmpl->assign('AVATAR', $_POST['avatar']);
                $apx->tmpl->assign('AVATAR_PATH', $avatar);
                $apx->tmpl->assign('AVATAR_TITLE', compatible_hsc($_POST['avatar_title']));
                $apx->tmpl->assign('DELAVATAR', (int)$_POST['delavatar']);
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
                $apx->tmpl->assign('ACTION', 'edit');
                $apx->tmpl->assign('ID', $_REQUEST['id']);

                $apx->tmpl->parse('add_edit');
            }
        }
    }
}
