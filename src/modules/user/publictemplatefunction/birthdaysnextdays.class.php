<?php

namespace Modules\User\PublicTemplateFunction;

class BirthdaysNextDays extends \PublicTemplateFunction
{
    public function execute($days = 5, $template = 'birthdays_nextdays')
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();

        $apx->lang->drop('func_birthdays_nextdays', 'user');

        $today = date('j', time() - TIMEDIFF);
        $daylist = array();
        for ($i = 0; $i < $days; $i++)
        {
            $timestamp = mktime(0, 0, 0, date('n', time() - TIMEDIFF), $today + 1 + $i, date('Y', time() - TIMEDIFF)) + TIMEDIFF;
            $daylist[] = "birthday LIKE '" . date('d-m', $timestamp - TIMEDIFF) . "%'";
        }

        $data = $db->fetch("SELECT userid,username,email,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM " . PRE . "_user WHERE ( " . implode(' OR ', $daylist) . " ) ORDER BY username ASC");
        $this->publicModule()->call('print', array($data, 'functions/' . $template));
    }
}
