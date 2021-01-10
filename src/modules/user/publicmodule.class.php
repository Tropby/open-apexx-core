<?php

namespace Modules\User;

class PublicModule extends \PublicModule
{
    public function __construct(\Module &$module)
    {
        parent::__construct($module);

        /**
         * Register all actions that can be executed in public scope
         */
        $this->registerAction("index");
        $this->registerAction("login");
        $this->registerAction("logout");
        $this->registerAction("friends");
        $this->registerAction("activate");
        $this->registerAction("addbookmark");
        $this->registerAction("addbuddy");
        $this->registerAction("avatar");
        $this->registerAction("blog");
        $this->registerAction("collection");
        $this->registerAction("delbookmark");
        $this->registerAction("delbuddy");
        $this->registerAction("delpm");
        $this->registerAction("gallery");
        $this->registerAction("getpwd");
        $this->registerAction("getregkey");
        $this->registerAction("guestbook");
        $this->registerAction("ignorelist");
        $this->registerAction("listuser");
        $this->registerAction("myblog");
        $this->registerAction("mygallery");
        $this->registerAction("myprofile");
        $this->registerAction("newmail");
        $this->registerAction("newpm");
        $this->registerAction("online");
        $this->registerAction("pms");
        $this->registerAction("profile");
        $this->registerAction("readpm");
        $this->registerAction("register");
        $this->registerAction("report");
        $this->registerAction("search");
        $this->registerAction("setstatus");
        $this->registerAction("signature");
        $this->registerAction("subscribe");
        $this->registerAction("subscriptions");
        $this->registerAction("usermap");

        /** 
         * Register all tempaltes that can be used in public scope
         */
        $this->registerTemplateFunction('Info', 'USER_INFO');
        $this->registerTemplateFunction('Stats', 'USER_STATS');
        $this->registerTemplateFunction('GalleryLastPics', 'USERGALLERY_LASTPICS');


		/*
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
		$this->register_template_function('NEWUSER', 'user_newuser', true);
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
		*/        
    }

    function search($items, $conn)
    {
        //Suchstring generieren
        foreach ($items as $item)
        {
            $search[] = "username LIKE '%" . addslashes_like($item) . "%'";
        }

        //Ergebnisse
        $data = $this->apx->db()->fetch("SELECT userid,username FROM " . PRE . "_user WHERE ( " . implode($conn, $search) . " ) ORDER BY username ASC");
        if (count($data))
        {
            $i = 0;
            foreach ($data as $res)
            {
                ++$i;
                $result[$i]['TITLE'] = $res['username'];
                $user = $this->module()->apx()->get_registered_object("user");
                $result[$i]['LINK'] = $user->mkprofile($res['userid'], $res['username']);
            }
        }

        return $result;
    }    

