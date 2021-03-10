<?php

namespace Modules\User\PublicAction;

/**
 * This Action will activate a account with the key provided by E-Mail
 */
class Activate extends \PublicAction
{
	public function execute()
	{
		$apx = $this->publicModule()->module()->apx();
		$db = $apx->db();
		$user = $apx->get_registered_object('user');

		if ($user->id()) {
			(new Index($this->publicModule()))->execute();
			return;
		}

		if ($apx->config('user')['useractivation'] != 3) exit;

		$apx->lang->drop('activate');

		if (!$apx->param()->requestIf('userid') || !$apx->param()->requestIf('key')) {
			$apx->message('back');
			require('lib/_end.php');
		}

		$userId = $apx->param()->requestInt('userid');
		$key = $apx->param()->requestString('key');

		$stmt = $db->prepare("SELECT userid, reg_key FROM " . PRE . "_user WHERE userid=? LIMIT 1");
		$stmt->bind_param("i", $userId);
		$stmt->execute();

		$res = $stmt->get_result()->fetch_assoc();

		if ($res['userid'] && !$res['reg_key']) {
			$apx->message($apx->lang->get('MSG_ISACTIVE'), mklink('user.php', 'user.html'));
		} elseif ($res['reg_key'] == $key) {
			$stmt = $db->prepare("UPDATE " . PRE . "_user SET reg_key='' WHERE userid=? LIMIT 1");
			$stmt->bind_param("i", $userId);
			$stmt->execute();
			$apx->message($apx->lang->get('MSG_OK'), mklink('user.php', 'user.html'));
		} else {
			$apx->message($apx->lang->get('MSG_WRONGKEY'));
		}
	}
}
