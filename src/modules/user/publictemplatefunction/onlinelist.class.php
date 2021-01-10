<?php

namespace Modules\User\PublicTemplateFunction;

class OnlineList extends \PublicTemplateFunction
{
    public function execute($template = 'onlinelist')
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');

        $apx->lang->drop('func_onlinelist', 'user');

        $data = $db->fetch("SELECT userid,username,email,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM " . PRE . "_user WHERE ( lastactive>=" . (time() - $set['user']['timeout'] * 60) . " AND pub_invisible=0 ) ORDER BY username ASC");
        $this->publicModule()->call('print', array($data, 'functions/' . $template));
    }

}
