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


define('APXRUN',true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('includes/_start.php');  /////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

$user = $apx->get_registered_object("user");

if ( $user->info['userid'] ) 
{
	$apx->tmpl->loaddesign('default');

	//Sektionen
	$selsec = $apx->session()->get('section');
	$secdata = array();
	foreach ( $apx->sections AS $id => $section ) {
		$secdata[] = array(
			'ID' => $id,
			'TITLE' => compatible_hsc($section['title']),
			'SELECTED' => ($selsec==$id)
		);
	}
	$apx->tmpl->assign('SECTION', $secdata);

	//Navigation
	$apx->tmpl->assign_static('NAVI',$html->navi());	

	
	$module = "main.index";
	if( $apx->param()->requestIf("action") )
	{
		$module = $apx->param()->requestString("action");
	}
	$module = explode(".", $module);
	if( count( $module ) != 2 )
		throw new ApexxError("Action must contain a dot!");
	$action = $module[1];
	$module = $module[0];

	$apx->executeModule($module, $action);
}
else 
{	
	$apx->executeModule("user", "login");
	exit;
}


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('includes/_end.php');  ////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>