    protected function galleryPrintpic($data, $template)
    {
        global $set, $db, $apx, $user;
        $tmpl = new \tengine($apx);

        //Verwendete Variablen auslesen
        $parse = $apx->tmpl->used_vars($template, 'user');

        if (count($data))
        {

            //Benutzer-Infos auslesen
            $userdata = array();
            if (in_template(array('PICTURE.USERNAME', 'PICTURE.REALNAME', 'PICTURE.AVATAR', 'PICTURE.AVATER_TITLE'), $parse))
            {
                $userids = get_ids($data, 'owner');
                $userdata = $user->get_info_multi($userids, 'username,realname,avatar,avatar_title');
            }

            //Bilder auflisten
            $tabledata = array();
            $i = 0;
            foreach ($data as $res)
            {
                ++$i;

                //GALERIE
                $gallink = mklink(
                    'user.php?action=gallery&amp;id=' . $res['owner'] . '&amp;galid=' . $res['galid'],
                    'user,gallery,' . $res['owner'] . ',' . $res['galid'] . ',1.html'
                );

                $tabledata[$i]['GALLERY_ID'] = $res['galid'];
                $tabledata[$i]['GALLERY_TITLE'] = $res['title'];
                $tabledata[$i]['GALLERY_DESCRIPTION'] = $res['description'];
                $tabledata[$i]['GALLERY_TIME'] = $res['galaddtime'];
                $tabledata[$i]['GALLERY_LINK'] = $gallink;
                $tabledata[$i]['GALLERY_UPDATETIME'] = $res['lastupdate'];

                //Enthaltene Bilder
                if (in_array('PICTURE.GALLERY_COUNT', $parse))
                {
                    list($galcount) = $db->first("SELECT count(id) FROM " . PRE . "_user_pictures WHERE galid='" . $res['galid'] . "'");
                    $tabledata[$i]['GALLERY_COUNT'] = $galcount;
                }

                //BILD
                $size = getimagesize(BASEDIR . getpath('uploads') . $res['picture']);
                $tabledata[$i]['LINK'] = "javascript:popuppic('misc.php?action=picture&amp;pic=" . $res['picture'] . "','" . $size[0] . "','" . $size[1] . "');";
                $tabledata[$i]['CAPTION'] = $res['caption'];
                $tabledata[$i]['IMAGE'] = getpath('uploads') . $res['thumbnail'];
                $tabledata[$i]['FULLSIZE'] = getpath('uploads') . $res['picture'];
                $tabledata[$i]['TIME'] = $res['addtime'];

                //Userinfo
                $userinfo = $userdata[$res['owner']];
                $tabledata[$i]['USERID'] = $res['owner'];
                $tabledata[$i]['USERNAME'] = replace($userinfo['username']);
                $tabledata[$i]['REALNAME'] = replace($userinfo['realname']);
                $tabledata[$i]['AVATAR'] = $user->mkavatar($userinfo);
                $tabledata[$i]['AVATAR_TITLE'] = $user->mkavtitle($userinfo);
            }
        }

        $tmpl->assign('PICTURE', $tabledata);
        $tmpl->parse($template, 'user');
    }

    protected function galleryPrintsingle($res, $template)
    {
        $apx = $this->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');      

        if (!$res['id']) return;
        $tmpl = new \tengine($apx);

        //Verwendete Variablen auslesen
        $parse = $apx->tmpl->used_vars($template, 'user');

        //GALERIE
        $gallink = mklink(
            'user.php?action=gallery&amp;id=' . $res['owner'] . '&amp;galid=' . $res['galid'],
            'user,gallery,' . $res['galid'] . ',1.html'
        );

        $tmpl->assign('GALLERY_ID', $res['galid']);
        $tmpl->assign('GALLERY_TITLE', $res['title']);
        $tmpl->assign('GALLERY_DESCRIPTION', $res['description']);
        $tmpl->assign('GALLERY_TIME', $res['galaddtime']);
        $tmpl->assign('GALLERY_LINK', $gallink);
        $tmpl->assign('GALLERY_LASTUPDATE', $res['lastupdate']);

        //Enthaltene Bilder
        if (in_array('GALLERY_COUNT', $parse))
        {
            list($galcount) = $db->first("SELECT count(id) FROM " . PRE . "_user_pictures WHERE galid='" . $res['galid'] . "'");
            $tmpl->assign('GALLERY_COUNT', $galcount);
        }

        //BILD
        $size = getimagesize(BASEDIR . getpath('uploads') . $res['picture']);
        $tmpl->assign('LINK', "javascript:popuppic('misc.php?action=picture&amp;pic=" . $res['picture'] . "','" . $size[0] . "','" . $size[1] . "');");
        $tmpl->assign('CAPTION', $res['caption']);
        $tmpl->assign('IMAGE', getpath('uploads') . $res['thumbnail']);
        $tmpl->assign('FULLSIZE', getpath('uploads') . $res['picture']);
        $tmpl->assign('TIME', $res['addtime']);

        //Benutzer-Infos auslesen
        $tmpl->assign('USERID', $res['owner']);
        if (in_template(array('USERNAME', 'REALNAME', 'AVATAR', 'AVATER_TITLE'), $parse))
        {
            $userinfo = $user->get_info($res['owner'], 'username,realname,avatar,avatar_title');
            $tmpl->assign('USERNAME', replace($userinfo['username']));
            $tmpl->assign('REALNAME', replace($userinfo['realname']));
            $tmpl->assign('AVATAR', $user->mkavatar($userinfo));
            $tmpl->assign('AVATAR_TITLE', $user->mkavtitle($userinfo));
        }

        $tmpl->parse($template, 'user');
    }

