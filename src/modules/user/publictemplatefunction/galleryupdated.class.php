<?php

namespace Modules\User\PublicTemplateFunction;

class GalleryUpdated extends \PublicTemplateFunction
{
    public function execute($count = 5, $start = 0, $friendsonly = false, $userid = 0, $template = 'gallery_updated')
    {
        global $set, $db, $apx, $user;
        $count = (int)$count;
        $start = (int)$start;
        $userid = (int)$userid;

        //Nach Freunden filtern
        $friendfilter = '';
        if (
            $friendsonly
        )
        {
            $friends = $user->get_buddies();
            $friends[] = -1;
            $friendfilter = " AND owner IN (" . implode(',', $friends) . ") ";
        }

        //Nach Benutzer filtern
        $userfilter = '';
        if (
            $userid
        )
        {
            $userfilter = " AND owner='" . $userid . "'";
        }

        $data = $db->fetch("SELECT * FROM " . PRE . "_user_gallery WHERE password='' " . $userfilter . $friendfilter . " ORDER BY lastupdate DESC LIMIT " . iif($start, $start . ',') . $count);
        $this->publicModule()->call('galleryPrint', array($data, 'functions/' . $template));
    }
}
