<?php

namespace Modules\User\PublicTemplateFunction;

class Stats extends \PublicTemplateFunction
{
    public function execute($template = 'stats'): void
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $tmpl = new \tengine($apx);

        $parse = $tmpl->used_vars('functions/' . $template, 'user');

        $apx->lang->drop('func_stats', 'user');

        //User
        if (
            in_array('COUNT_USERS', $parse)
        )
        {
            list($count) = $db->first("
			SELECT count(userid) FROM " . PRE . "_user
			WHERE active='1' " . iif($apx->config('user')['listactiveonly'], " AND reg_key='' ") . "
		");
            $tmpl->assign('COUNT_USERS', $count);
        }
        if (
            in_array('COUNT_USERS_MALE', $parse)
        )
        {
            list($count) = $db->first("
			SELECT count(userid) FROM " . PRE . "_user
			WHERE active='1' AND gender=1 " . iif($apx->config('user')['listactiveonly'], " AND reg_key='' ") . "
		");
            $tmpl->assign('COUNT_USERS_MALE', $count);
        }
        if (
            in_array('COUNT_USERS_FEMALE', $parse)
        )
        {
            list($count) = $db->first("
			SELECT count(userid) FROM " . PRE . "_user
			WHERE active='1' AND gender=2 " . iif($apx->config('user')['listactiveonly'], " AND reg_key='' ") . "
		");
            $tmpl->assign('COUNT_USERS_FEMALE', $count);
        }

        //Blogs
        if (
            in_array('COUNT_BLOGS', $parse)
        )
        {
            list($count) = $db->first("
			SELECT count(id) FROM " . PRE . "_user_blog
		");
            $tmpl->assign('COUNT_BLOGS', $count);
        }

        //Galerien
        if (
            in_array('COUNT_GALLERIES', $parse)
        )
        {
            list($count) = $db->first("
			SELECT count(id) FROM " . PRE . "_user_gallery
		");
            $tmpl->assign('COUNT_GALLERIES', $count);
        }
        if (
            in_array('COUNT_PICTURES', $parse)
        )
        {
            list($count) = $db->first("
			SELECT count(id) FROM " . PRE . "_user_pictures
		");
            $tmpl->assign('COUNT_PICTURES', $count);
        }


        $tmpl->parse('functions/' . $template, 'user');
    }
}

?>