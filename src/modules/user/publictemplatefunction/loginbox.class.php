<?php

namespace Modules\User\PublicTemplateFunction;

class LoginBox extends \PublicTemplateFunction
{
    public function execute($template = 'loginbox')
    {
        $apx = $this->publicModule()->module()->apx();
        $user = $apx->get_registered_object('user');
        
        $tmpl = new \tengine($apx);
        $apx->lang->drop('func_loginbox', 'user');

        if (!isset($user->info['userid']) || !$user->info['userid']) 
        {
            $tmpl->assign('POSTTO', mklink('user.php', 'user.html'));
        }
        $tmpl->parse('functions/' . $template, 'user');
    }
}
