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
        $this->registerAction("team");

        /** 
         * Register all tempaltes that can be used in public scope
         */
        $this->registerTemplateFunction('Info', 'USER_INFO');
        $this->registerTemplateFunction('Stats', 'USER_STATS');
        $this->registerTemplateFunction('GalleryLastPics', 'USERGALLERY_LASTPICS');
        $this->registerTemplateFunction('USER_INFO', 'info', true);
        $this->registerTemplateFunction('USERONLINE', 'online', false);
        $this->registerTemplateFunction('NEWPMS', 'newpms', false);
        $this->registerTemplateFunction('NEWGBENTRIES', 'newgbs', false);
        $this->registerTemplateFunction('ONLINELIST', 'onlinelist', true);
        $this->registerTemplateFunction('LOGINBOX', 'loginbox', false);
        $this->registerTemplateFunction('BIRTHDAYS', 'birthdays', true);
        $this->registerTemplateFunction('BIRTHDAYS_TOMORROW', 'birthdaystomorrow', true);
        $this->registerTemplateFunction('BIRTHDAYS_NEXTDAYS', 'birthdaysnextdays', true);
        $this->registerTemplateFunction('BUDDYLIST', 'buddylist', true);
        $this->registerTemplateFunction('NEWUSER', 'newuser', true);
        $this->registerTemplateFunction('RANDOMUSER', 'random', true);
        $this->registerTemplateFunction('PROFILE', 'PROFILE', true);
        $this->registerTemplateFunction('BOOKMARK', 'bookmarklink', false);
        $this->registerTemplateFunction('SHOWBOOKMARKS', 'bookmarks', true);
        $this->registerTemplateFunction('ONLINERECORD', 'onlinerecord', true);
        $this->registerTemplateFunction('USERBLOGS', 'blogslast', true);
        $this->registerTemplateFunction('USERGALLERY_LAST', 'gallerylast', true);
        $this->registerTemplateFunction('USERGALLERY_UPDATED', 'galleryupdated', true);
        $this->registerTemplateFunction('USERGALLERY_LASTPICS', 'gallerylastpics', true);
        $this->registerTemplateFunction('USERGALLERY_POTM', 'gallerypotm', true);
        $this->registerTemplateFunction('USERSTATUS', 'status', true);
    }

    function search($items, $conn)
    {
        //Suchstring generieren
        foreach ($items as $item) {
            $search[] = "username LIKE '%" . addslashes_like($item) . "%'";
        }

        //Ergebnisse
        $data = $this->apx->db()->fetch("SELECT userid,username FROM " . PRE . "_user WHERE ( " . implode($conn, $search) . " ) ORDER BY username ASC");
        if (count($data)) {
            $i = 0;
            foreach ($data as $res) {
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

        if (count($data)) {

            //Benutzer-Infos auslesen
            $userdata = array();
            if (in_template(array('PICTURE.USERNAME', 'PICTURE.REALNAME', 'PICTURE.AVATAR', 'PICTURE.AVATER_TITLE'), $parse)) {
                $userids = get_ids($data, 'owner');
                $userdata = $user->get_info_multi($userids, 'username,realname,avatar,avatar_title');
            }

            //Bilder auflisten
            $tabledata = array();
            $i = 0;
            foreach ($data as $res) {
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
                if (in_array('PICTURE.GALLERY_COUNT', $parse)) {
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
        if (in_array('GALLERY_COUNT', $parse)) {
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
        if (in_template(array('USERNAME', 'REALNAME', 'AVATAR', 'AVATER_TITLE'), $parse)) {
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
        ) {

            //Benutzer-Infos auslesen
            $userdata = array();
            if (in_template(array('GALLERY.USERNAME', 'GALLERY.REALNAME', 'GALLERY.AVATAR', 'GALLERY.AVATER_TITLE'), $parse)) {
                $userids = get_ids($data, 'owner');
                $userdata = $user->get_info_multi($userids, 'username,realname,avatar,avatar_title');
            }

            //Galerien auflisten
            $tabledata = array();
            $i = 0;
            $laststamp = "";
            foreach ($data as $res) {
                ++$i;

                //Link
                $link = mklink(
                    'user.php?action=gallery&amp;id=' . $res['owner'] . '&amp;galid=' . $res['id'],
                    'user,gallery,' . $res['owner'] . ',' . $res['id'] . ',0.html'
                );

                //Enthaltene Bilder
                if (in_array('GALLERY.COUNT', $parse)) {
                    list($count) = $db->first("SELECT count(id) FROM " . PRE . "_user_pictures WHERE galid='" . $res['id'] . "'");
                }

                //Vorschau-Bild
                $preview = '';
                if (in_array('GALLERY.PREVIEW', $parse) && (!$res['password'] || $user->info['userid'] == $res['owner'] || $res['password'] == $_COOKIE['usergallery_pwd_' . $res['id']])) {
                    list($preview) = $db->first("SELECT thumbnail FROM " . PRE . "_user_pictures WHERE galid='" . $res['id'] . "' ORDER BY RAND() LIMIT 1");
                }

                //Datehead
                if ($laststamp != date('Y/m/d', $res['starttime'] - TIMEDIFF)) {
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
                if ($apx->is_module('comments') && $res['allowcoms']) {
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
                    if (in_template(array('GALLERY.COMMENT_LAST_USERID', 'GALLERY.COMMENT_LAST_NAME', 'GALLERY.COMMENT_LAST_TIME'), $parse)) {
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
        if (is_array($data) && count($data)) {
            $i = 0;
            foreach ($data as $res) {
                ++$i;

                $age = 0;
                $birthday = 0;
                if ($res['birthday']) {
                    $bd = explode('-', $res['birthday']);
                    $birthday = intval($bd[0]) . '. ' . getcalmonth($bd[1]) . iif($bd[2], ' ' . $bd[2]);
                    if ($bd[2]) {
                        $age = date('Y') - $bd[2];
                        if (intval(sprintf('%02d%02d', $bd[1], $bd[0])) > intval(date('md'))) {
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
                if (in_array($varname . '.ISBUDDY', $parse)) {
                    $tabledata[$i]['ISBUDDY'] = $user->is_buddy($res['userid']);
                }

                //Custom-Felder
                for ($ii = 1; $ii <= 10; $ii++) {
                    $tabledata[$i]['CUSTOM' . $ii . '_NAME'] = $set['user']['cusfield_names'][($ii - 1)] ?? "";
                    $tabledata[$i]['CUSTOM' . $ii] = compatible_hsc($res['custom' . $ii]);
                }

                //Interaktions-Links
                if (isset($user->info['userid']) && $user->info['userid']) {
                    $tabledata[$i]['LINK_SENDPM'] = mklink(
                        'user.php?action=newpm&amp;touser=' . $res['userid'],
                        'user,newpm,' . $res['userid'] . '.html'
                    );

                    $tabledata[$i]['LINK_SENDEMAIL'] = mklink(
                        'user.php?action=newmail&amp;touser=' . $res['userid'],
                        'user,newmail,' . $res['userid'] . '.html'
                    );

                    if (in_array($varname . '.LINK_BUDDY', $parse) && !$user->is_buddy($res['userid'])) {
                        $tabledata[$i]['LINK_BUDDY'] = mklink(
                            'user.php?action=addbuddy&amp;id=' . $res['userid'],
                            'user,addbuddy,' . $res['userid'] . '.html'
                        );
                    }
                }

                //Nur Buddy-Liste
                if ($buddylist) {
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
        ) {
            if ($set['user']['onlinelist']) {
                list($count) = $db->first("SELECT count(ip) FROM " . PRE . "_user_online");
            } else {
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
        ) {
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
        ) {
            $i = 0;
            foreach ($set['main']['smilies'] as $res) {
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


    //Download-Größe
    function user_getsize($fsize, $digits = 1)
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
    function user_count_visit($object, $id)
    {
        global $apx, $set, $db, $user;
        if (!$user->info['userid']) return;
        $db->query("DELETE FROM " . PRE . "_user_visits WHERE object='" . $object . "' AND userid='" . $user->info['userid'] . "'");
        $db->query("INSERT INTO " . PRE . "_user_visits (object,id,userid,time) VALUES ('" . $object . "','" . $id . "','" . $user->info['userid'] . "','" . time() . "')");
    }



    //Besucher assign
    function user_assign_visitors($object, $id, &$tmpl)
    {
        global $apx, $set, $db, $user;

        $userdata = array();
        $data = $db->fetch("SELECT u.userid,u.username,u.groupid,u.realname,u.gender,u.city,u.plz,u.country,u.city,u.lastactive,u.pub_invisible,u.avatar,u.avatar_title,u.custom1,u.custom2,u.custom3,u.custom4,u.custom5,u.custom6,u.custom7,u.custom8,u.custom9,u.custom10 FROM " . PRE . "_user_visits AS v LEFT JOIN " . PRE . "_user AS u USING(userid) WHERE v.object='" . addslashes($object) . "' AND v.id='" . intval($id) . "' AND v.time>='" . (time() - 24 * 3600) . "' ORDER BY u.username ASC");
        if (count($data)) {
            $i = 0;
            foreach ($data as $res) {
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
                for ($ii = 1; $ii <= 10; $ii++) {
                    $tabledata[$i]['CUSTOM' . $ii . '_NAME'] = $set['user']['cusfield_names'][($ii - 1)];
                    $tabledata[$i]['CUSTOM' . $ii] = compatible_hsc($res['custom' . $ii]);
                }
            }
        }

        $tmpl->assign('VISITOR', $userdata);
    }



    //Links zu Profil-Funktionen
    function user_assign_profile_links(&$tmpl, $userinfo)
    {
        global $apx, $set, $db, $user;

        $link_profile = mklink(
            'user.php?action=profile&amp;id=' . $userinfo['userid'],
            'user,profile,' . $userinfo['userid'] . urlformat($userinfo['username']) . '.html'
        );
        if ($set['user']['blog']) {
            $link_blog = mklink(
                'user.php?action=blog&amp;id=' . $userinfo['userid'],
                'user,blog,' . $userinfo['userid'] . ',1.html'
            );
        }
        if ($set['user']['gallery']) {
            $link_gallery = mklink(
                'user.php?action=gallery&amp;id=' . $userinfo['userid'],
                'user,gallery,' . $userinfo['userid'] . ',0,0.html'
            );
        }
        if ($set['user']['guestbook'] && $userinfo['pub_usegb']) {
            $link_guestbook = mklink(
                'user.php?action=guestbook&amp;id=' . $userinfo['userid'],
                'user,guestbook,' . $userinfo['userid'] . ',1.html'
            );
        }
        if ($apx->is_module('products') && $set['products']['collection']) {
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
