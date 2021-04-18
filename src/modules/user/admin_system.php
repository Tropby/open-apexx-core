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


//Security-Check
if (!defined('APXRUN')) die('You are not allowed to execute this file directly!');


//Liste der Teammitglieder ausgeben
function user_team($selected = 0)
{
	global $set, $apx, $db;
	$selected = (int)$selected;

	$data = $db->fetch("SELECT a.userid,a.username FROM " . PRE . "_user AS a LEFT JOIN " . PRE . "_user_groups AS b USING(groupid) WHERE ( " . iif($selected, "userid='" . $selected . "' OR") . " ( active='1' AND b.gtype IN ('admin','indiv') ) ) ORDER BY username ASC");
	if (!count($data)) return;

	foreach ($data as $res)
	{
		echo '<option value="' . $res['userid'] . '"' . iif($res['userid'] == $selected, ' selected="selected"') . '>' . replace($res['username']) . '</option>';
	}
}

//Liste der Gruppen ausgeben
function user_groups($selected)
{
	global $set, $apx, $db;

	if (!is_array($selected))
		$selected = array();

	$numargs = func_num_args();
	$arg_list = func_get_args();
	for ($i = 0; $i < $numargs; $i++)
	{
		$selected[] = $arg_list[$i];
	}

	$data = $db->fetch("SELECT * FROM " . PRE . "_user_groups AS a GROUP BY name ASC");
	if (!count($data)) return;

	foreach ($data as $res)
	{
		echo '<option value="' . $res['groupid'] . '"' . iif(in_array($res['groupid'], $selected), ' selected="selected"') . '>' . replace($res['name']) . '</option>';
	}
}
