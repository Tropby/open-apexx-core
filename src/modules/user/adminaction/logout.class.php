<?php

namespace Modules\User\AdminAction;

/**
 * 
 */
class Logout extends \AdminAction
{
    public function execute()
    {

        global $set, $apx, $db;

        if (
            !checkToken()
        ) printInvalidToken();
        else
        {
            $apx->session->destroy();
            setcookie($set['main']['cookie_pre'] . '_admin_userid', 0, time() - 99999, '/');
            setcookie($set['main']['cookie_pre'] . '_admin_password', 0, time() - 99999, '/');
            /*setcookie($set['main']['cookie_pre'].'_userid',$res['userid'],time()+100*24*3600,'/');
		setcookie($set['main']['cookie_pre'].'_password',$res['password'],time()+100*24*3600,'/');*/
            unset(
                $_COOKIE[$set['main']['cookie_pre'] . '_admin_userid'],
                $_COOKIE[$set['main']['cookie_pre'] . '_admin_password']
            );

            if ($apx->user->info['userid']) logit('USER_LOGOUT');

            if (!$apx->user->info['userid']) message($apx->lang->get('MSG_NOLOGIN'), 'action.php');
            else
            {
                header("HTTP/1.1 301 Moved Permanently");
                header('Location: index.php');
            }
        }
    }
}
