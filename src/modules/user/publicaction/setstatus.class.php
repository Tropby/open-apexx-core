<?php

namespace Modules\User\PublicAction;

class SetStatus extends \PublicAction
{
	public function execute()
	{
		$apx = $this->publicModule()->module()->apx();
		$db = $apx->db();
		$user = $apx->get_registered_object('user');

		if (!isset($user->info['userid']) || !$user->info['userid'])
		{
			(new Login($this->publicModule()))->execute();
			return;
		}

		if (isset($_POST['status']))
		{
			$db->query("UPDATE " . PRE . "_user SET status='" . addslashes($_POST['status']) . "', status_smiley='" . addslashes($_POST['status_smiley']) . "' WHERE userid='" . $user->info['userid'] . "' LIMIT 1");
		}

		$link = str_replace('&amp;', '&', $user->mkprofile($user->info['userid'], $user->info['username']));
		header('Location: ' . $link);
	}
}