    protected function galleryPrint($data, $template)
    {
        $apx = $this->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');     

        $tmpl = new \tengine($apx);

        //Verwendete Variablen auslesen
        $parse = $apx->tmpl->used_vars($template, 'user');

        if (
            count($data)
        )
        {

            //Benutzer-Infos auslesen
            $userdata = array();
            if (in_template(array('GALLERY.USERNAME', 'GALLERY.REALNAME', 'GALLERY.AVATAR', 'GALLERY.AVATER_TITLE'), $parse))
            {
                $userids = get_ids($data, 'owner');
                $userdata = $user->get_info_multi($userids, 'username,realname,avatar,avatar_title');
            }

            //Galerien auflisten
            $tabledata = array();
            $i = 0;
            $laststamp = "";
            foreach ($data as $res)
            {
                ++$i;

                //Link
                $link = mklink(
                    'user.php?action=gallery&amp;id=' . $res['owner'] . '&amp;galid=' . $res['id'],
                    'user,gallery,' . $res['owner'] . ',' . $res['id'] . ',0.html'
                );

                //Enthaltene Bilder
                if (in_array('GALLERY.COUNT', $parse))
                {
                    list($count) = $db->first("SELECT count(id) FROM " . PRE . "_user_pictures WHERE galid='" . $res['id'] . "'");
                }

                //Vorschau-Bild
                $preview = '';
                if (in_array('GALLERY.PREVIEW', $parse) && (!$res['password'] || $user->info['userid'] == $res['owner'] || $res['password'] == $_COOKIE['usergallery_pwd_' . $res['id']]))
                {
                    list($preview) = $db->first("SELECT thumbnail FROM " . PRE . "_user_pictures WHERE galid='" . $res['id'] . "' ORDER BY RAND() LIMIT 1");
                }

                //Datehead
                if ($laststamp != date('Y/m/d', $res['starttime'] - TIMEDIFF))
                {
                    $tabledata[$i]['DATEHEAD'] = $res['starttime'];
                }

                $tabledata[$i]['ID'] = $res['id'];
                $tabledata[$i]['TITLE'] = $res['title'];
                $tabledata[$i]['DESCRIPTION'] = $res['description'];
                $tabledata[$i]['TIME'] = $res['addtime'];
                $tabledata[$i]['UPDATETIME'] = $res['lastupdate'];
                $tabledata[$i]['LINK'] = $link;
                $tabledata[$i]['COUNT'] = $count;
                $tabledata[$i]['PREVIEW'] = iif($preview, HTTPDIR . getpath('uploads') . $preview);

                //Userinfo
                $userinfo = $userdata[$res['owner']];
                $tabledata[$i]['USERID'] = $res['owner'];
                $tabledata[$i]['USERNAME'] = replace($userinfo['username']);
                $tabledata[$i]['REALNAME'] = replace($userinfo['realname']);
                $tabledata[$i]['AVATAR'] = $user->mkavatar($userinfo);
                $tabledata[$i]['AVATAR_TITLE'] = $user->mkavtitle($userinfo);

                //Kommentare
                if ($apx->is_module('comments') && $res['allowcoms'])
                {
                    require_once(BASEDIR . getmodulepath('comments') . 'class.comments.php');
                    if (!isset($coms)) $coms = new \comments('usergallery', $res['id']);
                    else $coms->mid = $res['id'];

                    $link = mklink(
                        'user.php?action=gallery&amp;id=' . $_REQUEST['id'] . '&amp;galid=' . $res['id'],
                        'user,gallery,' . $_REQUEST['id'] . ',' . $res['id'] . ',0.html'
                    );

                    $tabledata[$i]['COMMENT_COUNT'] = $coms->count();
                    $tabledata[$i]['COMMENT_LINK'] = $coms->link($link);
                    $tabledata[$i]['DISPLAY_COMMENTS'] = 1;
                    if (in_template(array('GALLERY.COMMENT_LAST_USERID', 'GALLERY.COMMENT_LAST_NAME', 'GALLERY.COMMENT_LAST_TIME'), $parse))
                    {
                        $tabledata[$i]['COMMENT_LAST_USERID'] = $coms->last_userid();
                        $tabledata[$i]['COMMENT_LAST_NAME'] = $coms->last_name();
                        $tabledata[$i]['COMMENT_LAST_TIME'] = $coms->last_time();
                    }
                }

                $laststamp = date('Y/m/d', $res['starttime'] - TIMEDIFF);
            }
        }

        $tmpl->assign('GALLERY', $tabledata);
        $tmpl->parse($template, 'user');
    }

