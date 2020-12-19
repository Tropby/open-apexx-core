<?php
	
/***   apexx parsing engine v1.1   ***/
/***   compiled Sat, 19 Dec 2020 11:11:59 +0100 from "modules/main/admin/searches.html"   ***/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


if ( $this->mode=='getvars' ) {
	$this->used_vars['modules/main/admin/searches.html']=array(
		'SEARCH',
		'SEARCH.HITS',
		'SEARCH.STRING'
	);
	$this->used_includes['modules/main/admin/searches.html']=array(
		
	);
}
else {
?><p class="hint"><?php echo $this->get_langvar( 'INFO'); ?></p>

<table width="100%" class="tablelist">
<thead>
<tr>
<th width="5%"><?php echo $this->get_langvar( 'HITS'); ?></th>
<th width="95%"><?php echo $this->get_langvar( 'SEARCHSTRING'); ?></th>
</tr>
</thead>
<tbody>
<?php if ( isset($this->parsevars['SEARCH']) && !is_array($this->parsevars['SEARCH']) ): echo "<b>runtime error:</b> SEARCH is not listable!"; elseif ( is_array($this->parsevars['SEARCH']) ): foreach ( $this->parsevars['SEARCH'] AS $list_SEARCH ): $this->parsevars['#LIST#']['SEARCH']=&$list_SEARCH; ?>
<tr>
<td style="text-align:center;font-size:10px;"><?php echo $this->parsevars['#LIST#']['SEARCH']['HITS']; ?></td>
<td><?php echo $this->parsevars['#LIST#']['SEARCH']['STRING']; ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table><?php
}
?>