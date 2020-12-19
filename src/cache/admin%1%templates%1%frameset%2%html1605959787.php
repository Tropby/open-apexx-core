<?php
	
/***   apexx parsing engine v1.1   ***/
/***   compiled Sat, 19 Dec 2020 11:11:34 +0100 from "admin/templates/frameset.html"   ***/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


if ( $this->mode=='getvars' ) {
	$this->used_vars['admin/templates/frameset.html']=array(
		'CHARSET',
		'SERVER_REQUEST_FRAMEURL'
	);
	$this->used_includes['admin/templates/frameset.html']=array(
		
	);
}
else {
 echo "<?xml"; ?> version="1.0" encoding="<?php echo $this->parsevars['CHARSET']; ?>"<?php echo "?>\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>apexx - <?php echo $this->get_langvar( 'CORE_ADMINISTRATION'); ?></title>
<meta http-equiv="content-Type" content="text/html; charset=<?php echo $this->parsevars['CHARSET']; ?>" />
</head>

<frameset cols="201,*" frameborder="0" framespacing="0" border="0">
<frame src="navi.php" name="frame_navi" noresize="noresize" />
<frame src="<?php if ( $this->parsevars['SERVER_REQUEST_FRAMEURL'] ):  echo $this->parsevars['SERVER_REQUEST_FRAMEURL'];  else: ?>action.php<?php endif; ?>" name="frame_content" />
</frameset>
<noframes>Sorry, your browser does not support frames!</noframes>

</html><?php
}
?>