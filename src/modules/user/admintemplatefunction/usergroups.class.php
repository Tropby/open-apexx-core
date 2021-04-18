<?php

namespace Modules\User\AdminTemplateFunction;

class UserGroups extends \PublicTemplateFunction
{
    public function execute($selected = 0)
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');

        if (!is_array($selected))
            $selected = array();

        $numargs = func_num_args();
        $arg_list = func_get_args();
        for ($i = 0; $i < $numargs; $i++)
        {
            $selected[] = $arg_list[$i];
        }
        $data = $db->fetch("SELECT * FROM " . PRE . "_user_groups AS a GROUP BY name ASC");
        if (!count($data)) return;

        foreach ($data as $res)
        {
            echo '<option value="' . $res['groupid'] . '"' . iif(in_array($res[' groupid'], $selected), ' selected="selected"') . '>' . replace($res['name']) . '</option>';
        }
    }
}