    //Ausgabe Liste von Usern
    protected function print($data, $template, $varname = 'USER', $buddylist = false, $templatemodule = 'user')
    {
        global $set, $apx, $db, $user;

        $tmpl = new \tengine($apx);
        $parse = $tmpl->used_vars($template, $templatemodule);
        $tabledata = array();
        if (is_array($data) && count($data))
        {
            $i = 0;
            foreach ($data as $res)
            {
                ++$i;

                $age = 0;
                $birthday = 0;
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
                if (in_array($varname . '.ISBUDDY', $parse))
                {
                    $tabledata[$i]['ISBUDDY'] = $user->is_buddy($res['userid']);
                }

                //Custom-Felder
                for ($ii = 1; $ii <= 10; $ii++)
                {
                    $tabledata[$i]['CUSTOM' . $ii . '_NAME'] = $set['user']['cusfield_names'][($ii - 1)] ?? "";
                    $tabledata[$i]['CUSTOM' . $ii] = compatible_hsc($res['custom' . $ii]);
                }

                //Interaktions-Links
                if (isset($user->info['userid']) && $user->info['userid'])
                {
                    $tabledata[$i]['LINK_SENDPM'] = mklink(
                        'user.php?action=newpm&amp;touser=' . $res['userid'],
                        'user,newpm,' . $res['userid'] . '.html'
                    );

                    $tabledata[$i]['LINK_SENDEMAIL'] = mklink(
                        'user.php?action=newmail&amp;touser=' . $res['userid'],
                        'user,newmail,' . $res['userid'] . '.html'
                    );

                    if (in_array($varname . '.LINK_BUDDY', $parse) && !$user->is_buddy($res['userid']))
                    {
                        $tabledata[$i]['LINK_BUDDY'] = mklink(
                            'user.php?action=addbuddy&amp;id=' . $res['userid'],
                            'user,addbuddy,' . $res['userid'] . '.html'
                        );
                    }
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

        $tmpl->assign($varname, $tabledata);
        $tmpl->parse($template, $templatemodule);
    }

    //Online-Zahl berechnen
    public function user_getonline()
    {
        global $db, $set;
        static $count;
        if (
            !isset($count)
        )
        {
            if ($set['user']['onlinelist'])
            {
                list($count) = $db->first("SELECT count(ip) FROM " . PRE . "_user_online");
            }
            else
            {
                list($count) = $db->first("SELECT count(userid) FROM " . PRE . "_user WHERE lastactive>=" . (time() - $set['user']['timeout'] * 60) . " AND pub_invisible=0");
            }
        }
        return $count;
    }

    //PM-Anzahl auslesen
    public function user_getpms()
    {
        global $db, $set, $user;
        static $count;

        if (
            !isset($count)
        )
        {
            list($count) = $db->first("SELECT count(id) FROM " . PRE . "_user_pms WHERE ( touser='" . $user->info['userid'] . "' AND del_to='0' AND isread='0' )");
        }

        return $count;
    }

    //User-Status
    public function user_status($template = 'userstatus')
    {
        global $set, $apx, $db, $user;
        $tmpl = new \tengine($apx);
        $apx->lang->drop('func_status', 'user');

        if (
            count($set['main']['smilies'])
        )
        {
            $i = 0;
            foreach ($set['main']['smilies'] as $res)
            {
                ++$i;
                $smiledata[$i]['CODE'] = $res['code'];
                $smiledata[$i]['INSERTCODE'] = addslashes($res['code']);
                $smiledata[$i]['IMAGE'] = $res['file'];
                $smiledata[$i]['DESCRIPTION'] = $res['description'];
            }
        }

        $tmpl->assign('POSTTO', 'user.php?action=setstatus');
        $tmpl->assign('SMILEY', $smiledata);
        $tmpl->assign('STATUS', compatible_hsc($user->info['status']));
        $tmpl->assign('STATUS_SMILEY', compatible_hsc($user->info['status_smiley']));

        $tmpl->parse('functions/' . $template, 'user');
    }

}
