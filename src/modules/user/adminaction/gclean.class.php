<?php

namespace Modules\User\AdminAction;

/**
 * 
 */
class GAdd extends \AdminAction
{
    public function execute()
    {
        global $set, $db, $apx;
        $_REQUEST['id'] = (int)$_REQUEST['id'];
        if (
            !$_REQUEST['id']
        ) die('missing ID!');

        if (
            $_POST['send'] == 1
        )
        {
            if (!checkToken()) printInvalidToken();
            elseif ($_POST['moveto'])
            {
                $db->query("UPDATE " . PRE . "_user SET groupid='" . intval($_POST['moveto']) . "' WHERE groupid='" . $_REQUEST['id'] . "'");
                logit('USER_GCLEAN', "ID #" . $_REQUEST['id']);

                //Kategorie löschen
                if ($_POST['delgroup'] && $_REQUEST['id'] > 3)
                {
                    $db->query("DELETE FROM " . PRE . "_user_groups WHERE groupid='" . $_REQUEST['id'] . "' LIMIT 1");
                    logit('USER_GDEL', "ID #" . $_REQUEST['id']);
                }

                printJSRedirect(get_index('user.gshow'));
                return;
            }
        }

        //Andere Gruppen auflisten
        $data = $db->fetch("SELECT groupid,name FROM " . PRE . "_user_groups WHERE groupid!='" . $_REQUEST['id'] . "' ORDER BY name ASC");
        $grouplist = "";
        if (
            count($data)
        )
        {
            foreach ($data as $res)
            {
                $grouplist .= '<option value="' . $res['groupid'] . '" ' . iif($_POST['moveto'] == $res['groupid'], ' selected="selected"') . '>' . replace($res['name']) . '</option>';
            }
        }

        list($title) = $db->first("SELECT username FROM " . PRE . "_user WHERE userid='" . $_REQUEST['id'] . "' LIMIT 1");
        $apx->tmpl->assign('ID', $_REQUEST['id']);
        $apx->tmpl->assign('TITLE', compatible_hsc($title));
        $apx->tmpl->assign('DELGROUP', (int)$_POST['delgroup']);
        $apx->tmpl->assign('GROUPLIST', $grouplist);
        $apx->tmpl->assign('DELETEABLE', $_REQUEST['id'] > 3);

        tmessageOverlay('gclean');        
    }
}