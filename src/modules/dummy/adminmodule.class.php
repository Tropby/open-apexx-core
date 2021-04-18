<?php

/*
	Open Apexx Core
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

namespace Modules\Dummy;

class AdminModule extends \Module
{   
    public function __construct(\apexx $apx, string $modulename)
    {                
        // Copy the old init.php data in the dummy module
        require(BASEDIR . $apx->path()->getmodulepath($modulename) . 'init.php');
        parent::__construct($apx, $module["id"], $module["dependence"], $module["requirement"], $module["version"], $module["author"], $module["contact"]);

        foreach( $action as $k => $v )
        {
            $this->register_admin_action($k, $v[0], $v[1], $v[2], $v[3]);
        }

        foreach ($afunc??array() as $k => $v)
        {
            $this->register_admin_template_function($k, $v[0], $v[1], $v[2]??"", $v[3]??array());
        }

        foreach ($func ?? array() as $k => $v)
        {
            $this->register_template_function($k, $v[0], $v[1], $v[2]??"", $v[3]??array());
        }

        unset($module, $action, $func, $afunc);
    }

    public function init()
    {
        global $apx, $user, $set, $db;
        if (file_exists(BASEDIR . $this->apx->path()->getmodulepath($this->id()) . 'admin_system.php'))
            require_once(BASEDIR . $this->apx->path()->getmodulepath($this->id()) . 'admin_system.php');
    }

    public function startup()
    {
        global $apx, $user, $set, $db;
        if (file_exists(BASEDIR . $this->apx->path()->getmodulepath($this->id()) . 'startup.php'))
            require_once(BASEDIR . $this->apx->path()->getmodulepath($this->id()) . 'startup.php');
    }

    public function execute()
    {
        echo "You can not execute an old style module like this!";
        // Do Nothing
    }

    public function shutdown()
    {
        global $apx, $user, $set, $db;

        if( file_exists(BASEDIR . $this->apx->path()->getmodulepath($this->id()) . 'shutdown.php'))
            require_once(BASEDIR . $this->apx->path()->getmodulepath($this->id()) . 'shutdown.php');
    }
}

?>