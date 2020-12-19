<?php
	
/***   apexx parsing engine v1.1   ***/
/***   compiled Sat, 19 Dec 2020 11:11:53 +0100 from "admin/templates/pages.html"   ***/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


if ( $this->mode=='getvars' ) {
	$this->used_vars['admin/templates/pages.html']=array(
		'DOTPRINT',
		'END_LIMIT',
		'EXPECT_PAGE',
		'PAGE',
		'PAGE.NUMBER',
		'PAGE_COUNT',
		'PAGE_NEXT',
		'PAGE_PREVIOUS',
		'PAGE_SELECTED',
		'SEL_LOWER_LIMIT',
		'SEL_UPPER_LIMIT',
		'START_LIMIT'
	);
	$this->used_includes['admin/templates/pages.html']=array(
		'admin/templates/pagenumber.html'
	);
}
else {
 if ( $this->parsevars['PAGE_COUNT']>1 ): ?><div class="pages">
<?php if ( $this->parsevars['PAGE_PREVIOUS'] ): ?> <a href="<?php echo $this->parsevars['PAGE_PREVIOUS']; ?>">&laquo;</a><?php endif; 
 $this->parsevars['EXPECT_PAGE']=1;  $this->parsevars['END_LIMIT']=($this->parsevars['PAGE_COUNT']-5); 
 $this->parsevars['START_LIMIT']=5; 
 $this->parsevars['SEL_LOWER_LIMIT']=($this->parsevars['PAGE_SELECTED']-2); 
 $this->parsevars['SEL_UPPER_LIMIT']=($this->parsevars['PAGE_SELECTED']+2); 
 if ( isset($this->parsevars['PAGE']) && !is_array($this->parsevars['PAGE']) ): echo "<b>runtime error:</b> PAGE is not listable!"; elseif ( is_array($this->parsevars['PAGE']) ): foreach ( $this->parsevars['PAGE'] AS $list_PAGE ): $this->parsevars['#LIST#']['PAGE']=&$list_PAGE; 
 if ( $this->parsevars['EXPECT_PAGE']!=$this->parsevars['#LIST#']['PAGE']['NUMBER'] && !$this->parsevars['DOTPRINT'] ): ?> ...<?php $this->parsevars['DOTPRINT']=1;  endif; 
 if ( $this->parsevars['#LIST#']['PAGE']['NUMBER']>=$this->parsevars['SEL_LOWER_LIMIT'] && $this->parsevars['#LIST#']['PAGE']['NUMBER']<=$this->parsevars['SEL_UPPER_LIMIT'] ): 
 $this->include_file('admin/templates/pagenumber.html');  continue; 
 elseif ( $this->parsevars['#LIST#']['PAGE']['NUMBER']<=$this->parsevars['START_LIMIT'] || $this->parsevars['#LIST#']['PAGE']['NUMBER']>$this->parsevars['END_LIMIT'] || $this->parsevars['#LIST#']['PAGE']['NUMBER']%10==0 ): 
 $this->include_file('admin/templates/pagenumber.html'); 
 endif; 
 endforeach; endif; 
 if ( $this->parsevars['PAGE_NEXT'] ): ?> <a href="<?php echo $this->parsevars['PAGE_NEXT']; ?>">&raquo;</a><?php endif; ?>
</div><?php endif; 
}
?>