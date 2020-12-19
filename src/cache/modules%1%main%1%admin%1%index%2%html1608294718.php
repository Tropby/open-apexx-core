<?php
	
/***   apexx parsing engine v1.1   ***/
/***   compiled Sat, 19 Dec 2020 11:11:34 +0100 from "modules/main/admin/index.html"   ***/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


if ( $this->mode=='getvars' ) {
	$this->used_vars['modules/main/admin/index.html']=array(
		'EMAIL',
		'GROUP',
		'MODULES',
		'ONLINE',
		'ONLINE_COUNT',
		'SESSION',
		'USERID',
		'USERNAME',
		'USERNAME_LOGIN',
		'VERSION'
	);
	$this->used_includes['modules/main/admin/index.html']=array(
		
	);
}
else {
?><p class="hint"><?php echo $this->get_langvar( 'WELCOME'); ?></p>

<table width="100%" class="table">
<tr><td class="tableheader"><?php echo $this->parsevars['ONLINE_COUNT']; ?> <?php echo $this->get_langvar( 'ONLINE'); ?></td></tr>
<tr><td><?php echo $this->parsevars['ONLINE']; ?></td></tr>
</table>

<table width="100%" class="table" style="margin-top:15px;">
<colgroup>
<col width="30%" />
<col width="70%" />
</colgroup>
<tr><td class="tableheader" colspan="2"><?php echo $this->get_langvar( 'ACCOUNT'); ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'USERID'); ?>:</td><td>#<?php echo $this->parsevars['USERID']; ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'USERNAME_LOGIN'); ?>:</td><td><?php echo $this->parsevars['USERNAME_LOGIN']; ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'USERNAME'); ?>:</td><td><?php echo $this->parsevars['USERNAME']; ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'EMAIL'); ?>:</td><td><?php if ( $this->parsevars['EMAIL'] ):  echo $this->parsevars['EMAIL'];  else: ?>-<?php endif; ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'GROUP'); ?>:</td><td><?php echo $this->parsevars['GROUP']; ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'LASTSESSION'); ?>:</td><td><?php echo $this->parsevars['SESSION']; ?></td></tr>
</table>

<table width="100%" class="table" style="margin-top:15px;">
<colgroup>
<col width="30%" />
<col width="70%" />
</colgroup>
<tr><td class="tableheader" colspan="2"><?php echo $this->get_langvar( 'APEXX'); ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'VERSION'); ?>:</td><td><?php echo $this->parsevars['VERSION']; ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'LATESTVERSION'); ?>:</td><td><img src="https://raw.githubusercontent.com/Tropby/open-apexx/master/current_version_open_apexx.png" alt="" /></td></tr>
<tr><td><?php echo $this->get_langvar( 'MODULES'); ?>:</td><td><?php echo $this->parsevars['MODULES']; ?></td></tr>
</table><?php
}
?>