<?php

namespace Modules\User\PublicTemplateFunction;

class NewPMS extends \PublicTemplateFunction
{
    public function execute($template = 'newpms')
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');
        
        $tmpl = new \tengine($apx);
        if (
            !$user->info['userid']
        ) return;
        $apx->lang->drop('func_newpms', 'user');
        $count = user_getpms();
        $tmpl->assign('COUNT', $count);
        $tmpl->parse('functions/' . $template, 'user');
    }
}
