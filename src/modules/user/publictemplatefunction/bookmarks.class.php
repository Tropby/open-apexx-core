<?php

namespace Modules\User\PublicTemplateFunction;

class Bookmarks extends \PublicTemplateFunction
{
    public function execute($template = 'bookmarks')
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');
        
        $tmpl = new \tengine($apx);
        $apx->lang->drop('func_bookmarks', 'user');

        $tabledata = array();
        $data = $db->fetch("SELECT * FROM " . PRE . "_user_bookmarks WHERE userid='" . $user->info['userid'] . "' ORDER BY title ASC");
        if (
            count($data)
        )
        {
            $i = 0;
            foreach ($data as $res)
            {
                ++$i;

                //Bookmark löschen
                $dellink = mklink(
                    'user.php?action=delbookmark&amp;id=' . $res['id'],
                    'user,delbookmark,' . $res['id'] . '.html'
                );

                $tabledata[$i]['ID'] = $res['id'];
                $tabledata[$i]['TITLE'] = $res['title'];
                $tabledata[$i]['LINK'] = $res['url'];
                $tabledata[$i]['TIME'] = $res['addtime'];
                $tabledata[$i]['LINK_DELBOOKMARK'] = $dellink;
            }
        }

        $tmpl->assign('BOOKMARK', $tabledata);
        $tmpl->parse('functions/' . $template, 'user');
    }
}
