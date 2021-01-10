<?php

namespace Modules\User\PublicTemplateFunction;

class Random extends \PublicTemplateFunction
{
    public function execute($count = 5, $template = 'randomuser')
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');

        $count = (int)$count;
        if ($count < 1) $count = 1;
        $apx->lang->drop('func_newuser', 'user');

        $data = $db->fetch("SELECT userid,username,email,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM " . PRE . "_user WHERE reg_key='' ORDER BY RAND() LIMIT " . $count);
        $this->publicModule()->call('print', array($data, 'functions/' . $template));
    }

}