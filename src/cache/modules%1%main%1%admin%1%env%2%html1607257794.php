<?php
	
/***   apexx parsing engine v1.1   ***/
/***   compiled Sat, 19 Dec 2020 11:11:58 +0100 from "modules/main/admin/env.html"   ***/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


if ( $this->mode=='getvars' ) {
	$this->used_vars['modules/main/admin/env.html']=array(
		'C_GROUP',
		'C_USER',
		'FIRST',
		'FISRT',
		'MYSQL',
		'PARAM_INFO.DESCRIPTION',
		'PARAM_INFO.NAME',
		'PATH_BASEDIR',
		'PATH_MODULES',
		'PATH_UPLOADS',
		'PHP',
		'SERVER',
		'TFUNC',
		'TFUNC.DESCRIPTION',
		'TFUNC.FUNC',
		'TFUNC.FUNCNAME',
		'TFUNC.MODULE',
		'TFUNC.PARAMS',
		'TFUNC.PARAM_INFO',
		'VERSION'
	);
	$this->used_includes['modules/main/admin/env.html']=array(
		
	);
}
else {
?><h2>System-Konfiguration</h2>
<table width="100%" class="table">
<colgroup>
<col width="200" />
<col />
</colgroup>
<tr class="tableheader"><td colspan="2"><?php echo $this->get_langvar( 'SERVER'); ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'SERVERVERSION'); ?>:</td><td><?php echo $this->parsevars['SERVER']; ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'PHPVERSION'); ?>:</td><td><?php echo $this->parsevars['PHP']; ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'MYSQLVERSION'); ?>:</td><td><?php echo $this->parsevars['MYSQL']; ?></td></tr>
</table>

<table width="100%" class="table" style="margin-top:15px;">
<colgroup>
<col width="200" />
<col />
</colgroup>
<tr class="tableheader"><td colspan="2"><?php echo $this->get_langvar( 'APEXX'); ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'APXVERSION'); ?>:</td><td><?php echo $this->parsevars['VERSION']; ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'USERS'); ?>:</td><td><?php echo $this->parsevars['C_USER']; ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'USERGROUPS'); ?>:</td><td><?php echo $this->parsevars['C_GROUP']; ?></td></tr>
</table>

<table width="100%" class="table" style="margin:15px 0;">
<colgroup>
<col width="200" />
<col />
</colgroup>
<tr class="tableheader"><td colspan="2"><?php echo $this->get_langvar( 'PATHS'); ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'PATH_BASEDIR'); ?>:</td><td><?php echo $this->parsevars['PATH_BASEDIR']; ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'PATH_MODULES'); ?>:</td><td><?php echo $this->parsevars['PATH_MODULES']; ?></td></tr>
<tr><td><?php echo $this->get_langvar( 'PATH_UPLOADS'); ?>:</td><td><?php echo $this->parsevars['PATH_UPLOADS']; ?></td></tr>
</table>

<h2>Template-Funktionen</h2>
<table width="100%" class="tablelist">
<colgroup>
<col width="25%" />
<col width="25%" />
<col width="10%" />
<col width="10%" />
<col width="20%" />
</colgroup>
<thead>
<tr>
<th class="header2"><?php echo $this->get_langvar( 'TMPLFUNC'); ?></th>
<th class="header2"><?php echo $this->get_langvar( 'DESCRIPTION'); ?></th>
<th class="header2"><?php echo $this->get_langvar( 'FUNCNAME'); ?></th>
<th class="header2"><?php echo $this->get_langvar( 'PARAMS'); ?></th>
<th class="header2"><?php echo $this->get_langvar( 'MODULE'); ?></th>
</tr>
</thead>
<tbody>
<?php if ( isset($this->parsevars['TFUNC']) && !is_array($this->parsevars['TFUNC']) ): echo "<b>runtime error:</b> TFUNC is not listable!"; elseif ( is_array($this->parsevars['TFUNC']) ): foreach ( $this->parsevars['TFUNC'] AS $list_TFUNC ): $this->parsevars['#LIST#']['TFUNC']=&$list_TFUNC; ?>
<tr>
<td>{<?php echo $this->parsevars['#LIST#']['TFUNC']['FUNC']; ?>(<?php $this->parsevars['FIRST']=1;  if ( isset($this->parsevars['#LIST#']['TFUNC']['PARAM_INFO']) && !is_array($this->parsevars['#LIST#']['TFUNC']['PARAM_INFO']) ): echo "<b>runtime error:</b> TFUNC.PARAM_INFO is not listable!"; elseif ( is_array($this->parsevars['#LIST#']['TFUNC']['PARAM_INFO']) ): foreach ( $this->parsevars['#LIST#']['TFUNC']['PARAM_INFO'] AS $list_PARAM_INFO ): $this->parsevars['#LIST#']['PARAM_INFO']=&$list_PARAM_INFO;  if ( $this->parsevars['FIRST']==0 ): ?>, <?php endif;  echo $this->parsevars['#LIST#']['PARAM_INFO']['NAME'];  $this->parsevars['FIRST']=0;  endforeach; endif; ?>)}</td>
<td><?php echo $this->parsevars['#LIST#']['TFUNC']['DESCRIPTION']; ?></td>
<td><?php echo $this->parsevars['#LIST#']['TFUNC']['FUNCNAME']; ?></td>
<td align="center">
    <?php echo $this->parsevars['#LIST#']['TFUNC']['PARAMS']; ?><br />

    <?php $this->parsevars['FIRST']=1; 
     if ( isset($this->parsevars['#LIST#']['TFUNC']['PARAM_INFO']) && !is_array($this->parsevars['#LIST#']['TFUNC']['PARAM_INFO']) ): echo "<b>runtime error:</b> TFUNC.PARAM_INFO is not listable!"; elseif ( is_array($this->parsevars['#LIST#']['TFUNC']['PARAM_INFO']) ): foreach ( $this->parsevars['#LIST#']['TFUNC']['PARAM_INFO'] AS $list_PARAM_INFO ): $this->parsevars['#LIST#']['PARAM_INFO']=&$list_PARAM_INFO; 
         if ( $this->parsevars['FIRST']==1 ): 
             $this->parsevars['FISRT']=0; 
         else: ?>
            <br />
        <?php endif; 
         echo $this->parsevars['#LIST#']['PARAM_INFO']['NAME']; ?> = <?php echo $this->parsevars['#LIST#']['PARAM_INFO']['DESCRIPTION']; 
     endforeach; endif; ?>
</td>
<td align="center"><?php echo $this->parsevars['#LIST#']['TFUNC']['MODULE']; ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table><?php
}
?>