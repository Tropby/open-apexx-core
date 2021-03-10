<?php 

/*
	Open Apexx Core
	(c) Copyright 2020 Carsten Grings
	(c) Copyright 2005-2009, Christian Scheb

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
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

//$apx->execute_module('main', 'search');

$apx->module('main');
$apx->lang->drop('search');

headline($apx->lang->get('HEADLINE'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE'));

$parse = $apx->tmpl->used_vars('search');


////////////////////////////////////////////////////////////////////////////////////// VOREINSTELLUNGEN

if ( !$_POST['conn'] ) $_POST['conn']='and';

if ( !isset($_POST['searchin']) || ( !is_array($_POST['searchin']) && $_POST['searchin']=='all' ) ) {
	$_POST['searchin']=array();
	foreach ( $apx->modules AS $module => $trash ) {
		$_POST['searchin'][$module]='1';
	}
}
elseif ( !is_array($_POST['searchin']) ) {
	if ( $apx->is_module($_POST['searchin']) ) {
		$array[$_POST['searchin']]='1';
		$_POST['searchin']=$array;
	}
}

if ( !isset($_POST['item']) && isset($_GET['q']) ) {
	$_POST['item'] = $_GET['q'];
	$_POST['send'] = 1;
}


////////////////////////////////////////////////////////////////////////////////////// SUCHE

if ( $_POST['send'] ) {
	if ( !$_POST['item'] || ( !is_array($_POST['searchin']) ) ) {
		message('back');
		require('lib/_end.php');
	}
	
	//RegExp erstellen
	$items=array();
	$items = explode(' ',preg_replace('#\s{2,}#',' ',trim($_POST['item'])));
	$items = array_map('strtolower', $items);
	$items = array_unique($items);
	
	//Verbindungswort
	if ( $_POST['conn']=='or' ) $conn=' OR ';
	else $conn=' AND ';
	
	//Suche beginnen
	if ( count($items) ) {
		
		//Suchbegriffe aufzeichnen
		$db->query("
			INSERT INTO ".PRE."_search_item
			VALUES ('".addslashes(implode(' ', $items))."', '".time()."')
		");
		
		foreach ( $_POST['searchin'] AS $module => $value ) {
			if ( !file_exists(BASEDIR.getmodulepath($module).'search.php') ) {
				continue;
			}
			include_once(BASEDIR.getmodulepath($module).'search.php');
			$functionname='search_'.$module;
			
			if ( $value!='1' ) continue;
			if ( !function_exists($functionname) ) continue;
			if ( isset($set[$module]['searchable']) && !$set[$module]['searchable'] ) continue;
			
			++$i;
			$apx->lang->drop('func_search',$module);
			$resdata[$i]['TITLE']=$apx->lang->get('SEARCH_'.strtoupper($module));
			$resdata[$i]['RESULT']=$functionname($items,$conn);
		}
	}
}



////////////////////////////////////////////////////////////////////////////////////// EINGABEFELD

//Module, in denen Suche m�glich ist
foreach ( $apx->modules AS $module => $info ) {
	if ( file_exists(BASEDIR.getmodulepath($module).'search.php') ) {
		include_once(BASEDIR.getmodulepath($module).'search.php');
	}
	else {
		continue;
	}
	if ( !function_exists('search_'.$module) ) continue;
	if ( isset($set[$module]['searchable']) && !$set[$module]['searchable'] ) continue;
	
	$apx->lang->drop('func_search',$module);
	
	++$i;
	$posdata[$i]['ID']=$module;
	$posdata[$i]['TITLE']=$apx->lang->get('SEARCH_'.strtoupper($module));
	$posdata[$i]['CHECKED']=iif($_POST['searchin'][$module]=='1',1,0);
}

$postto=mklink(
	'search.php',
	'search.html'
);


//Letzte Suchen auslesen
$lastdata = array();
if ( in_array('LASTSEARCH', $parse) ) {
	$data = $db->fetch("
		SELECT item, max(time) AS time, count(item) AS weight
		FROM ".PRE."_search_item
		GROUP BY item
		ORDER BY time DESC
		LIMIT 10
	");
	
	$maxWeight = 1;
	foreach ( $data AS $res ) {
		$maxWeight = max($maxWeight, $res['weight']);
	}
	
	foreach ( $data AS $res ) {
		$lastdata[] = array(
			'ITEM' => compatible_hsc($res['item']),
			'WEIGHT' => $res['weight']/$maxWeight,
			'LINK' => mklink(
				'search.php?q='.urlencode($res['item']),
				'search.html?q='.urlencode($res['item'])
			)
		);
	}
}

$apx->tmpl->assign('POSTTO',$postto);
$apx->tmpl->assign('ITEM',compatible_hsc($_POST['item']));
$apx->tmpl->assign('MODULE',$resdata);
$apx->tmpl->assign('LASTSEARCH',$lastdata);
$apx->tmpl->assign('POSSIBLE',$posdata);
$apx->tmpl->assign('CONN',iif($_POST['conn']=='and' || $_POST['conn']=='or',$_POST['conn'],'and'));

$apx->tmpl->parse('search');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>