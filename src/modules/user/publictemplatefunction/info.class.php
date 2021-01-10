<?php

namespace Modules\User\PublicTemplateFunction;

class Info extends \PublicTemplateFunction
{
    public function execute($userid, $template="information") : void
    {        
        $apx = $this->publicModule()->module()->apx();
        $user = $apx->get_registered_object('user');
        $tmpl = new \tengine($apx);

        /**
         * @var \Modules\User\Module
         */
        $m = $this->publicModule()->module();

        $apx->lang->drop('profile', 'user');

        //Verwendete Variablen auslesen
        $parse = $tmpl->used_vars('functions/' . $template, 'user');

        $res = $apx->db()->first("SELECT * FROM " . PRE . "_user WHERE userid='" . $userid . "' LIMIT 1");
        $userid = $res['userid'];
        if (
            !$res['userid']
        ) return;
        list($groupname) = $apx->db()->first("SELECT name FROM " . PRE . "_user_groups WHERE groupid='" . $res['groupid'] . "' LIMIT 1");

        $age = 0;
        if (
            $res['birthday']
        )
        {
            $bd = explode('-', $res['birthday']);
            $birthday = intval($bd[0]) . '. ' . getcalmonth($bd[1]) . iif($bd[2], ' ' . $bd[2]);
            if ($bd[2])
            {
                $age = date('Y') - $bd[2];
                if (intval(sprintf('%02d%02d', $bd[1], $bd[0])) > intval(date('md')))
                {
                    $age -= 1;
                }
            }
        }

        $tmpl->assign('USERID', $res['userid']);
        $tmpl->assign('USERNAME', replace($res['username']));
        $tmpl->assign('GROUP', replace($groupname));
        $tmpl->assign('REGDATE', $res['reg_time']);
        $tmpl->assign('REGDAYS', floor((time() - $res['reg_time']) / (24 * 3600)));
        $tmpl->assign('LASTACTIVE', (int)$res['lastactive']);
        $tmpl->assign('IS_ONLINE', iif(!$res['pub_invisible'] && ($res['lastactive'] + $apx->config('user')['timeout'] * 60) >= time(), 1, 0));
        $tmpl->assign('EMAIL', replace($res['email']));
        $tmpl->assign('EMAIL_ENCRYPTED', replace(cryptMail($res['email'])));
        $tmpl->assign('HIDEMAIL', $res['pub_hidemail']);
        $tmpl->assign('HOMEPAGE', replace($res['homepage']));
        $tmpl->assign('ICQ', replace($res['icq']));
        $tmpl->assign('AIM', replace($res['aim']));
        $tmpl->assign('YIM', replace($res['yim']));
        $tmpl->assign('MSN', replace($res['msn']));
        $tmpl->assign('SKYPE', replace($res['skype']));
        $tmpl->assign('REALNAME', replace($res['realname']));
        $tmpl->assign('CITY', replace($res['city']));
        $tmpl->assign('PLZ', replace($res['plz']));
        $tmpl->assign('COUNTRY', replace($res['country']));
        $tmpl->assign('INTERESTS', replace($res['interests']));
        $tmpl->assign('WORK', replace($res['work']));
        $tmpl->assign('GENDER', (int)$res['gender']);
        $tmpl->assign('BIRTHDAY', $birthday);
        $tmpl->assign('AGE', $age);
        $tmpl->assign('SIGNATURE', $user->mksig($res, 1));
        $tmpl->assign('AVATAR', $user->mkavatar($res));
        $tmpl->assign('AVATAR_TITLE', $user->mkavtitle($res));

        //Custom-Felder
        for ($i = 1; $i <= 10; $i++)
        {
            $tmpl->assign('CUSTOM' . $i . '_NAME', replace($apx->config('user')['cusfield_names'][($i - 1)]));
            $tmpl->assign('CUSTOM' . $i, replace($res['custom' . $i]));
        }

        //Forum-Variablen
        if (
            $apx->is_module('forum')
        )
        {
            if ($res['forum_lastactive'] == 0) $res['forum_lastactive'] = $res['lastactive'];
            $tmpl->assign('FORUM_LASTACTIVE', (int)$res['forum_lastactive']);
            $tmpl->assign('FORUM_POSTS', (int)$res['forum_posts']);
            $tmpl->assign('FORUM_FINDPOSTS', HTTPDIR . $apx->config('forum')['directory'] . '/search.php?send=1&author=' . urlencode($res['username']));
        }

        //Kommentare
        if (
            $apx->is_module('comments') && in_array('COMMENTS', $parse)
        )
        {
            require_once(BASEDIR . getmodulepath('comments') . 'functions.php');
            $tmpl->assign('COMMENTS', comments_count($res['userid']));
        }

        //Interaktionen
        $link_buddy = iif($user->info['userid'] && !$user->is_buddy($res['userid']), mklink(
            'user.php?action=addbuddy&amp;id=' . $res['userid'],
            'user,addbuddy,' . $res['userid'] . '.html'
        ));
        $link_sendpm = iif($user->info['userid'], mklink(
            'user.php?action=newpm&amp;touser=' . $res['userid'],
            'user,newpm,' . $res['userid'] . '.html'
        ));
        $link_sendmail = iif($user->info['userid'], mklink(
            'user.php?action=newmail&amp;touser=' . $res['userid'],
            'user,newmail,' . $res['userid'] . '.html'
        ));
        $tmpl->assign('LINK_BUDDY', $link_buddy);
        $tmpl->assign('LINK_SENDPM', $link_sendpm);
        $tmpl->assign('LINK_SENDEMAIL', $link_sendmail);


        $m->assign_profile_links($tmpl, $res);

        //Buddyliste
        $tabledata = array();
        if (
            $res['pub_showbuddies'] && in_array('BUDDY', $parse)
        )
        {
            $data = $db->fetch("SELECT friendid FROM " . PRE . "_user_friends WHERE userid='" . $res['userid'] . "'");
            $buddies = get_ids($data, 'friendid');
            if (count($buddies))
            {
                $data = $db->fetch("SELECT userid,username,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM " . PRE . "_user WHERE userid IN (" . implode(',', $buddies) . ") ORDER BY username ASC");
                if (count($data))
                {
                    foreach ($data as $res)
                    {
                        ++$i;

                        $age = 0;
                        if ($res['birthday'])
                        {
                            $bd = explode('-', $res['birthday']);
                            $birthday = intval($bd[0]) . '. ' . getcalmonth($bd[1]) . iif($bd[2], ' ' . $bd[2]);
                            if ($bd[2])
                            {
                                $age = date('Y') - $bd[2];
                                if (intval(sprintf('%02d%02d', $bd[1], $bd[0])) > intval(date('md')))
                                {
                                    $age -= 1;
                                }
                            }
                        }

                        $tabledata[$i]['ID'] = $res['userid'];
                        $tabledata[$i]['USERID'] = $res['userid'];
                        $tabledata[$i]['NAME'] = replace($res['username']);
                        $tabledata[$i]['USERNAME'] = replace($res['username']);
                        $tabledata[$i]['GROUPID'] = $res['groupid'];
                        $tabledata[$i]['EMAIL'] = replace(iif(!$res['pub_hidemail'], $res['email']));
                        $tabledata[$i]['EMAIL_ENCRYPTED'] = replace(iif(!$res['pub_hidemail'], cryptMail($res['email'])));
                        $tabledata[$i]['ONLINE'] = iif(!$res['pub_invisible'] && ($res['lastactive'] + $set['user']['timeout'] * 60) >= time(), 1, 0);
                        $tabledata[$i]['ISONLINE'] = $tabledata[$i]['ONLINE'];
                        $tabledata[$i]['REALNAME'] = replace($res['realname']);
                        $tabledata[$i]['GENDER'] = $res['gender'];
                        $tabledata[$i]['CITY'] = replace($res['city']);
                        $tabledata[$i]['PLZ'] = replace($res['plz']);
                        $tabledata[$i]['COUNTRY'] = $res['country'];
                        $tabledata[$i]['REGTIME'] = $res['reg_time'];
                        $tabledata[$i]['REGDAYS'] = floor((time() - $res['reg_time']) / (24 * 3600));
                        $tabledata[$i]['LASTACTIVE'] = $res['lastactive'];
                        $tabledata[$i]['AVATAR'] = $user->mkavatar($res);
                        $tabledata[$i]['AVATAR_TITLE'] = $user->mkavtitle($res);
                        $tabledata[$i]['BIRTHDAY'] = $birthday;
                        $tabledata[$i]['AGE'] = $age;

                        //Custom-Felder
                        for ($ii = 1; $ii <= 10; $ii++)
                        {
                            $tabledata[$i]['CUSTOM' . $ii . '_NAME'] = $set['user']['cusfield_names'][($ii - 1)];
                            $tabledata[$i]['CUSTOM' . $ii] = compatible_hsc($res['custom' . $ii]);
                        }

                        //Interaktions-Links
                        if ($user->info['userid'])
                        {
                            $tabledata[$i]['LINK_SENDPM'] = mklink(
                                'user.php?action=newpm&amp;touser=' . $res['userid'],
                                'user,newpm,' . $res['userid'] . '.html'
                            );

                            $tabledata[$i]['LINK_SENDEMAIL'] = mklink(
                                'user.php?action=newmail&amp;touser=' . $res['userid'],
                                'user,newmail,' . $res['userid'] . '.html'
                            );
                        }

                        //Nur Buddy-Liste
                        if ($buddylist)
                        {
                            $tabledata[$i]['LINK_DELBUDDY'] = mklink(
                                'user.php?action=delbuddy&amp;id=' . $res['userid'],
                                'user,delbuddy,' . $res['userid'] . '.html'
                            );
                        }
                    }
                }
            }
        }
        $tmpl->assign('BUDDY', $tabledata);

        //Template ausgeben
        $tmpl->parse('functions/' . $template, 'user');
    }
}

?>