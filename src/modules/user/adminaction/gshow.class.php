<?php

namespace Modules\User\AdminAction;

/**
 * 
 */
class GShow extends \AdminAction
{
    public function execute()
    {
        global $set, $apx, $db, $html;

        quicklink('user.gadd');

        $orderdef[0] = 'group';
        $orderdef['group'] = array('name', 'ASC', 'COL_GROUP');

        $col[] = array('COL_GROUP', 75, 'class="title"');
        $col[] = array('COL_USERS', 25, 'align="center"');

        $data = $db->fetch("SELECT a.*,count(b.groupid) AS count FROM " . PRE . "_user_groups AS a LEFT JOIN " . PRE . "_user AS b USING(groupid) GROUP BY a.groupid " . getorder($orderdef));
        if (
            count($data)
        )
        {
            $obj=0;
            foreach ($data as $res)
            {
                ++$obj;

                $tabledata[$obj]['COL1'] = replace($res['name']);
                $tabledata[$obj]['COL2'] = replace($res['count']);

                //Optionen
                if ($apx->user->has_right('user.gedit')) $tabledata[$obj]['OPTIONS'] .= optionHTML('edit.gif', 'user.gedit', 'id=' . $res['groupid'], $apx->lang->get('CORE_EDIT'));
                else $tabledata[$obj]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';

                if ($res['groupid'] > 3 && $apx->user->has_right('user.gdel') && !$res['count']) $tabledata[$obj]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'user.gdel', 'id=' . $res['groupid'], $apx->lang->get('CORE_DEL'));
                else $tabledata[$obj]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';

                if ($apx->user->has_right('user.gclean') && $res['count']) $tabledata[$obj]['OPTIONS'] .= optionHTMLOverlay('clean.gif', 'user.gclean', 'id=' . $res['groupid'], $apx->lang->get('CLEAN'));
                else $tabledata[$obj]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
            }
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col);

        orderstr($orderdef, 'action.php?action=user.gshow');
        save_index($_SERVER['REQUEST_URI']);
    }
}
