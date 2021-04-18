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


define('APXRUN', true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('includes/_start.php');  /////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////


//CKEditor-Funcnum
if ($apx->param()->getIf('CKEditorFuncNum'))
{
	$apx->session->set('CKEditorFuncNum', $apx->param()->getInt('CKEditorFuncNum'));
}

if ($apx->user->info['userid'])
{
	$apx->tmpl->assign_static("HIDE_MENU", 1);

	$action = "index";
	if( $apx->param()->getif("action") )
	{
		$action = explode( ".", $apx->param()->getString("action") )[1];
	}

	//$apx->tmpl->loaddesign('blank');
	$apx->tmpl->assign('NAVI', $html->mm_navi());
	//$apx->tmpl->parse('mediamanager_navi', '/');

	$apx->executeModule("mediamanager", $action);
	//$apx->executeAction();
	//$apx->tmpl->loaddesign('blank');
	//$apx->tmpl->parse('mediamanager', '/');
}
else
{
	header('Location: action.php?action=user.login');
	exit;
}


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('includes/_end.php');  ////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
