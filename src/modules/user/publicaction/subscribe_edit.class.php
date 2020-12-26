<?php

namespace Modules\User\PublicAction;

class Subscribe_Edit extends \PublicAction
{
	public function execute()
	{
		$apx = $this->publicModule()->module()->apx();
		$db = $apx->db();
		$user = $apx->get_registered_object('user');

		$apx->module('forum'); //Diese Aktion gehört dem Forum
		$_REQUEST['id'] = (int)$_REQUEST['id'];
		if (!$_REQUEST['id']) die('missing ID!');
		$apx->lang->drop('subscribe');
		$subinfo = $db->first("SELECT type,notification FROM " . PRE . "_forum_subscriptions WHERE id='" . $_REQUEST['id'] . "' AND userid='" . $user->info['userid'] . "' LIMIT 1");

		if ($_POST['send'])
		{

			//Benachrichtigung
			if ($subinfo['type'] == 'thread' && !in_array($_POST['subscription'], array('none', 'instant', 'daily', 'weekly'))) die('invalid notification type');
			if ($subinfo['type'] == 'forum' && !in_array($_POST['subscription'], array('none', 'daily', 'weekly'))) die('invalid notification type');

			$db->query("UPDATE " . PRE . "_forum_subscriptions SET notification='" . $_POST['subscription'] . "' WHERE id='" . $_REQUEST['id'] . "' AND userid='" . $user->info['userid'] . "' LIMIT 1");
			message($apx->lang->get('MSG_SUBEDIT_OK'), mklink('user.php?action=subscriptions', 'user,subscriptions.html'));
		}
		else
		{
			$input = array(
				'ID' => $_REQUEST['id'],
				'SUBSCRIPTION' => $subinfo['notification'],
				'ISTHREAD' => iif($subinfo['type'] == 'thread', 1, 0)
			);
			tmessage('subscription_edit', $input);
		}
	}
}
