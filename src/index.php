<?php 

/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|           (c) Copyright 2005-2009, Christian Scheb            |
|                  http://www.stylemotion.de                    |
|                                                               |
|---------------------------------------------------------------|
| THIS SOFTWARE IS NOT FREE! MAKE SURE YOU OWN A VALID LICENSE! |
| DO NOT REMOVE ANY COPYRIGHTS WITHOUT PERMISSION!              |
| SOFTWARE BELONGS TO ITS AUTHORS!                              |
\***************************************************************/


define('APXRUN',true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

if ( $apx->config('main')['index_forwarder'] ) {
	header('location:'.$set['main']['index_forwarder']);
	exit;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////

if( $apx->param()->requestIf('module') )
{
	$action = "";
	if($apx->param()->requestIf('action'))
		$action = $apx->param()->requestString('action');
	$apx->execute_module($apx->param()->requestString('module'), $action );
}
else
{
	$apx->module('main');
	$apx->lang->drop('index');
	$apx->headline($apx->lang->get('HEADLINE'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
	$apx->titlebar($apx->lang->get('HEADLINE'));
	$apx->tmpl->parse('index', '/');
}

////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>