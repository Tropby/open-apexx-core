<?php

namespace Modules\User\PublicAction;

class Logout extends \PublicAction
{
    public function execute()
    {
        $apx = $this->publicModule()->module()->apx();
        
        $apx->session()->destroy();

        $apx->lang->drop('logout');

        $apx->session()->destroy();

        //Weiterleitung zur zuletzt besuchten Seite
        $filter = array(
            'user,login.html',
            'user.php?action=login'
        );
        $refforward = true;
        foreach ($filter as $url)
        {
            if (strpos($_SERVER['HTTP_REFERER'], $url) !== false)
            {
                $refforward = false;
                break;
            }
        }
        if ($refforward && $_SERVER['HTTP_REFERER']) $goto = $_SERVER['HTTP_REFERER'];
        else $goto = mklink('user.php', 'user.html');

        message($apx->lang->get('MSG_OK'), $goto);
    }
}
