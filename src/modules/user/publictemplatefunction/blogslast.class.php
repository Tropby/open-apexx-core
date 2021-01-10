<?php

namespace Modules\User\PublicTemplateFunction;

class BlogsLast extends \PublicTemplateFunction
{
    public function execute($count = 5, $start = 0, $friendsonly = false, $userid = 0, $template = 'lastblogs')
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');

        $tmpl = new \tengine($apx);
        $count = (int)$count;
        $start = (int)$start;
        $userid = (int)$userid;

        //Verwendete Variablen auslesen
        $parse = $apx->tmpl->used_vars('functions/' . $template, 'user');

        //Nach Freunde filtern
        $friendfilter = '';
        if (
            $friendsonly
        )
        {
            $friends = $user->get_buddies();
            $friends[] = -1;
            $friendfilter = " AND userid IN (" . implode(',', $friends) . ") ";
        }

        //Nach Benutzer filtern
        $userfilter = '';
        if (
            $userid
        )
        {
            $userfilter = " AND userid='" . $userid . "'";
        }

        $data = $db->fetch("SELECT * FROM " . PRE . "_user_blog WHERE 1 " . $userfilter . $friendfilter . " ORDER BY time DESC LIMIT " . iif($start, $start . ',') . $count);
        if (
            count($data)
        )
        {

            //Benutzer-Infos auslesen
            $userdata = array();
            if (in_template(array('BLOG.USERNAME', 'BLOG.REALNAME', 'BLOG.AVATAR', 'BLOG.AVATER_TITLE'), $parse))
            {
                $userids = get_ids($data, 'userid');
                $userdata = $user->get_info_multi($userids, 'username,realname,avatar,avatar_title');
            }

            //Blogs auflisten
            $tabledata = array();
            $i = 0;
            foreach ($data as $res)
            {
                ++$i;

                $link = mklink(
                    'user.php?action=blog&amp;id=' . $res['userid'] . '&amp;blogid=' . $res['id'],
                    'user,blog,' . $res['userid'] . ',id' . $res['id'] . urlformat($res['title']) . '.html'
                );

                //Text
                $text = '';
                if (in_array('BLOG.TEXT', $parse))
                {
                    $text = $res['text'];
                    $text = badwords($text);
                    $text = replace($text, 1);
                    $text = dbsmilies($text);
                    $text = dbcodes($text);
                }

                $tabledata[$i]['ID'] = $res['id'];
                $tabledata[$i]['TITLE'] = replace($res['title']);
                $tabledata[$i]['TEXT'] = $res['text'];
                $tabledata[$i]['LINK'] = $link;
                $tabledata[$i]['TIME'] = $res['time'];

                //Userinfo
                $userinfo = $userdata[$res['userid']];
                $tabledata[$i]['USERID'] = $res['userid'];
                $tabledata[$i]['USERNAME'] = replace($userinfo['username']);
                $tabledata[$i]['REALNAME'] = replace($userinfo['realname']);
                $tabledata[$i]['AVATAR'] = $user->mkavatar($userinfo);
                $tabledata[$i]['AVATAR_TITLE'] = $user->mkavtitle($userinfo);

                //Kommentare
                if ($apx->is_module('comments') && $res['allowcoms'])
                {
                    require_once(BASEDIR . getmodulepath('comments') . 'class.comments.php');
                    if (!isset($coms)) $coms = new \comments('userblog', $res['id']);
                    else $coms->mid = $res['id'];

                    $link = mklink(
                        'user.php?action=blog&amp;id=' . $res['userid'] . '&amp;blogid=' . $res['id'],
                        'user,blog,' . $res['userid'] . ',id' . $res['id'] . urlformat($res['title']) . '.html'
                    );

                    $tabledata[$i]['COMMENT_COUNT'] = $coms->count();
                    $tabledata[$i]['COMMENT_LINK'] = $coms->link($link);
                    $tabledata[$i]['DISPLAY_COMMENTS'] = 1;
                    if (in_template(array('BLOG.COMMENT_LAST_USERID', 'BLOG.COMMENT_LAST_NAME', 'BLOG.COMMENT_LAST_TIME'), $parse))
                    {
                        $tabledata[$i]['COMMENT_LAST_USERID'] = $coms->last_userid();
                        $tabledata[$i]['COMMENT_LAST_NAME'] = $coms->last_name();
                        $tabledata[$i]['COMMENT_LAST_TIME'] = $coms->last_time();
                    }
                }
            }
        }
        $tmpl->assign('BLOG', $tabledata);
        //Template ausgeben
        $tmpl->parse('functions/' . $template, 'user');
    }
}
