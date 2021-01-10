<?php

namespace Modules\User\PublicTemplateFunction;

class GalleryPOTM extends \PublicTemplateFunction
{
    public function execute($galid = 0, $friendsonly = false, $userid = 0, $template = 'gallery_potm')
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');        

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

        //Zufallsauswahl
        $res = $db->first("SELECT g.owner,g.title,g.description,g.password,g.addtime AS galaddtime,g.lastupdate,p.* FROM " . PRE . "_user_pictures AS p LEFT JOIN " . PRE . "_user_gallery AS g ON p.galid=g.id WHERE g.password='' " . $userfilter . $friendfilter . " " . iif($galid, " AND p.galid='" . $galid . "'") . " ORDER BY RAND() LIMIT 1");
        $this->publicModule()->userGalleryPrintsingle($res, 'functions/' . $template);
    }
}
