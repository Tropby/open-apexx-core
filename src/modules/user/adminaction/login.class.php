<?php

namespace Modules\User\AdminAction;

/**
 * 
 */
class Login extends \AdminAction
{
	public function execute()
	{
	
        $apx = $this->adminModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object("user");
        
        //Weiterleiten auf Startseite, wenn angemeldet
        if ( $user->info['userid']??0 ) 
        {
            header('Location: index.php');
        }
    
        if ( $apx->param()->postIf('send') ) 
        {
            
            if ( $apx->param()->postIf('login_user') && $apx->param()->postIf('login_pwd') ) 
            {
                $stmt = $db->prepare("
                    SELECT 
                        userid,password,salt,active,gtype 
                    FROM 
                        ".PRE."_user AS a 
                    LEFT JOIN 
                        ".PRE."_user_groups AS b USING(groupid) 
                    WHERE 
                        LOWER(username_login) = ?
                    LIMIT 1
                ");
                $stmt->bind_param( "s", strtolower( $apx->param()->postString("login_user") ) );
                $stmt->execute();
                $res = $stmt->get_result()->fetch_assoc();
                unset($stmt);
    
                list($count) = $db->first("SELECT count(time) FROM ".PRE."_loginfailed WHERE ( userid='".$res['userid']."' AND time>='".(time()-15*60)."' )");
                
                if ( !checkToken() ) 
                {
                    infoInvalidToken();
                }
                elseif ( $count>=5 ) 
                {
                    printInfo($apx->lang->get('INFO_BLOCK'));
                }
                elseif ( !$res['userid'] || $res['password']!=md5(md5($_POST['login_pwd']).$res['salt']) ) 
                {
                    if ( $res['userid'] )
                    {
                        $db->query("INSERT INTO ".PRE."_loginfailed VALUES ('".$res['userid']."','".time()."')");
                    }
                    if ( $count==4 )
                    {
                        printInfo($apx->lang->get('INFO_BLOCK'));
                    }
                    else 
                    {
                        printInfo($apx->lang->get('INFO_FAIL'));
                    }
                }
                elseif ( $res['gtype']!='admin' && $res['gtype']!='indiv' ) 
                {
                    printInfo($apx->lang->get('INFO_NOGROUP'));
                }
                elseif ( !$res['active'] ) 
                {
                    printInfo($apx->lang->get('INFO_BANNED'));
                }
                else 
                {
                    $apx->session()->set($apx->config('main')['cookie_pre'] . '_userid', $res['userid']);
                    $apx->session()->set('apxses_password', $res['password']);
                    
                    $timeout=(int)$_POST['cookie_time'];
                    if ( $timeout<=0 ) $timeout = 100;
                    
                    // delete fails logins
                    $db->query("DELETE FROM ".PRE."_loginfailed WHERE userid='".$res['userid']."'");
                    
                    $apx->user->info['userid'] = $res['userid']; //Für Log
                    logit('USER_LOGIN');
                    
                    printJSRedirect('index.php');
                }
            }
            else 
            {
                printInfo($apx->lang->get('CORE_BACK'));
            }
            return;
        }
        else 
        {
            $apx->tmpl->loaddesign('blank');
            $apx->tmpl->parse('login');
        }        
    }
}