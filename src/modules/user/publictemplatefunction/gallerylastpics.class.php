<?php

namespace Modules\User\PublicTemplateFunction;

use PublicModule;

class GalleryLastPics extends \PublicTemplateFunction
{
    public function execute($count = 5, $start = 0, $galid = false, $friendsonly = false, $userid = 0, $template = 'gallery_lastpics')
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');

        $count = (int)$count;
        $start = (int)$start;
        $galid = (int)$galid;
        $userid = (int)$userid;

        //Nach Freunden filtern
        $friendfilter = '';
        if (
            $friendsonly
        )
        {
            $friends = $user->get_buddies();
            $friends[] = -1;
            $friendfilter = " AND g.owner IN (" . implode(',', $friends) . ") ";
        }

        //Nach Benutzer filtern
        $userfilter = '';
        if (
            $userid
        )
        {
            $userfilter = " AND g.owner='" . $userid . "'";
        }

        $data = $db->fetch("SELECT g.owner,g.title,g.description,g.password,g.addtime AS galaddtime,g.lastupdate,p.* FROM " . PRE . "_user_pictures AS p LEFT JOIN " . PRE . "_user_gallery AS g ON p.galid=g.id WHERE g.password='' " . $userfilter . $friendfilter . " " . iif($galid, " AND p.galid='" . $galid . "'") . " ORDER BY p.addtime DESC LIMIT " . iif($start, $start . ',') . $count);
        $this->publicModule()->call('galleryPrintPic', array($data, 'functions/' . $template));
    }
}
