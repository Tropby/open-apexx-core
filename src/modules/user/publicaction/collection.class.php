<?php

namespace Modules\User\PublicAction;

class Collection extends \PublicAction
{
	public function execute()
	{
		$apx = $this->publicModule()->module()->apx();
		$db = $apx->db();
		$user = $apx->get_registered_object('user');

		//Produkt-Modul wird benötigt
		if (!$apx->is_module('products'))
		{
			filenotfound();
			return;
		}

		//Include von Produkt-Modul
		require(BASEDIR . getmodulepath('products') . 'pub/collection.php');
	}
}
