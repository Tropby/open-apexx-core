<?php

namespace Modules\User\PublicTemplateFunction;

class BuddyList extends \PublicTemplateFunction
{
    public function execute($template = 'buddylist')
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');

        $apx->lang->drop('func_buddylist', 'user');

        $buddies = $user->get_buddies();
        if (count($buddies))
        {
            $data = $db->fetch("SELECT userid,username,email,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM " . PRE . "_user WHERE userid IN (" . implode(',', $buddies) . ") ORDER BY username ASC");
        }
        $this->publicModule()->call('print', array($data, 'functions/' . $template, 'BUDDY', true));
    }

}
