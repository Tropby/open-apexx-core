<?php

namespace Modules\User\PublicTemplateFunction;

class BookmarkLink extends \PublicTemplateFunction
{
    public function execute()
    {        
        $escurl = urlencode(HTTP_HOST . $_SERVER['REQUEST_URI']);
        $link = mklink(
            'user.php?action=addbookmark&amp;url=' . $escurl,
            'user,addbookmark.html?url=' . $escurl
        );
        echo $link;
    }
}
