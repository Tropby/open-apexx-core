<?php

namespace Modules\User\PublicTemplateFunction;

class BirthdaysTomorrow extends \PublicTemplateFunction
{
    public function execute($template = 'birthdays_tomorrow')
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();

        $apx->lang->drop('func_birthdays_tomorrow', 'user');

        $data = $db->fetch("SELECT userid,username,email,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM " . PRE . "_user WHERE ( birthday LIKE '" . date('d-m-', time() + 24 * 3600 - TIMEDIFF) . "%' ) ORDER BY username ASC");
        $this->publicModule()->call('print', array($data, 'functions/' . $template));
    }
}
