<?php

namespace Modules\User\AdminTemplateFunction;

class User extends \PublicTemplateFunction
{
    public function execute($selected = 0)
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');

        $selected = (int)$selected;

        $data = $db->fetch("SELECT a.userid,a.username FROM " . PRE . "_user AS a LEFT JOIN " . PRE . "_user_groups AS b USING(groupid) WHERE ( " . iif($selected, "userid='" . $selected . "' OR") . " ( active='1' AND b.gtype IN ('admin','indiv') ) ) ORDER BY username ASC");
        if (!count($data)) return;

        foreach ($data as $res)
        {
            echo '<option value="' . $res['userid'] . '"' . iif($res['userid'] == $selected, ' selected="selected"') . '>' . replace($res['username']) . '</option>';
        }
    }
}
