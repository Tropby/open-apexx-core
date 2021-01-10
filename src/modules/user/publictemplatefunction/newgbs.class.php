<?php

namespace Modules\User\PublicTemplateFunction;

class NewGBS extends \PublicTemplateFunction
{
    public function execute($template = 'newgbentries')
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');
        
        $tmpl = new \tengine($apx);
        if (
            !$user->info['userid']
        ) return;

        $apx->lang->drop('func_newgbs', 'user');

        list($count) = $db->first("SELECT count(id) FROM " . PRE . "_user_guestbook WHERE owner='" . $user->info['userid'] . "' AND userid!='" . $user->info['userid'] . "' AND time>'" . $user->info['lastonline'] . "'");
        $tmpl->assign('COUNT', $count);
        $tmpl->parse('functions/' . $template, 'user');
    }
}
