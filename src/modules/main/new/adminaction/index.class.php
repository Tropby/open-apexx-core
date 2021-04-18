<?php

namespace Modules\main\AdminAction;

/**
 * 
 */
class Index extends \AdminAction
{
	public function execute()
	{
        $apx = $this->adminModule()->module()->apx();
        $db = $apx->db();

        $user = $apx->get_registered_object("user");

		//Ist der Nutzer angemeldet?
		if (!$user->info['userid'])
		{
			header('Location: action.php?action=user.login');
			return;
		}

		//Online-Liste
		list($online) = $db->first("SELECT count(userid) FROM " . PRE . "_user LEFT JOIN " . PRE . "_user_groups USING(groupid) WHERE ( gtype IN ('admin','indiv') AND lastactive>='" . (time() - $user->timeout * 60) . "' )");
		$data = $db->fetch("SELECT username FROM " . PRE . "_user LEFT JOIN " . PRE . "_user_groups USING(groupid) WHERE ( gtype IN ('admin','indiv') AND lastactive>='" . (time() - $user->timeout * 60) . "' ) ORDER BY username ASC");
		foreach ($data as $res)
		{
			$usernames[] = $res['username'];
		}
		$apx->tmpl->assign('ONLINE_COUNT', $online);
		$apx->tmpl->assign('ONLINE', implode(', ', $usernames));

		//Benutzer-Informationen
		list($groupname) = $db->first("SELECT name FROM " . PRE . "_user_groups WHERE groupid='" . $user->info['groupid'] . "' LIMIT 1");

		$apx->tmpl->assign('USERID', $user->info['userid']);
		$apx->tmpl->assign('USERNAME_LOGIN', replace($user->info['username_login']));
		$apx->tmpl->assign('USERNAME', replace($user->info['username']));
		$apx->tmpl->assign('EMAIL', replace($user->info['email']));
		$apx->tmpl->assign('GROUP', replace($groupname));
		$apx->tmpl->assign('SESSION', mkdate($user->info['lastonline']));

		$apx->tmpl->assign('VERSION', VERSION);
		$apx->tmpl->assign('MODULES', count($apx->modules));

		$apx->tmpl->parse('index');	
	}
}
