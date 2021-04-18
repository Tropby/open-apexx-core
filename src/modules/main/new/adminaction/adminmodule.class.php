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
namespace Modules\Main;

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
        $this->registerAction("index");
        $this->registerAction("mshow");
        $this->registerAction("get_modinfo");
        $this->registerAction("regmod_readout");
        $this->registerAction("mshow_refresh");
        $this->registerAction("menable");
        $this->registerAction("mdisable");
        $this->registerAction("minstall");
        $this->registerAction("muninstall");
        $this->registerAction("mupdate");
        $this->registerAction("mconfig");
        $this->registerAction("secshow");
        $this->registerAction("secshow_add");
        $this->registerAction("secshow_edit");
        $this->registerAction("secshow_del");
        $this->registerAction("secshow_default");
        $this->registerAction("lshow");
        $this->registerAction("lshow_add");
        $this->registerAction("lshow_del");
        $this->registerAction("lshow_default");
        $this->registerAction("tshow");
        $this->registerAction("tshow_add");
        $this->registerAction("tshow_edit");
        $this->registerAction("tshow_del");
        $this->registerAction("snippets");
        $this->registerAction("snippets_add");
        $this->registerAction("snippets_edit");
        $this->registerAction("snippets_del");
        $this->registerAction("tags");
        $this->registerAction("tags_edit");
        $this->registerAction("tags_del");
        $this->registerAction("sshow");
        $this->registerAction("sshow_add");
        $this->registerAction("sshow_edit");
        $this->registerAction("sshow_del");
        $this->registerAction("cshow");
        $this->registerAction("cshow_add");
        $this->registerAction("cshow_edit");
        $this->registerAction("cshow_del");
        $this->registerAction("bshow");
        $this->registerAction("bshow_add");
        $this->registerAction("bshow_edit");
        $this->registerAction("bshow_del");
        $this->registerAction("log");
        $this->registerAction("log_get");
        $this->registerAction("log_clean");
        $this->registerAction("log_download");
        $this->registerAction("env");
        $this->registerAction("searches");
        $this->registerAction("close");
        $this->registerAction("delcache");
    }
}
