<?php 

/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|           (c) Copyright 2005-2009, Christian Scheb            |
|                  http://www.stylemotion.de                    |
|                                                               |
|---------------------------------------------------------------|
| THIS SOFTWARE IS NOT FREE! MAKE SURE YOU OWN A VALID LICENSE! |
| DO NOT REMOVE ANY COPYRIGHTS WITHOUT PERMISSION!              |
| SOFTWARE BELONGS TO ITS AUTHORS!                              |
\***************************************************************/


//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class tengine_compiler 
{

	private \apexx $apx;

var $autoinclude = true;
var $used_modules=array();
var $used_vars=array();
var $used_includes=array();
var $filename='';
var $nophp=true;
var $parseerror=true;
var $mod=array(
	'replace' => 'compatible_hsc',
	'lower' => 'strtolower',
	'upper' => 'strtoupper',
	'notags' => 'strip_tags',
	'breaks' => 'nl2br',
	'urlencode' => 'urlencode'
);
var $failed = false;

//Startup
function __construct($apx, $filename) {
	if ( !$filename ) die('<br /><b>fatal error:</b> missing filename!<br />');
	
	// Apexx instanz setzten
	$this->apx = $apx;

	//Datei setzen
	$this->filename=$filename;
	
	//Variablen Pattern
	$this->pattern_var='[A-Z]+[A-Z0-9_]*';
	$this->pattern_fvar=$this->pattern_var.'(\.'.$this->pattern_var.')+';
	$this->pattern_vars=$this->pattern_var.'(\.'.$this->pattern_var.')*';
	$this->pattern_number='[0-9]+|[0-9]+\.[0-9]+';
	$this->pattern_string='""|\'\'|".*?[^\\\\]"|\'.*?[^\\\\]\''; //Non-Greedy
	$this->pattern_all=$this->pattern_number.'|'.$this->pattern_string.'|'.$this->pattern_vars;
	$this->pattern_operator='\+|-|\*|/|%';
	$this->pattern_compare='===|!==|==|!=|>=|<=|>|<';
	$this->pattern_boole='&&|\|\|';
}



//Symbolische Zeichen "../", "./" und "//" entfernen
//F�r include-Pfade!
function cleanpath($path) {
	$result=array();
	$pp=explode('/',$path);
	if ( !$pp[0] ) $result[]='';
	
	foreach ( $pp AS $key => $dir ) {
		if ( $dir=='..' ) {
			if ( end($result)=='..' ) {
				$result[]='..';
			}
			elseif ( !$tmp=array_pop($result) ) {
				$result[]='..';
			}
		}
		elseif ( $dir && $dir!='.' ) {
			$result[]=$dir;
		}
	}
	
	if ( !end($pp) ) $result[]='';
	
	$remove=array(
		'./'=>'',
		'../'=>''
	);
	
	$cleanpath=implode('/',$result);
	$cleanpath=strtr($cleanpath,$remove);
	return $cleanpath;
}



/*********************** MASTER-FUNKTION ***********************/

function get_compiled_content($content) {
	if ( !$this->validate($content) ) return false;
	if ( !$compiled=$this->compile($content) ) return false;
	return $compiled;
}



/*********************** COMPILER ***********************/

/*** Datei-Code prüfen ***/
function validate($content) {
	$openelement=array();
	$level=0;
	$lines=explode("\n",$content);
	$linenumber=0;
	
	//Zeile für Zeile durchlaufen
	foreach ( $lines AS $line ) {
		++$linenumber;
		$found=array();
		
		preg_match_all('#{if +.+}|{elseif +.+}|{/if}|{else}|{list +.+}|{/list}|{continue}|{break}|{repeat +.+}|{/repeat}#sU',$line,$found);
		if ( !count($found[0]) ) continue;
		
  	foreach ( $found[0] AS $element ) {
  		$element=strtolower($element);
			
  		//IF
  		if ( substr($element,0,3)=='{if' ) {
  			++$level;
  			$openelement[$level]='{/if}';
  		}
			
			//ELSEIF
			elseif ( substr($element,0,7)=='{elseif' ) {
				if ( $openelement[$level]!='{/if}' || (isset($openelse[$level]) && $openelse[$level]===true) ) {
					$this->validate_error($element,$linenumber,$openelement[$level]);
					return false;
  			}
			}
			
			//ELSE
			elseif ( $element=='{else}' ) {
				if ( $openelement[$level]!='{/if}' ) {
  				$this->validate_error($element,$linenumber,$openelement[$level]);
					return false;
  			}
				$openelse[$level]=true;
			}
  		
  		//ENDIF
  		elseif ( $element=='{/if}' ) {
  			if ( $openelement[$level]!='{/if}' ) {
  				$this->validate_error($element,$linenumber,$openelement[$level]);
					return false;
  			}
  			unset($openelement[$level]);
			
			// TODO: Check if this Variable can be set
			if(isset($openelse[$level]))
				unset($openelse[$level]);
  			--$level;
  		}
  		
  		//LIST
  		elseif ( substr($element,0,5)=='{list' ) {
  			++$level;
  			$openelement[$level]='{/list}';
  		}
			
			//CONTINUE
			elseif ( $element=='{continue}' ) {
				if ( !in_array('{/list}',$openelement) ) {
  				$this->validate_error($element,$linenumber,$openelement[$level]);
					return false;
  			}
			}
  		
			//BREAK
			elseif ( $element=='{break}' ) {
				if ( !in_array('{/list}',$openelement) ) {
  				$this->validate_error($element,$linenumber,$openelement[$level]);
					return false;
  			}
			}
			
  		//ENDLIST
  		elseif ( $element=='{/list}' ) {
  			if ( $openelement[$level]!='{/list}' ) {
  				$this->validate_error($element,$linenumber,$openelement[$level]);
  				return false;
  			}
	  		unset($openelement[$level]);
  			--$level;
  		}
			
			//REPEAT OPEN
  		elseif ( substr($element,0,7)=='{repeat' ) {
  			++$level;
  			$openelement[$level]='{/repeat}';
  		}
			
			//REPEAT CLOSE
  		elseif ( $element=='{/repeat}' ) {
  			if ( $openelement[$level]!='{/repeat}' ) {
  				$this->validate_error($element,$linenumber,$openelement[$level]);
  				return false;
  			}
	  		unset($openelement[$level]);
  			--$level;
  		}
  	}
	}
	
	//Tags, die nicht geschlossen wurden
	if ( count($openelement) ) {
		foreach ( $openelement AS $element ) {
			echo '<br /><b>parse error:</b> expected "'.$element.'" in <b>'.$this->filename.'</b> on <b>line '.$linenumber.'</b> not found!';
			return false;
		}
	}
	
	return true;
}



/*** Validatierungs-Fehler ausgeben ***/
function validate_error($element,$linenumber,$openelement) {
	echo '<br /><b>parse error:</b> unexpected "'.$element.'" in <b>'.$this->filename.'</b> on <b>line '.$linenumber.'</b>';
	if ( $openelement ) echo ', expecting "'.$openelement.'"';
	echo '!<br />';
}



/*** Template kompilieren ***/
function compile($content) {
	
	//PHP löschen
	if ( $this->nophp ) {
		$content=preg_replace_callback('#<\?php(.*?)\?>#s',function($m) {return " ";},$content);
	}
	
	//Erwünschtes PHP
	$content=preg_replace_callback('#{php}(.*?){/php}#s',function($m) {return '<?php'.$m[1].'?>';},$content);
	
	//XML schützen
	$content=preg_replace_callback('#<\?xml(.*?)\?>#s',function($m) {return '<?php echo "<?xml"; ?>'.$m[1].'<?php echo "?>\n"; ?>' ;},$content);
	
	//Kommentare entfernen
	$content=preg_replace_callback('#{\*(.*?)\*}#s',function($m) {return "";},$content);
	
	//Variable mit Wert belegen
	$content=preg_replace_callback('#{(static *)?('.$this->pattern_vars.') *= *('.$this->pattern_all.')}#s',array(&$this,'compile_set'),$content);
	$content=preg_replace_callback('#{(static *)?('.$this->pattern_vars.') *= *(.*?)}#s',array(&$this,'compile_set_calculate'),$content);
	
	//Mathematische Funktionen
	$content=preg_replace_callback('#{('.$this->pattern_vars.') *('.$this->pattern_operator.') *(.*?)}#s',array(&$this,'compile_calculate'),$content);
	$content=preg_replace_callback('#{(\+\+|--) *('.$this->pattern_vars.')}#s',function($m) {return $this->compile_calculate_addsub($m[1],$m[2]);},$content); //Nur Variablennamen => ungefährlich
	$content=preg_replace_callback('#{('.$this->pattern_vars.') *(\+\+|--)}#s',function($m) {return $this->compile_calculate_addsub($m[3],$m[1]);},$content); //Nur Variablennamen => ungefährlich
	
	//IFs
	$content=preg_replace_callback('#{(else)?if +(.*?)}#s',array(&$this,'compile_if'),$content);
	$content=str_replace('{else}','<?php else: ?>',$content);
	$content=str_replace('{/if}','<?php endif; ?>',$content);
	if ( $this->failed ) return;
	
	//Listen
	$content=preg_replace_callback('#{list +(.*?)}#s',array(&$this,'compile_list'),$content);
	$content=str_replace('{/list}','<?php endforeach; endif; ?>',$content);
	$content=str_replace('{continue}','<?php continue; ?>',$content);
	$content=str_replace('{break}','<?php break; ?>',$content);
	if ( $this->failed ) return;
	
	//Repeats
	$content=preg_replace_callback('#{repeat +(.*?)}#s',array(&$this,'compile_repeat'),$content);
	$content=str_replace('{/repeat}','<?php endfor; ?>',$content);
	
	//Includes
	$content=preg_replace_callback('#{include +(.*?)}#s',array(&$this,'compile_include'),$content);
	$content=preg_replace_callback('#{includemoduletemplate +(.*?) +(.*?)}#s',array(&$this,'compile_include_from_module'),$content);
	
	//Funktionen
	$content=preg_replace_callback('#{([A-Z0-9_-]+)\((.*?)\)}#s',array(&$this,'compile_function'),$content);
	
	//Variablen ausgeben
	$content=preg_replace_callback('#{('.$this->pattern_vars.')}#s',function($m) {return $this->compile_echo($m[1]);},$content); //Nur Variablennamen => ungef�hrlich
	$content=preg_replace_callback('#{LANG\[([A-Z0-9_-]+)\]}#s',function($m) {return '<?php echo $this->get_langvar( \''.$m[1].'\'); ?>' ;},$content); //Nur Array-Keys => ungef�hrlich
	
	//Variablen mit Modifikatoren
	foreach ( $this->mod AS $modid => $func ) {
		$content=preg_replace_callback('#{('.$this->pattern_vars.')\|'.$modid.'}#s',function($m) use ($func) {return $this->compile_echo($m[1], $func); }, $content); //Nur Variablennamen => ungefährlich
	}
	
	//Informationen anfügen
	$content=$this->add_header($content);
	
	//Clean Code
	$content=preg_replace_callback("#\?>([ 	]*\r?\n[ 	]*)*<\?php#",function($m) {return count($m)>=2 ? $m[1] : "" ;},$content);
	
	return $content;
}



/*** Info-Header hinzufügen ***/
function add_header($content) {
	$date=date('r');
	
	//Variablen + Includes auflisten
	$vars=array_unique($this->used_vars);
	$includes=array_unique($this->used_includes);
	$modules=array_unique($this->used_modules);
	sort($vars);
	sort($includes);
	
	$varlist="'".implode("',\n		'",$vars)."'";
	$includelist="'".implode("',\n		'",$includes)."'";
	if ( $varlist=="''" ) $varlist='';
	if ( $includelist=="''" ) $includelist='';
	
	$header=<<<HEADER

	
/***   apexx parsing engine v1.1   ***/
/***   compiled {$date} from "{$this->filename}"   ***/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


if ( \$this->mode=='getvars' ) {
	\$this->used_vars['{$this->filename}']=array(
		{$varlist}
	);
	\$this->used_includes['{$this->filename}']=array(
		{$includelist}
	);
}
else {

HEADER;
	
	if ( $this->autoinclude ) {
		foreach ( $modules AS $module ) {
			$header .= "	require_once(BASEDIR.getmodulepath('{$module}').'tfunctions.php');\n";
		}
	}
	
	$footer=<<<FOOTER

}

FOOTER;

	return '<?php'.$header.'?>'.$content.'<?php'.$footer.'?>';
}



/*** Variablennamen kompilieren ***/
function get_compiled_var($varname) {
	
	if ( preg_match('#^'.$this->pattern_fvar.'$#',$varname) ) {
		$parts=explode('.',$varname);
		$listid=$parts[count($parts)-2];
		$keyid=$parts[count($parts)-1];
		$this->used_vars[]=$varname;
		return '$this->get_list_var(\''.$listid.'\', \''.$keyid.'\')';
	}
	
	if ( preg_match('#^'.$this->pattern_var.'$#',$varname) ) {
		$this->used_vars[]=$varname;
		return '$this->get_var(\''.$varname.'\')';
	}
	
	return false;
}



/*** Ausdruck verifizieren und kompilieren ***/
function compile_expression($expression,$isboole=false) {
	$i=0;
	$openbrak=0;
	$isnot=false;
	
	//Boolescher Ausdruck
	if ( $isboole ) {
		preg_match_all('#'.$this->pattern_all.'|'.$this->pattern_operator.'|'.$this->pattern_boole.'|'.$this->pattern_compare.'|\(|\)|!| +#s',$expression,$match,PREG_SET_ORDER);
		$prev='BOOLE'; //OPERATOR, ELEMENT, COMPARE, BOOLE
	}
	
	//Mathematischer Ausdruck
	else {
		preg_match_all('#'.$this->pattern_all.'|'.$this->pattern_operator.'|\(|\)| +#s',$expression,$match,PREG_SET_ORDER);
		$prev='OPERATOR'; //OPERATOR, ELEMENT
	}
	
	//Prüfen, ob der Ausdruck komplett eingelesen wurde
	$check="";
	foreach ( $match AS $part ) {
		$check.=$part[0];
	}
	if ( strlen($check)!=strlen($expression) ) return false;
	
	//Elemente durchlaufen und auf Richtigkeit prüfen
	foreach ( $match AS $key => $part ) {
		++$i;
		list($string)=$part;
		$string=trim($string); //Whitespace entfernen
		
		//Whitespace überspringen
		if ( $string=='' ) continue;
		
		//Verneinung
		if ( $isboole && $string=='!' ) {
			$isnot=true;
			continue;
		}
		
		/*********** KLAMMERN ***********/
		
		//Klammer öffnen
		if ( $string=='(' ) {
			if ( $prev!='BOOLE' && $prev!='OPERATOR' ) { $errcode=2; break; } //Vorgänger muss ein Boole-Operator sein!
			++$openbrak;
		}
		
		//Klammer schließen
		elseif ( $string==')' ) {
			if ( $openbrak==0 ) { $errcode=3; break; } //Klammern müssen geöffnet sein!
			if ( $prev!='ELEMENT' ) { $errcode=4; break; } //Vorgänger muss ein Element sein
			--$openbrak;
		}
		
		/*********** OPERATOREN ***********/
		
		//Mathematische Operatoren
		elseif ( preg_match('#^('.$this->pattern_operator.')$#',$string) ) {
			if ( $prev!='ELEMENT' ) { $errcode=6; break; }
			if ( $isnot ) { $errcode=7; break; } //Verneinung darf nicht auf Operatoren treffen
			$prev='OPERATOR';
		}
		
		//Vergleichs-Operatoren
		elseif ( $isboole && preg_match('#^('.$this->pattern_compare.')$#',$string) ) {
			if ( $prev!='ELEMENT' ) { $errcode=5; break; }
			if ( $isnot ) break; //Verneinung darf nicht auf Operatoren treffen
			$prev='COMPARE';
		}
		
		//Boole-Operatoren
		elseif ( $isboole && preg_match('#^('.$this->pattern_boole.')$#',$string) ) {
			if ( $prev!='ELEMENT' ) { $errcode=8; break; }
			if ( $isnot ) { $errcode=9; break; } //Verneinung darf nicht auf Operatoren treffen
			$prev='BOOLE';
		}
		
		/*********** ELEMENTE ***********/
		
		//Variablen
		elseif ( preg_match('#^('.$this->pattern_vars.')$#',$string) ) {
			if ( $prev!='COMPARE' && $prev!='OPERATOR' && $prev!='BOOLE' ) { $errcode=10; break; }
			$prev='ELEMENT';
			$isnot=false; //Verneinung lief OK
			$match[$key][0]=$this->get_compiled_var($string); //Konvertieren
		}
		
		//Zahlen und Strings
		elseif ( preg_match('#^('.$this->pattern_number.'|'.$this->pattern_string.')$#',$string) ) {
			if ( $prev!='COMPARE' && $prev!='OPERATOR' && $prev!='BOOLE' ) { $errcode=11; break; }
			$prev='ELEMENT';
			$isnot=false; //Verneinung lief OK
		}
	}
	
	//Fehlerinfos ausgeben
	if ( $this->parseerror && isset($errcode) && $errcode ) {
		echo 'stopped at '.$i.' of '.count($match).', '.iif($isnot,'ISNOT, ').'ERRCODE: '.$errcode.'<br />';
		print_r($match);
	}
	
	/* 
	 Abbrechen, wenn ein Fehler aufgetreten ist d.h.
	 - die Schleife vor Ende abgebrochen wurde
	 - das letzte Teilstück ein "!" war
	 - das letzte Teilstück kein Element war
	*/
	if ( $i!=count($match) || $isnot===true || $prev!='ELEMENT' ) {
		return false;
	}
	
	//IF zusammenbauen
	$newexpression="";
	foreach ( $match AS $element ) {
		$newexpression.=$element[0];
	}
	
	return $newexpression;
}



/*** Variable mit irgendeinem Wert belegen ***/
function compile_set($match) {
	$varname=$match[2];
	$value=$match[4];
	$isStatic=$match[1];
	
	//Variable
	$value=preg_replace_callback('#^('.$this->pattern_vars.')$#',function($m) {return $this->get_compiled_var($m[1]);},$value);
	
	//Maskierungen entfernen
	if ( substr($value,0,2)=="\'" && substr($value,-2)=="\'" ) {
		$value="'".substr($value,2,strlen($value)-4)."'";
	}
	
	if ( $isStatic ) {
		return '<?php $this->assign_static("'.$varname.'", '.$value.'); ?>';
	}
	else {
		return '<?php $help=&'.$this->get_compiled_var($varname).'; $help='.$value.'; ?>';
	}
}



/*** Variable mit dem Ergebnis einer Rechenoperation belegen ***/
function compile_set_calculate($match) {
	$varname=$match[2];
	$expression=$match[4];
	$isStatic=$match[1];
	
	//Nicht notwendig, Callback liefert den String "im Original"	
	//$expression=str_replace("\\'","'",trim($expression));
	
	$compiled_expr=$this->compile_expression($expression,false);
	
	//Fehler aufgetreten
	if ( $compiled_expr===false ) {
		$error='<br /><b>parse error:</b> &#123;'.$varname.strtr($expression,array('{'=>'&#123;','}'=>'&#125;')).'&#125; is not a valid expression!<br />';
		$error.='<?php '.$this->get_compiled_var($varname).'=false; ?>';
		return $error;
	}
	
	if ( $isStatic ) {
		return '<?php $this->assign_static("'.$varname.'", ('.$compiled_expr.')); ?>';
	}
	else {
		return '<?php $help=&'.$this->get_compiled_var($varname).'; $help=('.$compiled_expr.'); ?>';
	}
}



/*** KURZSCHREIBWEISE: Variable mit dem Ergebnis einer Rechenoperation belegen ***/
function compile_calculate($match) {
	$varname=$match[1];
	$operator=$match[3];
	$expression=$match[4];
	
	//Nicht notwendig, Callback liefert den String "im Original"	
	//$expression=str_replace("\\'","'",trim($expression));
	
	$compiled_expr=$this->compile_expression($expression,false);
	
	//Fehler aufgetreten
	if ( $compiled_expr===false ) {
		$error='<br /><b>parse error:</b> &#123;'.$varname.$operator.strtr($expression,array('{'=>'&#123;','}'=>'&#125;')).'&#125; is not a valid expression!<br />';
		$error.='<?php '.$this->get_compiled_var($varname).'=false; ?>';
		return $error;
	}
	
	return '<?php '.$this->get_compiled_var($varname).$operator.'=('.$compiled_expr.'); ?>';
}



/*** Math: ++ -- Variable ***/
function compile_calculate_addsub($operator,$varname) {
	return '<?php $help=&'.$this->get_compiled_var($varname).'; $help'.$operator.'; ?>';
}



/*** IF kompilieren ***/
function compile_if($match) {
	$expression=$match[2];
	$elseif=$match[1];
	
	//Nicht notwendig, Callback liefert den String "im Original"	
	//$expression=str_replace("\\'","'",trim($expression));
	
	$compiled_expr=$this->compile_expression($expression,true);
	
	//Fehler aufgetreten
	if ( $compiled_expr===false ) {
		$error='<br /><b>parse error:</b> &#123;if '.strtr($expression,array('{'=>'&#123;','}'=>'&#125;')).'&#125; is not a valid if-term!<br />';
		$error.='<?php if ( false ): ?>'; //Dummy, damit PHP korrekt bleibt
		return $error;
	}
	
	return  '<?php '.iif($elseif,'else').'if ( '.$compiled_expr.' ): ?>';
}

function compile_list($match) 
{
	$varname=$match[1];
	$varname=trim($varname);
	
	//Variablenname auf Richtigkeit prüfen
	if ( !preg_match('#^'.$this->pattern_vars.'$#',$varname) ) {
		$error='<br /><b>parse error:</b> &#123;list "'.strtr($varname,array('{'=>'&#123;','}'=>'&#125;')).'&#125; is not a valid list!"<br />';
		$error.='<?php if ( false ): foreach ( array() AS $none ): ?>'; //Dummy, damit PHP korrekt bleibt
		return $error;
	}
	
	//Listen-ID auslesen
	if ( strpos($varname,'.') ) $listid=substr(strrchr($varname,'.'),1);
	else $listid=$varname;
	
	//Kompilierte Variable
	$comp_varname=$this->get_compiled_var($varname);
	
	return '<?php if ( ('.$comp_varname.') && !is_array('.$comp_varname.') ): echo "<b>runtime error:</b> '.$varname.' is not listable!"; elseif ( is_array('.$comp_varname.') ): foreach ( '.$comp_varname.' AS $list_'.$listid.' ): $this->parsevars[\'#LIST#\'][\''.$listid.'\']=&$list_'.$listid.'; ?>';
}

function compile_repeat($match) {
	$expression=$match[1];
	$expression=trim($expression);
	
	//Expression auf Richtigkeit prüfen
	if ( !preg_match('#^(('.$this->pattern_vars.'|'.$this->pattern_number.')( *('.$this->pattern_operator.') *('.$this->pattern_vars.'|'.$this->pattern_number.'))*)$#',$expression) ) {
		return '<br /><b>parse error:</b> &#123;repeat '.strtr($expression,array('{'=>'&#123;','}'=>'&#125;')).'&#125; is not a valid repeat!<br />';
	}
	
	//Variablen ersetzen, Rest bleibt gleich
	//Strings können nicht vorkommen (durch vorherige Prüfung ausgeschlossen)
	//Es können nur Variablennamen übergeben werden => ungefährlich
	$expression=preg_replace_callback('#('.$this->pattern_vars.')#s',function($m) {return $this->get_compiled_var($m[1]);},$expression);
	
	return '<?php for ( $counter=1; $counter<=('.$expression.'); $counter++ ) : ?>';
}



/*** Template-Funktion kompilieren ***/
function compile_function($match) 
{
	$funcname=$match[1];
	$params=$match[2];

	$found = preg_split(
		"/[,\s]*\\\"([^\\\"]+)\\\"[,\s]*|" . "[,\s]*'([^']+)'[,\s]*|" . "[,\s]+/",
		$params,
		0,
		PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
	);

	//Parameter durchlaufen und Variablen konvertieren
	$parts = array();
	foreach ($found as $param)
	{
		$param = trim($param);

		//Variable konvertieren
		if (preg_match('#^' . $this->pattern_vars . '$#', $param))
		{
			$parts[] = $this->get_compiled_var($param);
			continue;
		}
		else
		{
			$parts[] = "'".$param."'";
		}
	}	
	$ret = '$apx->executePublicTemplateFunction("' . $funcname . '", array( ' . implode(', ', $parts) . ' ) );';	
	return "<?php ".$ret." ?>";
}

//Static-Befehl
function compile_static($match) {
	$varname=$match[1];
	$value=$match[2];
	return '<?php $this->assign_static("'.strtoupper($varname).'", ); ?>';
}

function compile_include_from_module($match)
{
	global $apx, $tmpl;
	
	$modulename=strtolower($match[1]);
	if( !isset($apx->modules[$modulename]) )
		return "";
	
	$incfile=$match[2];
	$incfile=trim($incfile);
	$incfile=preg_replace_callback('#/{2,}#',function($m) {return '/';},$incfile);
	$incfile=preg_replace_callback('#\\+#',function($m) {return '/';},$incfile);
	
	//Korrekten Dateipfad prüfen
	if ( !preg_match('#/?([A-Z0-9_-]+/)*[A-Z0-9_-]+.html#si',$incfile) ) {
		return '<br /><b>parse error:</b> invalid inclusion of "'. $incfile.'<br />';
	}
	
	//Releativer Include
	if ( substr($incfile,0,1)=='/' ) $incfile=substr($incfile,1);
	$incfile = $apx->tmpl->get_templatepath($modulename).$incfile;
	
	//Include registrieren
	$this->used_includes[]=$incfile;
	
	return '<?php $this->include_file(\''.addslashes($incfile).'\'); ?>';	
}


/*** Includes ***/
function compile_include($match) {
	$incfile=$match[1];
	$incfile=trim($incfile);
	$incfile=preg_replace_callback('#/{2,}#',function($m) {return '/';},$incfile);
	$incfile=preg_replace_callback('#\\+#',function($m) {return '/';},$incfile);
	
	//Korrekten Dateipfad pr�fen
	if ( !preg_match('#/?([A-Z0-9_-]+/)*[A-Z0-9_-]+.html#si',$incfile) ) {
		return '<br /><b>parse error:</b> invalid inclusion of "'. $incfile.'<br />';
	}
	
	//Releativer Include
	if ( substr($incfile,0,1)=='/' ) $incfile=substr($incfile,1);
	else $incfile=$this->cleanpath(dirname($this->filename).'/'.$incfile);
	
	//Include registrieren
	$this->used_includes[]=$incfile;
	
	return '<?php $this->include_file(\''.addslashes($incfile).'\'); ?>';
}



/*** Variable ausgeben ***/
function compile_echo($varname,$func=false) {
	$varname=trim($varname);
	$compiled_var=$this->get_compiled_var($varname);
	
	//Prüfen ob die Variable korrekt umgesetzt wurde
	if ( $compiled_var===false ) {
		return '<br /><b>parse error:</b> &#123;'.$varname.'&#125; is not a valid VAR!<br />';
	}
	
	//Modifikatoren einbinden
	if ( is_string($func) ) return '<?php echo '.$func.'('.$compiled_var.'); ?>';
	
	//Ausgabe
	return '<?php echo '.$compiled_var.'; ?>';
}



} //END CLASS


?>
