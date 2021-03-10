<?php

namespace Modules\User\PublicAction;

class Login extends \PublicAction
{
    public function execute()
    {
        $apx = $this->publicModule()->module()->apx();

        $apx->lang->drop('login');

        $apx->headline($apx->lang->get('HEADLINE_LOGIN'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
        $apx->titlebar($apx->lang->get('HEADLINE_LOGIN'));
        
        if( $apx->param()->postIf('send') )
        {
            $this->doLogin();
        }
        else
        {
            $this->show();
        }
    }
    
    /**
     * show the login screen
     */
    private function show()
    {
        $apx = $this->publicModule()->module()->apx();
        $postto = mklink('user.php?action=login', 'user.html?action=login');
        $apx->tmpl->assign('POSTTO', $postto);
        $apx->tmpl->parse('login');
    }

    /**
     * Login to apexx system and setting the session cookies
     */
    private function doLogin()
    {
        $apx = $this->publicModule()->module()->apx();

        if (!$_POST['login_user'] || !$_POST['login_pwd']) 
        {
            message('back');
        }
        else
        {

            $res = $apx->db()->first("SELECT userid,password,salt,active,reg_key FROM " . PRE . "_user WHERE LOWER(username_login)='" . addslashes(strtolower($_POST['login_user'])) . "' LIMIT 1");
            list($failcount) = $apx->db()->first("SELECT count(time) FROM " . PRE . "_loginfailed WHERE ( userid='" . $res['userid'] . "' AND time>='" . (time() - 15 * 60) . "' )");

            if ($failcount >= 5) 
            {
                message($apx->lang->get('MSG_BLOCK'), 'javascript:history.back()');
            }
            elseif(
                !$res['userid'] || 
                $res['password'] != md5(md5($_POST['login_pwd']) . $res['salt'])
            )
            {
                if ($res['userid'])
                {
                    $apx->db()->query("INSERT INTO " . PRE . "_loginfailed VALUES ('" . $res['userid'] . "','" . time() . "')");
                }

                if ($failcount == 4)
                {
                    message($apx->lang->get('MSG_BLOCK'), 'javascript:history.back()');
                }
                else 
                {
                    message($apx->lang->get('MSG_FAIL'), 'javascript:history.back()');
                }
            }
            elseif (!$res['active'])
            {
                message($apx->lang->get('MSG_BANNED'), 'javascript:history.back()');
            }
            elseif ($apx->config('user')['useractivation'] == 2 && $res['reg_key'] == 'BYADMIN')
            {
                message($apx->lang->get('MSG_ADMINACTIVATION'), 'javascript:history.back()');
            }
            elseif ($apx->config('user')['useractivation'] == 3 && $res['reg_key'])
            {
                message($apx->lang->get('MSG_NOTACTIVE'), 'javascript:history.back()');
            }
            else
            {
                $apx->session()->set($apx->config('main')['cookie_pre'] . '_userid', $res['userid']);

                //Loginfailed löschen
                $apx->db()->query("DELETE FROM " . PRE . "_loginfailed WHERE userid='" . $res['userid'] . "'");

                //Weiterleitung zur zuletzt besuchten Seite
                $filter = array(
                    'user,login.html',
                    'user.php?action=login'
                );
                $refforward = true;
                foreach ($filter as $url)
                {
                    if (
                        strpos($_SERVER['HTTP_REFERER'], $url) !== false
                    )
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
    }
}
