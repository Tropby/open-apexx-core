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

define('APXRUN', true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

if( !isset($set['installed']) || !$set['installed'] )
{
	$setup = new \setup\Setup($apx);
	$setup->execute();
	$apx->shutdown();
	exit;
}

/**
 * Standard Module to execute
 * @var string
 */
$module = "user";

/**
 * Standard Action to execute
 * @var string
 */
$action = "login";

if ($apx->param()->getIf('module'))
    $module = $apx->param()->getString('module');

if( $apx->param()->getIf('action'))
    $action = $apx->param()->getString('action');

$apx->execute_module($module, $action);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////