<?php

namespace Modules\User\AdminAction;

/**
 * 
 */
class Enable extends \AdminAction
{
    public function execute()
    {
        global $set, $apx, $tmpl, $db;
        $_REQUEST['id'] = (int)$_REQUEST['id'];
        if (
            !$_REQUEST['id']
        ) die('missing ID!');

        if (
            !checkToken()
        ) printInvalidToken();
        else
        {
            $res = $db->first("SELECT username,reg_key,email FROM " . PRE . "_user WHERE userid='" . $_REQUEST['id'] . "' LIMIT 1");
            if ($res['reg_key'] != 'BYADMIN') die('can not activate user!');

            $db->query("UPDATE " . PRE . "_user SET reg_key='' WHERE ( userid='" . $_REQUEST['id'] . "' AND reg_key='BYADMIN' ) LIMIT 1");
            logit('USER_ENABLE', 'ID #' . $_REQUEST['id']);

            $input = array();
            $input['USERNAME'] = replace($res['username']);
            $input['WEBSITE'] = $set['main']['websitename'];
            $input['URL'] = HTTP_HOST . mklink('user.php', 'user.html');
            sendmail($res['email'], 'ACTIVATION', $input);

            header("HTTP/1.1 301 Moved Permanently");
            header('Location: ' . get_index('user.show'));
        }
    }
}
