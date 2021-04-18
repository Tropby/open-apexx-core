<?php

namespace Modules\User\AdminAction;

/**
 * 
 */
class Del extends \AdminAction
{
    public function execute()
    {
        global $set, $apx, $tmpl, $db;
        $_REQUEST['id'] = (int)$_REQUEST['id'];
        if (!$_REQUEST['id']) die('missing ID!');
        if ($_REQUEST['id'] == $apx->user->info['userid']) die('can not delete yourself!');

        if (
            $_POST['send'] == 1
        )
        {
            if (!checkToken()) infoInvalidToken();
            else
            {
                $db->query("DELETE FROM " . PRE . "_user WHERE userid='" . $_REQUEST['id'] . "' LIMIT 1");
                if ($db->affected_rows())
                {
                    $db->query("DELETE FROM " . PRE . "_user_guestbook WHERE owner='" . $_REQUEST['id'] . "'");
                    $db->query("DELETE FROM " . PRE . "_user_blog WHERE userid='" . $_REQUEST['id'] . "'");
                    $db->query("DELETE FROM " . PRE . "_user_bookmarks WHERE userid='" . $_REQUEST['id'] . "'");
                    $db->query("DELETE FROM " . PRE . "_user_friends WHERE userid='" . $_REQUEST['id'] . "' OR friendid='" . $_REQUEST['id'] . "'");
                    $db->query("DELETE FROM " . PRE . "_user_visits WHERE userid='" . $_REQUEST['id'] . "'");
                    $db->query("DELETE FROM " . PRE . "_user_pms WHERE fromuser='" . $_REQUEST['id'] . "' OR touser='" . $_REQUEST['id'] . "'");
                    $db->query("DELETE FROM " . PRE . "_user_ignore WHERE userid='" . $_REQUEST['id'] . "' OR ignored='" . $_REQUEST['id'] . "'");

                    //Galerie löschen
                    $data = $db->fetch("SELECT id FROM " . PRE . "_user_gallery WHERE owner='" . $_REQUEST['id'] . "'");
                    $galids = get_ids($data);
                    if (
                        count($galids)
                    )
                    {
                        require_once(BASEDIR . 'lib/class.mediamanager.php');
                        $mm = new \mediamanager();

                        //Bilder löschen
                        $data = $db->fetch("SELECT thumbnail,picture FROM " . PRE . "_user_pictures WHERE galid IN (" . implode(',', $galids) . ")");
                        $db->query("DELETE FROM " . PRE . "_user_pictures WHERE galid IN (" . implode(',', $galids) . ")");
                        if (
                            count($data)
                        )
                        {
                            foreach ($data as $res)
                            {
                                $picture = $res['picture'];
                                $thumbnail = $res['thumbnail'];
                                if ($picture && file_exists(BASEDIR . getpath('uploads') . $picture)) $mm->deletefile($picture);
                                if ($thumbnail && file_exists(BASEDIR . getpath('uploads') . $thumbnail)) $mm->deletefile($thumbnail);
                            }
                        }

                        //Galerie-Ordner löschen
                        foreach ($galids as $gid)
                        {
                            $mm->deletedir('user/gallery-' . $gid);
                        }
                    }
                }
                logit('USER_DEL', 'ID #' . $_REQUEST['id']);
                printJSReload();
            }
        }
        else
        {
            list($title) = $db->first("SELECT username FROM " . PRE . "_user WHERE userid='" . $_REQUEST['id'] . "' LIMIT 1");
            $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
            $input['ID'] = $_REQUEST['id'];
            tmessageOverlay('deltitle', $input, '/');
        }
    }
}
