<?php

namespace Modules\User\AdminAction;

/**
 * 
 */
class GDel extends \AdminAction
{
    public function execute()
    {
        global $set, $apx, $db;
        $_REQUEST['id'] = (int)$_REQUEST['id'];
        if (
            $_REQUEST['id'] <= 3
        ) die('can not delete group!');
        if (
            !$_REQUEST['id']
        ) die('missing ID!');

        list($count) = $db->first("SELECT count(userid) FROM " . PRE . "_user WHERE groupid='" . $_REQUEST['id'] . "'");
        if (
            $count
        ) die('usergroup is still in use!');

        if (
            $_POST['send'] == 1
        )
        {
            if (!checkToken()) printInvalidToken();
            else
            {
                $db->query("DELETE FROM " . PRE . "_user_groups WHERE groupid='" . $_REQUEST['id'] . "' LIMIT 1");
                logit('USER_GDEL', 'ID #' . $_REQUEST['id']);
                printJSReload();
            }
        }
        else
        {
            list($title) = $db->first("SELECT name FROM " . PRE . "_user_groups WHERE groupid='" . $_REQUEST['id'] . "' LIMIT 1");
            $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
            $input['ID'] = $_REQUEST['id'];
            tmessageOverlay('deltitle', $input, '/');
        }        
    }
}