<?php

namespace Modules\User\PublicTemplateFunction;

class Online extends \PublicTemplateFunction
{
    public function execute($template = 'online')
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');
        
        $tmpl = new \tengine($apx);
        $apx->lang->drop('func_online', 'user');
        $count = user_getonline();
        $tmpl->assign('COUNT', $count);
        $tmpl->parse('functions/' . $template, 'user');
    }
}
