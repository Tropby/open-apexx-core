<?php

/*
	Open Apexx Core
	(c) Copyright 2005-2009, Christian Scheb
	(c) Copyright 2020 Carsten Grings

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 2.1 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace Modules\User;

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');

class AdminModule extends \AdminModule
{
    public function __construct(\Module &$module)
    {
        parent::__construct($module);

        /**
         * Register all actions that can be executed in admin scope
         */
        $this->registerAction("login");

		$this->registerAction('login', 0, 0, 1, 1);
		$this->registerAction('logout', 0, 0, 2, 1);
		$this->registerAction('autologout', 0, 0, 3, 1);

		$this->registerAction('show', 0, 1, 4, 0);
		$this->registerAction('add', 0, 1, 5, 0);
		$this->registerAction('edit', 0, 0, 6, 0);
		$this->registerAction('del', 0, 0, 7, 0);
		$this->registerAction('enable', 0, 0, 8, 0);

		$this->registerTemplateFunction('User', 'USER');
		$this->registerTemplateFunction('UserGroups', 'USERGROUPS');

    }
}
