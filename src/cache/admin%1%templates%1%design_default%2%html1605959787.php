<?php
	
/***   apexx parsing engine v1.1   ***/
/***   compiled Sat, 19 Dec 2020 12:07:53 +0100 from "admin/templates/design_default.html"   ***/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


if ( $this->mode=='getvars' ) {
	$this->used_vars['admin/templates/design_default.html']=array(
		'CHARSET',
		'CONTENT',
		'HEADLINE',
		'JS_FOOTER'
	);
	$this->used_includes['admin/templates/design_default.html']=array(
		
	);
}
else {
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>apexx - <?php echo $this->get_langvar( 'CORE_ADMINISTRATION'); ?> | <?php echo $this->parsevars['HEADLINE']; ?></title>
<meta http-equiv="content-Type" content="text/html; charset=<?php echo $this->parsevars['CHARSET']; ?>" />
<link rel="stylesheet" href="design/import.css" type="text/css" />
<script type="text/javascript" src="../lib/yui/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="../lib/javascript/global.js"></script>
<script type="text/javascript" src="../lib/javascript/admin_basic.js"></script>
<script type="text/javascript" src="../lib/javascript/admin_mediamanager.js"></script>
<script type="text/javascript" src="../lib/javascript/admin_inserts.js"></script>
<script type="text/javascript" src="../lib/javascript/messageoverlay.js"></script>
<script type="text/javascript">

var editors=new Array();
var editor_status=0;

//Titelleiste aktualisieren
if ( typeof(parent.document)!='undefined' && typeof(parent.document)!='unknown' && typeof(parent.document.title)=='string' ) {
	parent.document.title = 'apexx - <?php echo $this->get_langvar( 'CORE_ADMINISTRATION'); ?> | <?php echo $this->parsevars['HEADLINE']; ?>';
}

//Frameset wiederherstellen
function resetFrameset() {
	if ( top.frames.length!=2 ) {
		top.window.location.href = 'index.php?frameurl='+escape(window.location);
	}
}

//Link anzeigen
window.onload = function() {
	if ( top==self ) {
		YAHOO.util.Dom.setStyle(YAHOO.util.Dom.get('framereset'), 'display', 'block');
	}
}

//Info-Text anzeigen
function displayInfo(text) {
	var box = yDom.get('infobox');
	box.innerHTML = text;
	yDom.setStyle(box, 'display', 'block');
}


//-->
</script>
</head>
<body>
<noscript class="jsrequired"><div><?php echo $this->get_langvar( 'JSREQUIRED'); ?></div></noscript>

<h1><?php echo $this->parsevars['HEADLINE']; ?></h1>
<p id="framereset"><a href="javascript:resetFrameset();"><?php echo $this->get_langvar( 'RESET_FRAMESET'); ?></a></p>
<p id="infobox" style="display:none;"></p>

<iframe name="postframe" src="about:blank" style="width:100%;height:100px;display:none;"></iframe>
<!-- INHALT START -->

<?php echo $this->parsevars['CONTENT']; ?>

<!-- INHALT ENDE -->

<?php echo $this->parsevars['JS_FOOTER']; ?>

<form name="removefocus" style="display:none;height:0;overflow:none;">
<input type="text" name="textfield" />
</form>

</body>
</html><?php
}
?>