<?php
	
/***   apexx parsing engine v1.1   ***/
/***   compiled Sat, 19 Dec 2020 12:07:53 +0100 from "admin/templates/navi.html"   ***/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


if ( $this->mode=='getvars' ) {
	$this->used_vars['admin/templates/navi.html']=array(
		'CHARSET',
		'NAVI',
		'NAVI.ACTION',
		'NAVI.ACTION.LINK',
		'NAVI.ACTION.TITLE',
		'NAVI.HIDDEN',
		'NAVI.ID',
		'NAVI.TITLE',
		'SECINDEX',
		'SECTION',
		'SECTION.ID',
		'SECTION.SELECTED',
		'SECTION.TITLE',
		'SECTOKEN',
		'SELSECTION'
	);
	$this->used_includes['admin/templates/navi.html']=array(
		
	);
}
else {
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Navigation</title>
<meta http-equiv="content-Type" content="text/html; charset=<?php echo $this->parsevars['CHARSET']; ?>" />
<link rel="stylesheet" href="design/navi.css" type="text/css" />
<script type="text/javascript" src="../lib/yui/yahoo/yahoo-min.js"></script>
<script type="text/javascript" src="../lib/yui/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="../lib/javascript/global.js"></script>
<script type="text/javascript" src="../lib/javascript/admin_basic.js"></script>
<script type="text/javascript" src="../lib/javascript/admin_mediamanager.js"></script>
</head>
<body>

<div id="logo"><a href="action.php?action=main.index" target="frame_content"><img src="design/logo.gif" alt="apexx Logo" /></a></div>
<div id="buttons"><a href="action.php" target="frame_content"><img src="design/button_home.gif" alt="<?php echo $this->get_langvar( 'CORE_HOME'); ?>" title="<?php echo $this->get_langvar( 'CORE_HOME'); ?>" onmouseover="roll_in(this,'home')" onmouseout="roll_out(this,'home')" /></a><a href="javascript:openmm('mediamanager.php?action=mediamanager.index')"><img src="design/button_media.gif" alt="<?php echo $this->get_langvar( 'CORE_MEDIA'); ?>" title="<?php echo $this->get_langvar( 'CORE_MEDIA'); ?>" onmouseover="roll_in(this,'media')" onmouseout="roll_out(this,'media')" /></a><a href="action.php?action=user.logout&amp;sectoken=<?php echo $this->parsevars['SECTOKEN']; ?>" target="_top"><img src="design/button_logout.gif" alt="<?php echo $this->get_langvar( 'CORE_LOGOUT'); ?>" title="<?php echo $this->get_langvar( 'CORE_LOGOUT'); ?>" onmouseover="roll_in(this,'logout')" onmouseout="roll_out(this,'logout')" /></a><a href="../" target="_blank"><img src="design/button_website.gif" alt="<?php echo $this->get_langvar( 'CORE_WEBSITE'); ?>" title="<?php echo $this->get_langvar( 'CORE_WEBSITE'); ?>" onmouseover="roll_in(this,'website')" onmouseout="roll_out(this,'website')" /></a></div>

<?php $this->parsevars['SELSECTION']=0; 
 if ( $this->parsevars['SECTION'] ): ?><form action="index.php" method="post" target="_top" id="navi_sections">
<select name="selectsection" onchange="updateSection(this);"><option value="0" style="font-weight:bold;"><?php echo $this->get_langvar( 'ALLSECS'); ?></option><?php if ( isset($this->parsevars['SECTION']) && !is_array($this->parsevars['SECTION']) ): echo "<b>runtime error:</b> SECTION is not listable!"; elseif ( is_array($this->parsevars['SECTION']) ): foreach ( $this->parsevars['SECTION'] AS $list_SECTION ): $this->parsevars['#LIST#']['SECTION']=&$list_SECTION;  ++$this->parsevars['SECINDEX']; ?><option value="<?php echo $this->parsevars['#LIST#']['SECTION']['ID']; ?>"<?php if ( $this->parsevars['#LIST#']['SECTION']['SELECTED'] ):  $this->parsevars['SELSECTION']=$this->parsevars['SECINDEX']; ?> selected="selected"<?php endif; ?>><?php echo $this->parsevars['#LIST#']['SECTION']['TITLE']; ?></option><?php endforeach; endif; ?></select>
</form><?php endif; ?>

<ul id="navi" class="navi">
<?php if ( isset($this->parsevars['NAVI']) && !is_array($this->parsevars['NAVI']) ): echo "<b>runtime error:</b> NAVI is not listable!"; elseif ( is_array($this->parsevars['NAVI']) ): foreach ( $this->parsevars['NAVI'] AS $list_NAVI ): $this->parsevars['#LIST#']['NAVI']=&$list_NAVI; ?>
<li class="navi_header<?php if ( $this->parsevars['#LIST#']['NAVI']['HIDDEN'] ): ?> collapsed<?php else: ?> expanded<?php endif; ?>" id="<?php echo $this->parsevars['#LIST#']['NAVI']['ID']; ?>"><a href="javascript:void(0);" class="navi_header"><span class="move" id="<?php echo $this->parsevars['#LIST#']['NAVI']['ID']; ?>_move"></span><span class="inner"><?php echo $this->parsevars['#LIST#']['NAVI']['TITLE']; ?></span></a>
<ul class="navi_sub" style="<?php if ( $this->parsevars['#LIST#']['NAVI']['HIDDEN'] ): ?>display:none;<?php endif; ?>">
<?php if ( isset($this->parsevars['#LIST#']['NAVI']['ACTION']) && !is_array($this->parsevars['#LIST#']['NAVI']['ACTION']) ): echo "<b>runtime error:</b> NAVI.ACTION is not listable!"; elseif ( is_array($this->parsevars['#LIST#']['NAVI']['ACTION']) ): foreach ( $this->parsevars['#LIST#']['NAVI']['ACTION'] AS $list_ACTION ): $this->parsevars['#LIST#']['ACTION']=&$list_ACTION; ?><li class="navi_sub"><a href="<?php echo $this->parsevars['#LIST#']['ACTION']['LINK']; ?>" target="frame_content"><?php echo $this->parsevars['#LIST#']['ACTION']['TITLE']; ?></a></li><?php endforeach; endif; ?>
</ul>
</li>
<?php endforeach; endif; ?>
</ul>

<script type="text/javascript" src="../lib/yui/dragdrop/dragdrop-min.js"></script>
<script type="text/javascript" src="../lib/yui/animation/animation-min.js"></script>
<script type="text/javascript" src="../lib/yui/connection/connection-min.js"></script>
<script type="text/javascript" src="../lib/javascript/sortablenavi.js"></script>
<script type="text/javascript" src="../lib/javascript/collapseable.js"></script>
<script type="text/javascript">

var menu=new Array();
var mi=0;
<?php if ( isset($this->parsevars['NAVI']) && !is_array($this->parsevars['NAVI']) ): echo "<b>runtime error:</b> NAVI is not listable!"; elseif ( is_array($this->parsevars['NAVI']) ): foreach ( $this->parsevars['NAVI'] AS $list_NAVI ): $this->parsevars['#LIST#']['NAVI']=&$list_NAVI; ?>
menu[mi++]='<?php echo $this->parsevars['#LIST#']['NAVI']['ID']; ?>';
<?php endforeach; endif; ?>


var selectedSection = <?php echo $this->parsevars['SELSECTION']; ?>;

function updateSection(select) {
	var confirmed = confirm('<?php echo $this->get_langvar( 'CORE_CHANGESEC'); ?>');
	if ( confirmed ) {
		var secid = select.options[select.selectedIndex].value;
		top.location.href = "index.php?selectsection="+secid;
		selectedSection = select.selectedIndex;
	}
	else {
		select.selectedIndex = selectedSection;
	}
}

yEvent.onDOMReady(function() {
	new SortableNavi(yDom.get('navi'));
});

</script>

</body>
</html><?php
}
?>