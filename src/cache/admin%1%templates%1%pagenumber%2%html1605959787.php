<?php
	
/***   apexx parsing engine v1.1   ***/
/***   compiled Sat, 19 Dec 2020 11:11:54 +0100 from "admin/templates/pagenumber.html"   ***/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


if ( $this->mode=='getvars' ) {
	$this->used_vars['admin/templates/pagenumber.html']=array(
		'DOTPRINT',
		'EXPECT_PAGE',
		'PAGE.LINK',
		'PAGE.NUMBER',
		'PAGE_SELECTED'
	);
	$this->used_includes['admin/templates/pagenumber.html']=array(
		
	);
}
else {
?> <a href="<?php echo $this->parsevars['#LIST#']['PAGE']['LINK']; ?>"<?php if ( $this->parsevars['#LIST#']['PAGE']['NUMBER']==$this->parsevars['PAGE_SELECTED'] ): ?> class="selected"<?php endif; ?>><?php echo $this->parsevars['#LIST#']['PAGE']['NUMBER']; ?></a>
<?php $this->parsevars['EXPECT_PAGE']=$this->parsevars['#LIST#']['PAGE']['NUMBER']; 
 ++$this->parsevars['EXPECT_PAGE']; 
 $this->parsevars['DOTPRINT']=0; 
}
?>