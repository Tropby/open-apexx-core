<?php

namespace Modules\User\PublicAction;

class Subscribe extends \PublicAction
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

		//Forum-Modul muss aktiv sein
		if (!$apx->is_module('forum'))
		{
			filenotfound();
			return;
		}

		if (in_array($_REQUEST['option'], array('addforum', 'addthread')))
		{
			(new Subscribe_Add($this->publicModule()))->execute();
		}
		elseif ($_REQUEST['option'] == 'edit')
		{
			(new Subscribe_Edit($this->publicModule()))->execute();
		}
		elseif ($_REQUEST['option'] == 'delete')
		{ 
			(new Subscribe_Del($this->publicModule()))->execute();
		}
		else
		{
			filenotfound();
		}
	}
}
