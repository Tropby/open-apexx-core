<?php
	
/***   apexx parsing engine v1.1   ***/
/***   compiled Sat, 19 Dec 2020 12:07:53 +0100 from "admin/templates/table.html"   ***/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


if ( $this->mode=='getvars' ) {
	$this->used_vars['admin/templates/table.html']=array(
		'CHECKBOXES',
		'COL',
		'COL.TITLE',
		'COL.WIDTH',
		'COL1_ACTIVE',
		'COL1_ATTRIB',
		'COL2_ACTIVE',
		'COL2_ATTRIB',
		'COL3_ACTIVE',
		'COL3_ATTRIB',
		'COL4_ACTIVE',
		'COL4_ATTRIB',
		'COL5_ACTIVE',
		'COL5_ATTRIB',
		'COL6_ACTIVE',
		'COL6_ATTRIB',
		'COL7_ACTIVE',
		'COL7_ATTRIB',
		'COL8_ACTIVE',
		'COL8_ATTRIB',
		'COL9_ACTIVE',
		'COL9_ATTRIB',
		'COLCOUNT',
		'FOOTER',
		'MULTIACTION',
		'MULTIACTION.OVERLAY',
		'MULTIACTION.URL',
		'SECTOKEN',
		'TABLE',
		'TABLE.CLASS',
		'TABLE.COL1',
		'TABLE.COL2',
		'TABLE.COL3',
		'TABLE.COL4',
		'TABLE.COL5',
		'TABLE.COL6',
		'TABLE.COL7',
		'TABLE.COL8',
		'TABLE.COL9',
		'TABLE.ID',
		'TABLE.OPTIONS',
		'TABLE.SPACER'
	);
	$this->used_includes['admin/templates/table.html']=array(
		
	);
}
else {
?><form name="tableform" action="action.php" method="post">
<table width="100%" class="tablelist">
<thead>
<tr>
<?php if ( isset($this->parsevars['COL']) && !is_array($this->parsevars['COL']) ): echo "<b>runtime error:</b> COL is not listable!"; elseif ( is_array($this->parsevars['COL']) ): foreach ( $this->parsevars['COL'] AS $list_COL ): $this->parsevars['#LIST#']['COL']=&$list_COL; ?>
<th width="<?php echo $this->parsevars['#LIST#']['COL']['WIDTH']; ?>%"><?php echo $this->parsevars['#LIST#']['COL']['TITLE']; ?></th>
<?php endforeach; endif; ?>
<th width="1" style="white-space:nowrap;padding:0px 10px;"><?php echo $this->get_langvar( 'CORE_COL_OPTIONS'); ?></th>
<?php if ( $this->parsevars['CHECKBOXES'] ): ?><th width="1"><input type="checkbox" name="checkall" id="checkall" onclick="checkbox_toggle(this.form)" /></th><?php endif; ?>
</tr>
</thead>
<?php if ( $this->parsevars['FOOTER'] && $this->parsevars['TABLE'] ): ?>
<tfoot>
<tr><td colspan="<?php echo $this->parsevars['COLCOUNT']; ?>" align="center"><?php echo $this->parsevars['FOOTER']; ?> <img src="design/marrow.gif" alt="" style="vertical-align:middle;" /></td></tr>
</tfoot>
<?php endif; ?>
<tbody>
<?php if ( isset($this->parsevars['TABLE']) && !is_array($this->parsevars['TABLE']) ): echo "<b>runtime error:</b> TABLE is not listable!"; elseif ( is_array($this->parsevars['TABLE']) ): foreach ( $this->parsevars['TABLE'] AS $list_TABLE ): $this->parsevars['#LIST#']['TABLE']=&$list_TABLE; 
 if ( $this->parsevars['#LIST#']['TABLE']['SPACER'] ): ?><tr class="spacer"><td colspan="<?php echo $this->parsevars['COLCOUNT']; ?>"><img src="design/1px.gif" width="1" height="1" alt="" /></td></tr><?php endif; ?>
<tr class="<?php echo $this->parsevars['#LIST#']['TABLE']['CLASS']; ?>" id="<?php echo $this->parsevars['#LIST#']['TABLE']['ID']; ?>">
<?php if ( $this->parsevars['COL1_ACTIVE'] ): ?><td<?php echo $this->parsevars['COL1_ATTRIB']; ?>><?php echo $this->parsevars['#LIST#']['TABLE']['COL1']; ?></td><?php endif; 
 if ( $this->parsevars['COL2_ACTIVE'] ): ?><td<?php echo $this->parsevars['COL2_ATTRIB']; ?>><?php echo $this->parsevars['#LIST#']['TABLE']['COL2']; ?></td><?php endif; 
 if ( $this->parsevars['COL3_ACTIVE'] ): ?><td<?php echo $this->parsevars['COL3_ATTRIB']; ?>><?php echo $this->parsevars['#LIST#']['TABLE']['COL3']; ?></td><?php endif; 
 if ( $this->parsevars['COL4_ACTIVE'] ): ?><td<?php echo $this->parsevars['COL4_ATTRIB']; ?>><?php echo $this->parsevars['#LIST#']['TABLE']['COL4']; ?></td><?php endif; 
 if ( $this->parsevars['COL5_ACTIVE'] ): ?><td<?php echo $this->parsevars['COL5_ATTRIB']; ?>><?php echo $this->parsevars['#LIST#']['TABLE']['COL5']; ?></td><?php endif; 
 if ( $this->parsevars['COL6_ACTIVE'] ): ?><td<?php echo $this->parsevars['COL6_ATTRIB']; ?>><?php echo $this->parsevars['#LIST#']['TABLE']['COL6']; ?></td><?php endif; 
 if ( $this->parsevars['COL7_ACTIVE'] ): ?><td<?php echo $this->parsevars['COL7_ATTRIB']; ?>><?php echo $this->parsevars['#LIST#']['TABLE']['COL7']; ?></td><?php endif; 
 if ( $this->parsevars['COL8_ACTIVE'] ): ?><td<?php echo $this->parsevars['COL8_ATTRIB']; ?>><?php echo $this->parsevars['#LIST#']['TABLE']['COL8']; ?></td><?php endif; 
 if ( $this->parsevars['COL9_ACTIVE'] ): ?><td<?php echo $this->parsevars['COL9_ATTRIB']; ?>><?php echo $this->parsevars['#LIST#']['TABLE']['COL9']; ?></td><?php endif; ?>
<td align="center" style="white-space:nowrap;"><?php if ( $this->parsevars['#LIST#']['TABLE']['OPTIONS'] ):  echo $this->parsevars['#LIST#']['TABLE']['OPTIONS'];  else: ?>&nbsp;<?php endif; ?></td>
<?php if ( $this->parsevars['CHECKBOXES'] ): ?><td align="center"><?php if ( $this->parsevars['#LIST#']['TABLE']['ID'] ): ?><input type="checkbox" name="multiid[]" value="<?php echo $this->parsevars['#LIST#']['TABLE']['ID']; ?>" /><?php else: ?>&nbsp;<?php endif; ?></td><?php endif; ?>
</tr>
<?php endforeach; endif; 
 if ( !$this->parsevars['TABLE'] ): ?><tr><td colspan="<?php echo $this->parsevars['COLCOUNT']; ?>" class="tablenone"><?php echo $this->get_langvar( 'NONE'); ?></td></tr><?php endif; ?>
</tbody>
</table>
</form>

<?php if ( $this->parsevars['MULTIACTION'] ): ?>
<script type="text/javascript">

var multiactions = new Array();
<?php if ( isset($this->parsevars['MULTIACTION']) && !is_array($this->parsevars['MULTIACTION']) ): echo "<b>runtime error:</b> MULTIACTION is not listable!"; elseif ( is_array($this->parsevars['MULTIACTION']) ): foreach ( $this->parsevars['MULTIACTION'] AS $list_MULTIACTION ): $this->parsevars['#LIST#']['MULTIACTION']=&$list_MULTIACTION; ?>
multiactions[multiactions.length] = { url:'<?php echo $this->parsevars['#LIST#']['MULTIACTION']['URL']; ?>', overlay:<?php if ( $this->parsevars['#LIST#']['MULTIACTION']['OVERLAY'] ): ?>true<?php else: ?>false<?php endif; ?> };
<?php endforeach; endif; ?>


//Multi-Aktion ausführen
function tableMultiAction(index) {
	var selected = new Array();
	var form = document.forms.tableform;
	for ( var i = 0; i<form.elements.length; i++ ) {
		var element = form.elements[i];
		if ( element.type=='checkbox' && element.name=='multiid[]' && element.checked ) {
			selected[selected.length] = element.value;
		}
	}
	if ( selected.length ) {
		var action = multiactions[index];
		var url = action.url;
		url += '&sectoken=<?php echo $this->parsevars['SECTOKEN']; ?>';
		url += '&multiid[]='+selected.join('&multiid[]=');
		if ( action.overlay ) {
			MessageOverlayManager.createLayer(url);
		}
		else {
			window.location.href = url;
		}
	}
}

</script>
<?php endif; 
}
?>