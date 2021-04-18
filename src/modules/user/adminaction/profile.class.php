<?php

namespace Modules\User\AdminAction;


/**
 * 
 */
class profile extends \AdminAction
{
    public function execute()
    {
        global $set, $apx, $tmpl, $db, $user;
        if (!$_REQUEST['id']) die('missing ID!');

        $res = $db->first("SELECT a.userid,a.username,a.email,a.reg_time,a.reg_email,a.lastactive,b.name FROM " . PRE . "_user AS a LEFT JOIN " . PRE . "_user_groups AS b USING(groupid) WHERE a.userid='" . $_REQUEST['id'] . "'");

        $apx->tmpl->assign('USERID', $res['userid']);
        $apx->tmpl->assign('USERNAME', replace($res['username']));
        $apx->tmpl->assign('REGDATE', mkdate($res['reg_time']));
        $apx->tmpl->assign('REGEMAIL', replace($res['reg_email']));
        $apx->tmpl->assign('EMAIL', replace($res['email']));
        $apx->tmpl->assign('LASTACTIVE', mkdate($res['lastactive']));
        $apx->tmpl->assign('GROUPNAME', replace($res['name']));

        $apx->tmpl->parse('profile');
    }
}
