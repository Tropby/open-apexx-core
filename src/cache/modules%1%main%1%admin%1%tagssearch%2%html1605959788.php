<?php
	
/***   apexx parsing engine v1.1   ***/
/***   compiled Sat, 19 Dec 2020 11:11:53 +0100 from "modules/main/admin/tagssearch.html"   ***/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


if ( $this->mode=='getvars' ) {
	$this->used_vars['modules/main/admin/tagssearch.html']=array(
		'ITEM'
	);
	$this->used_includes['modules/main/admin/tagssearch.html']=array(
		
	);
}
else {
?><form action="action.php" method="post" class="searchform">
<table width="100%">
<colgroup>
<col width="100" />
<col />
</colgroup>
<tr><td><?php echo $this->get_langvar( 'SEARCHTEXT'); ?>:</td><td>
<input type="text" name="item" class="input" value="<?php echo $this->parsevars['ITEM']; ?>" size="70" style="width:300px;" /> <input type="submit" name="apxsubmit" value="<?php echo $this->get_langvar( 'SEARCH'); ?>" accesskey="s" class="button" /><br />
</td>
</tr>
</table>
<input type="hidden" name="action" value="main.tags" />
</form><?php
}
?>