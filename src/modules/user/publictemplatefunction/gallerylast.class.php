<?php

namespace Modules\User\PublicTemplateFunction;

class GalleryLast extends \PublicTemplateFunction
{
    public function execute($count = 5, $start = 0, $friendsonly = false, $userid = 0, $template = 'gallery_last')
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');

        $count = (int)$count;
        $start = (int)$start;

        //Nach Freunden filtern
        $friendfilter = '';
        if ($friendsonly)
        {
            $friends = $user->get_buddies();
            $friends[] = -1;
            $friendfilter = " WHERE userid IN (" . implode(',', $friends) . ") ";
        }

        //Nach Benutzer filtern
        $userfilter = '';
        if ($userid)
        {
            $userfilter = " AND owner='" . $userid . "'";
        }

        $data = $db->fetch("SELECT * FROM " . PRE . "_user_gallery WHERE password='' " . $userfilter . $friendfilter . " ORDER BY addtime DESC LIMIT " . iif($start, $start . ',') . $count);

        $this->publicModule()->call('galleryPrint', array($data, 'functions/' . $template));
    }

}
