<?php

namespace Modules\User\PublicAction;

class Signature extends \PublicAction
{
	public function execute()
	{
		$apx = $this->publicModule()->module()->apx();		
		$db = $apx->db();
		/**
		 * @var \Modules\User\User 
		 */
		$user = $apx->get_registered_object('user');

		// Show the login 
		if ( !$user->id() )
		{
			(new Login($this->publicModule()))->execute();
			return;
		}
		
		// Show the Signature editor
		$apx->lang->drop('signature');
		$apx->headline($apx->lang->get('HEADLINE_SIGNATURE'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
		$apx->titlebar($apx->lang->get('HEADLINE_SIGNATURE'));

		$signature = "";
		if( $apx->param()->postIf('signature') )
			$signature = $apx->param()->postString('signature');

		if (!$apx->param()->postIf('preview') && $apx->param()->postIf('send'))
		{
			// signature to long?
			if (strlen($signature) > $apx->config('user')['sigmaxlen']) 
			{
				message($apx->lang->get('MSG_SIGTOOLONG'), 'javascript:history.back()');
			}
			else
			{
				// Save signature
				$stmt = $db->prepare("UPDATE ".PRE."_user SET signature=? WHERE userid=? LIMIT 1");
				$stmt->bind_param("si", $signature, $user->id());
				$stmt->execute();

				message($apx->lang->get('MSG_OK'), mklink('user.php', 'user.html'));
			}
		}
		else
		{
			// If not preview load signature from user
			if (!$apx->param()->postIf('signature')) 
			{
				$signature = $user->signature();
			}

			// Show preview
			if ($apx->param()->postIf('preview')) 
			{
				$apx->tmpl->assign('PREVIEW', $user->mksig(["signature" => $signature], 1));
			}

			// Output data
			$apx->tmpl->assign('SIGNATURE', compatible_hsc($signature));
			$apx->tmpl->assign('MAXLEN', $apx->config('user')['sigmaxlen']);

			$postto = mklink(
				'user.php?action=signature',
				'user,signature.html'
			);

			$apx->tmpl->assign('POSTTO', $postto);
			$apx->tmpl->parse('signature');
		}
	}
}
