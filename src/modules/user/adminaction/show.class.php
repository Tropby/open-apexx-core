<?php

namespace Modules\User\AdminAction;

/**
 * 
 */
class Show extends \AdminAction
{
    public function execute()
    {
        global $set,$apx,$db,$html;

        //Suche durchführen
        if ( ( $_REQUEST['item'] && ( $_REQUEST['name'] || $_REQUEST['profile'] ) ) || $_POST['sgroupid'] ) {
        $where = '';
        if ( $_REQUEST['item'] ) {
        if ( $_REQUEST['name'] ) {
        $sc[]="username LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="username_login LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        }
        if ( $_REQUEST['profile'] ) {
        $sc[]="email LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="homepage LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="icq LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="aim LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="yim LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="msn LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="skype LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="realname LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="city LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="plz LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="interests LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="work LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="city LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="custom1 LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="custom2 LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="custom3 LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="custom4 LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="custom5 LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="custom6 LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="custom7 LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="custom8 LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="custom9 LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        $sc[]="custom10 LIKE '%".addslashes_like($_REQUEST['item'])."%'";
        }
        if ( is_array($sc) ) $where.=' AND ( '.implode(' OR ',$sc).' )';
        }
        if ( $_REQUEST['sgroupid'] ) $where.=" AND a.groupid='".intval($_REQUEST['sgroupid'])."'";

        $data=$db->fetch("SELECT userid FROM ".PRE."_user AS a LEFT JOIN ".PRE."_user_groups AS b USING(groupid) WHERE 1 ".$where);
        $ids = get_ids($data, 'userid');
        $ids[] = -1;
        $searchid = saveSearchResult('admin_user', $ids, array(
        'item' => $_REQUEST['item'],
        'name' => $_REQUEST['name'],
        'profile' => $_REQUEST['profile'],
        'groupid' => $_REQUEST['sgroupid']
        ));
        header("HTTP/1.1 301 Moved Permanently");
        header('Location: action.php?action=user.show&who='.$_REQUEST['who'].'&searchid='.$searchid);
        return;
        }


        //Voreinstellungen
        $_REQUEST['name'] = 1;

        quicklink('user.add');

        $layerdef[]=array('LAYER_TEAM','action.php?action=user.show',!$_REQUEST['who']);
        $layerdef[]=array('LAYER_ALL','action.php?action=user.show&amp;who=all',$_REQUEST['who']=='all');
        $layerdef[]=array('LAYER_ACTIVATE','action.php?action=user.show&amp;who=activate',$_REQUEST['who']=='activate');

        //Layer Header ausgeben
        $html->layer_header($layerdef);

        $orderdef[0]='user_login';
        $orderdef['active']=array('a.active','DESC','COL_ACTIVE');
        $orderdef['user_login']=array('a.username_login','ASC','COL_USER_LOGIN');
        $orderdef['user']=array('a.username','ASC','COL_USER');
        $orderdef['regtime']=array('a.reg_time','DESC','COL_REGTIME');
        $orderdef['lastactive']=array('a.lastactive','DESC','COL_LASTACTIVE');
        $orderdef['group']=array('b.name','ASC','COL_GROUP');

        //Suchergebnis?
        $resultFilter = '';
        if ( $_REQUEST['searchid'] ) {
        $searchRes = getSearchResult('admin_user', $_REQUEST['searchid']);
        if ( $searchRes ) {
        list($resultIds, $resultMeta) = $searchRes;
        $_REQUEST['item'] = $resultMeta['item'];
        $_REQUEST['name'] = $resultMeta['name'];
        $_REQUEST['profile'] = $resultMeta['profile'];
        $_REQUEST['sgroupid'] = $resultMeta['groupid'];
        $resultFilter = " AND a.userid IN (".implode(', ', $resultIds).")";
        }
        else {
        $_REQUEST['searchid'] = '';
        }
        }

        //Suchformular
        $grouplist = "";
        $data=$db->fetch("SELECT groupid,name FROM ".PRE."_user_groups ORDER BY name ASC");
        if ( count($data) ) {
        foreach ( $data AS $res ) {
        $grouplist.='<option value="'.$res['groupid'].'"'.iif($_REQUEST[' sgroupid']==$res['groupid'],' selected="selected"').'>'.replace($res['name']).'</option>';
        }
        }
        $apx->tmpl->assign('ITEM', compatible_hsc($_REQUEST['item']));
        $apx->tmpl->assign('SNAME', $_REQUEST['name']);
        $apx->tmpl->assign('SPROFILE', $_REQUEST['profile']);
        $apx->tmpl->assign('GROUPS', $grouplist);
        $apx->tmpl->assign('WHO', $_REQUEST['who']);
        $apx->tmpl->parse('search');


        $layerFilter = '';
        if ( $_REQUEST['who']=='all' ) {
        //$layerFilter = ""; //Nix :)
        }
        elseif ( $_REQUEST['who']=='activate' ) {
        $layerFilter = " AND reg_key!='' ";
        }
        else {
        $admingroups=array();
        $data=$db->fetch("SELECT groupid FROM ".PRE."_user_groups WHERE ( gtype='admin' OR gtype='indiv' )");

        if ( count($data) ) {
        foreach ( $data AS $res ) $admingroups[]=$res['groupid'];
        }
        $layerFilter = " AND a.groupid IN (".implode(',',$admingroups).") ";
        }


        letters('action.php?action=user.show&amp;who='.$_REQUEST['who'].iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']));
        $letterfilter = '';
        if ( $_REQUEST['letter']=='spchar' ) $letterfilter=" AND username NOT REGEXP(\"^[a-zA-Z]\") ";
        elseif ( $_REQUEST['letter'] ) $letterfilter=" AND username LIKE '".addslashes($_REQUEST['letter'])."%' ";

        list($count)=$db->first("SELECT count(userid) FROM ".PRE."_user AS a WHERE 1 ".$layerFilter.$letterfilter.$resultFilter);
        pages('action.php?action=user.show&amp;who='.$_REQUEST['who'].'&amp;letter='.$_REQUEST['letter'].'&amp;sortby='.$_REQUEST['sortby'].iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']),$count);
        $data=$db->fetch("SELECT a.userid,a.username_login,a.username,a.active,a.reg_key,a.lastactive,b.name FROM ".PRE."_user AS a LEFT JOIN ".PRE."_user_groups AS b USING(groupid) WHERE 1 ".$layerFilter.$letterfilter.$resultFilter." ".getorder($orderdef).getlimit());
        $this->show_print($data);
        orderstr($orderdef,'action.php?action=user.show&amp;who='.$_REQUEST['who'].'&amp;letter='.$_REQUEST['letter'].iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']));
        save_index($_SERVER['REQUEST_URI']);


        //Layer-Footer ausgeben
        $html->layer_footer();
    }

    function show_print($data)
    {
        global $set, $apx, $db, $html;

        $user = $this->adminModule()->module()->apx()->get_registered_object("user");

        $col[] = array('', 1, 'align="center"');
        $col[] = array('COL_USER_LOGIN', 20, 'class="title"');
        $col[] = array('COL_USER', 20, 'align="center"');
        $col[] = array('COL_GROUP', 30, 'align="center"');
        $col[] = array('COL_LASTACTIVE', 20, 'align="center"');

        //Benutzer-IDs auslesen
        $userids = get_ids($data, 'userid');

        //Blog-Einträge zählen
        $blogcount = array();
        if ($set['user']['blog'] && count($userids))
        {
            $blogcount = $db->fetch_index("SELECT userid,count(*) AS count FROM " . PRE . "_user_blog WHERE userid IN (" . implode(',', $userids) . ") GROUP BY userid", 'userid');
        }

        //Gästebucheinträge zählen
        $gbcount = array();
        if ($set['user']['guestbook'] && count($userids))
        {
            $gbcount = $db->fetch_index("SELECT owner,count(*) AS count FROM " . PRE . "_user_guestbook WHERE owner IN (" . implode(',', $userids) . ") GROUP BY owner", 'owner');
        }

        //Galerien zählen
        $galcount = array();
        if ($set['user']['gallery'] && count($userids))
        {
            $galcount = $db->fetch_index("SELECT owner,count(*) AS count FROM " . PRE . "_user_gallery WHERE owner IN (" . implode(',', $userids) . ") GROUP BY owner", 'owner');
        }

        if (count($data))
        {
            $obj = 0;
            foreach ($data as $res)
            {
                ++$obj;

                if ($res['userid'] != $user->info['userid']) $tabledata[$obj]['ID'] = $res['userid'];

                if ($res['active']) $tabledata[$obj]['COL1'] = '<img src="design/active.gif" title="' . $apx->lang->get('CORE_ACTIVE') . '" alt="' . $apx->lang->get('CORE_ACTIVE') . '" />';
                else $tabledata[$obj]['COL1'] = '<img src="design/inactive.gif" title="' . $apx->lang->get('CORE_INACTIVE') . '" alt="' . $apx->lang->get('CORE_INACTIVE') . '" />';

                $tabledata[$obj]['COL2'] = '<a href="';
                $tabledata[$obj]['COL2'] .= mklink(
                    'user.php?action=profile&amp;id=' . $res['userid'],
                    'user,profile,' . $res['userid'] . urlformat($res['username']) . '.html',
                    iif($set['main']['forcesection'], $apx->section_default, 0)
                );
                $tabledata[$obj]['COL2'] .= '" target="_blank">' . replace($res['username_login']) . '</a>';
                $tabledata[$obj]['COL3'] = replace($res['username']);
                $tabledata[$obj]['COL4'] = replace($res['name']);
                $tabledata[$obj]['COL5'] = $res['lastactive'] ? mkdate($res['lastactive'], '<br />') : '';

                //Optionen
                $tabledata[$obj]['OPTIONS'] = '';
                if ($res['userid'] != $user->info['userid'] && $user->has_right('user.edit')) $tabledata[$obj]['OPTIONS'] .= optionHTML('edit.gif', 'user.edit', 'id=' . $res['userid'], $apx->lang->get('CORE_EDIT'));
                else $tabledata[$obj]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';

                if ($res['userid'] != $user->info['userid'] && $user->has_right('user.del')) $tabledata[$obj]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'user.del', 'id=' . $res['userid'], $apx->lang->get('CORE_DEL'));
                else $tabledata[$obj]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';

                if ($res['userid'] != $user->info['userid'] && $user->has_right('user.enable') && $res['reg_key'] == 'BYADMIN') $tabledata[$obj]['OPTIONS'] .= optionHTML('enable.gif', 'user.enable', 'id=' . $res['userid'] . '&sectoken=' . $apx->session->get('sectoken'), $apx->lang->get('CORE_ENABLE'));
                else $tabledata[$obj]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';

                if ($user->has_right('user.profile')) $tabledata[$obj]['OPTIONS'] .= optionHTML('details.gif', 'user.profile', 'id=' . $res['userid'], $apx->lang->get('PROFILE'));
                else $tabledata[$obj]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';

                if ($set['user']['blog'] && $blogcount[$res['userid']]['count'] && $user->has_right('user.blog')) $tabledata[$obj]['OPTIONS'] .= optionHTML('blog.gif', 'user.blog', 'userid=' . $res['userid'], $apx->lang->get('BLOG'));
                else $tabledata[$obj]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';

                if ($set['user']['guestbook'] && $gbcount[$res['userid']]['count'] && $user->has_right('user.guestbook')) $tabledata[$obj]['OPTIONS'] .= optionHTML('comments.gif', 'user.guestbook', 'userid=' . $res['userid'], $apx->lang->get('GUESTBOOK'));
                else $tabledata[$obj]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';

                if ($set['user']['gallery'] && $galcount[$res['userid']]['count'] && $user->has_right('user.gallery')) $tabledata[$obj]['OPTIONS'] .= optionHTML('pic.gif', 'user.gallery', 'userid=' . $res['userid'], $apx->lang->get('GALLERY'));
                else $tabledata[$obj]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';

                if (!$tabledata[$obj]['OPTIONS']) $tabledata[$obj]['OPTIONS'] .= '&nbsp;';
            }
        }

        $multiactions = array();
        if ($user->has_right('user.edit')) $multiactions[] = array($apx->lang->get('MULTI_EDIT'), 'action.php?action=user.edit', true);

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col, $multiactions);
    }
}