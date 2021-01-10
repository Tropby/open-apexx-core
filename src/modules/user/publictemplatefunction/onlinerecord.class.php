<?php

namespace Modules\User\PublicTemplateFunction;

class OnlineRecord extends \PublicTemplateFunction
{
    public function execute($template = 'onlinerecord')
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');

        $tmpl = new \tengine($apx);
        $apx->lang->drop('func_online', 'user');

        $nowonline = user_getonline();

        if (
            $nowonline > $apx->config('user')['onlinerecord']
        )
        {
            $recordtime = time();
            $set['user']['onlinerecord'] = $nowonline;
            $set['user']['onlinerecord_time'] = $recordtime;
            $db->query("UPDATE " . PRE . "_config SET value='" . $nowonline . "' WHERE module='user' AND varname='onlinerecord' LIMIT 1");
            $db->query("UPDATE " . PRE . "_config SET value='" . $recordtime . "' WHERE module='user' AND varname='onlinerecord_time' LIMIT 1");
        }

        $tmpl->assign('COUNT', $set['user']['onlinerecord']);
        $tmpl->assign('TIME', $set['user']['onlinerecord_time']);
        $tmpl->parse('functions/' . $template, 'user');
    }
}
