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


# USER CLASS
# ==========

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//CityMatch laden
include(BASEDIR.getmodulepath('user').'func/func.citymatch.php');


class action {

//***************************** User freischalten *****************************
function enable() {
	global $set,$apx,$tmpl,$db;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( !checkToken() ) printInvalidToken();
	else {
		$res=$db->first("SELECT username,reg_key,email FROM ".PRE."_user WHERE userid='".$_REQUEST['id']."' LIMIT 1");
		if ( $res['reg_key']!='BYADMIN' ) die('can not activate user!');
		
		$db->query("UPDATE ".PRE."_user SET reg_key='' WHERE ( userid='".$_REQUEST['id']."' AND reg_key='BYADMIN' ) LIMIT 1");
		logit('USER_ENABLE','ID #'.$_REQUEST['id']);
		
		$input=array();
		$input['USERNAME']=replace($res['username']);
		$input['WEBSITE']=$set['main']['websitename'];
		$input['URL']=HTTP_HOST.mklink('user.php','user.html');
		sendmail($res['email'],'ACTIVATION',$input);
		
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: '.get_index('user.show'));
	}
}



//***************************** User Blog *****************************
function blog() {
	global $set,$apx,$db,$html;
	$_REQUEST['userid'] = (int)$_REQUEST['userid'];
	
	//AKTIONEN
	if ( $_REQUEST['do']=='edit' ) return $this->blog_edit();
	elseif ( $_REQUEST['do']=='del' ) return $this->blog_del();
	
	$orderdef[0]='pub';
	$orderdef['title']=array('title','ASC','TITLE');
	$orderdef['pub']=array('time','DESC','COL_PUB');
	
	if ( $_REQUEST['userid'] ) {
		$col[]=array('TITLE',85,'class="title"');
		$col[]=array('COL_PUB',15,'align="center"');
	}
	else {
		$col[]=array('TITLE',65,'class="title"');
		$col[]=array('COL_PUB',15,'align="center"');
		$col[]=array('COL_OWNER',20,'align="center"');
	}
	
	//Benuternamen als Titel ausgeben
	if ( $_REQUEST['userid'] ) {
		list($username) = $db->first("SELECT username FROM ".PRE."_user WHERE userid='".$_REQUEST['userid']."' LIMIT 1");
		echo '<h2>'.$apx->lang->get('BLOGOF').' '.$username.'</h2>';
	}
	
	//ÜBERSICHT
	if ( $_REQUEST['userid'] ) $ownerfilter = " AND userid='".$_REQUEST['userid']."' ";
	else $ownerfilter = '';
	list($count) = $db->first("SELECT count(id) FROM ".PRE."_user_blog WHERE 1 ".$ownerfilter);
	pages('action.php?action=user.blog',$count);
	
	//Einträge auslesen
	$data = $db->fetch("SELECT id,userid AS owner,title,time,allowcoms FROM ".PRE."_user_blog WHERE 1 ".$ownerfilter." ".getorder($orderdef).getlimit());
	if ( count($data) ) {
		
		//Owner-Namen auslesen
		if ( !$_REQUEST['userid'] ) {
			$userids = get_ids($data,'owner');
			$usernames = $db->fetch_index("SELECT userid,username FROM ".PRE."_user WHERE userid IN (".implode(',',$userids).")",'userid');
		}
		
		foreach ( $data AS $res ) {
			++$i;
			
			$link = mklink(
				'user.php?action=blog&amp;id='.$res['owner'].'&amp;blogid='.$res['id'],
				'user,blog,'.$res['owner'].',id'.$res['id'].urlformat($res['title']).'.html',
				iif($set['main']['forcesection'],$apx->section_default,0)
			);
			
			$tabledata[$i]['COL1'] = '<a href="'.$link.'" target="_blank">'.replace($res['title']).'</a>';
			$tabledata[$i]['COL2'] = mkdate($res['time'],'<br />');
			
			if ( !$_REQUEST['userid'] ) {
				$ownername = $usernames[$res['owner']]['username'];
				$ownerprofile = mklink(
					'user.php?action=profile&amp;id='.$res['owner'],
					'user,profile,'.$res['owner'].urlformat($res['username']).'.html',
					iif($set['main']['forcesection'],$apx->section_default,0)
				);
				$tabledata[$i]['COL3']='<a href="'.$ownerprofile.'" target="_blank">'.replace($ownername).'</a>';
			}
			
			$tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'user.blog', 'userid='.$_REQUEST['userid'].'&do=edit&id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			$tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'user.blog', 'userid='.$_REQUEST['userid'].'&do=del&id='.$res['id'], $apx->lang->get('CORE_DEL'));
			
			//Kommentare + Bewertungen
			if ( $apx->is_module('comments') ) {
				$tabledata[$i]['OPTIONS'].='&nbsp;';
				list($comments)=$db->first("SELECT count(id) FROM ".PRE."_comments WHERE ( module='userblog' AND mid='".$res['id']."' )");
				if ( $comments && $apx->is_module('comments') && $res['allowcoms'] && $apx->user->has_right('comments.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('comments.gif', 'comments.show', 'module=userblog&mid='.$res['id'], $apx->lang->get('COMMENTS').' ('.$comments.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	
	orderstr($orderdef,'action.php?action=user.blog&amp;userid='.$_REQUEST['userid']);
	save_index($_SERVER['REQUEST_URI']);
}


//EDIT
function blog_edit() {
	global $set,$apx,$db;
	$_REQUEST['userid'] = (int)$_REQUEST['userid'];
	$_REQUEST['id'] = (int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send'] ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['text'] ) message('back');
		else {
			$db->dupdate(PRE.'_user_blog','title,text',"WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('USER_BLOG_EDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('user.blog'));
		}
	}
	else {
		list($_POST['title'],$_POST['text']) = $db->first("SELECT title,text FROM ".PRE."_user_blog WHERE id='".$_REQUEST['id']."' LIMIT 1");
		
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('USERID',$_REQUEST['userid']);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		
		$apx->tmpl->parse('blog_edit');
	}
}


//DEL
function blog_del() {
	global $set,$apx,$db;
	$_REQUEST['userid'] = (int)$_REQUEST['userid'];
	$_REQUEST['id'] = (int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send'] ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("DELETE FROM ".PRE."_user_blog WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('USER_BLOG_DEL','ID #'.$_REQUEST['id']);
			printJSReload();
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_user_blog WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('blogdel',array('ID'=>$_REQUEST['id'],'USERID'=>$_REQUEST['userid']));
	}
}



//***************************** User Gästebuch *****************************
function guestbook() {
	global $set,$apx,$db,$html;
	$_REQUEST['userid'] = (int)$_REQUEST['userid'];
	
	//AKTIONEN
	if ( $_REQUEST['do']=='edit' ) return $this->guestbook_edit();
	elseif ( $_REQUEST['do']=='del' ) return $this->guestbook_del();
	
	$orderdef[0]='time';
	$orderdef['name']=array('username','ASC','COL_NAME');
	$orderdef['time']=array('time','DESC','COL_ADDTIME');
	
	if ( $_REQUEST['userid'] ) {
		$col[]=array('COL_NAME',20,'class="title"');
		$col[]=array('COL_TEXT',65,'');
		$col[]=array('COL_ADDTIME',15,'align="center"');
	}
	else {
		$col[]=array('COL_NAME',20,'class="title"');
		$col[]=array('COL_TEXT',45,'');
		$col[]=array('COL_ADDTIME',15,'align="center"');
		$col[]=array('COL_OWNER',20,'align="center"');
	}
	
	//Benuternamen als Titel ausgeben
	if ( $_REQUEST['userid'] ) {
		list($username) = $db->first("SELECT username FROM ".PRE."_user WHERE userid='".$_REQUEST['userid']."' LIMIT 1");
		echo '<h2>'.$apx->lang->get('GUESTBOOKOF').' '.$username.'</h2>';
	}
	
	if ( $_REQUEST['userid'] ) $ownerfilter = " AND owner='".$_REQUEST['userid']."' ";
	else $ownerfilter = '';
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_user_guestbook WHERE 1 ".$ownerfilter);
	pages('action.php?action=guestbook.show&amp;sortby='.$_REQUEST['sortby'],$count);
	
	$data=$db->fetch("SELECT a.id,a.text,a.time,a.owner,b.username FROM ".PRE."_user_guestbook AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE 1 ".$ownerfilter." ".getorder($orderdef).getlimit());	
	if ( count($data) ) {
		
		//Owner-Namen auslesen
		if ( !$_REQUEST['userid'] ) {
			$userids = get_ids($data,'owner');
			$usernames = $db->fetch_index("SELECT userid,username FROM ".PRE."_user WHERE userid IN (".implode(',',$userids).")",'userid');
		}
		
		$i = 0;
		foreach ( $data AS $res ) {
			++$i;
			
			$tabledata[$i]['COL1']=replace($res['username']);
			$tabledata[$i]['COL2']=shorttext($res['text'],50);
			$tabledata[$i]['COL3']=mkdate($res['time'],'<br />');
			
			if ( !$_REQUEST['userid'] ) {
				$ownername = $usernames[$res['owner']]['username'];
				$ownerprofile = mklink(
					'user.php?action=guestbook&amp;id='.$res['owner'],
					'user,guestbook,'.$res['owner'].',1.html',
					iif($set['main']['forcesection'],$apx->section_default,0)
				);
				$tabledata[$i]['COL4']='<a href="'.$ownerprofile.'" target="_blank">'.replace($ownername).'</a>';
			}
			
			$tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'user.guestbook', 'userid='.$_REQUEST['userid'].'&do=edit&id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			$tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'user.guestbook', 'userid='.$_REQUEST['userid'].'&do=del&id='.$res['id'], $apx->lang->get('CORE_DEL'));
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col,$factions);
	
	orderstr($orderdef,'action.php?action=user.guestbook&amp;userid='.$_REQUEST['userid']);
	save_index($_SERVER['REQUEST_URI']);
}


//EDIT
function guestbook_edit() {
	global $set,$apx,$db;
	$_REQUEST['userid'] = (int)$_REQUEST['userid'];
	$_REQUEST['id'] = (int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	$res = $db->first("SELECT a.*,b.username FROM ".PRE."_user_guestbook AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE id='".$_REQUEST['id']."' LIMIT 1");
	
	if ( $_POST['send'] ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['id'] || !$_POST['text'] ) infoNotComplete();
		else {
			$db->dupdate(PRE.'_user_guestbook','title,text',"WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('USER_GUESTBOOK_EDIT',"ID #".$_REQUEST['id']);
			printJSRedirect(get_index('user.guestbook'));
		}
	}
	else {
		$_POST['text'] = $res['text'];
		$_POST['title'] = $res['title'];
	
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('USERID',$res['userid']);
		$apx->tmpl->assign('USERNAME',replace($res['username']));
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		
		$apx->tmpl->parse('guestbook_edit');
	}
}


//DEL
function guestbook_del() {
	global $set,$apx,$db;
	$_REQUEST['userid'] = (int)$_REQUEST['userid'];
	$_REQUEST['id'] = (int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( $_POST['send'] ) {
		$db->query("DELETE FROM ".PRE."_user_guestbook WHERE id='".$_REQUEST['id']."' LIMIT 1");
		logit('USER_GUESTBOOK_DEL','ID #'.$_REQUEST['id']);
		printJSReload();
	}
	else {
		list($title) = $db->first("SELECT u.username FROM ".PRE."_user_guestbook AS g LEFT JOIN ".PRE."_user AS u USING(userid) WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('guestbookdel',array('ID'=>$_REQUEST['id'],'USERID'=>$_REQUEST['userid']));
	}
}



//***************************** User Galerien *****************************
function gallery() {
	global $set,$apx,$db,$html;
	$_REQUEST['userid'] = (int)$_REQUEST['userid'];
	
	//AKTIONEN
	if ( $_REQUEST['do']=='edit' ) return $this->gallery_edit();
	elseif ( $_REQUEST['do']=='del' ) return $this->gallery_del();
	elseif ( $_REQUEST['do']=='pics' ) return $this->gallery_pics();
	
	$orderdef[0]='update';
	$orderdef['name']=array('title','ASC','COL_TITLE');
	$orderdef['addtime']=array('addtime','DESC','COL_ADDTIME');
	$orderdef['update']=array('lastupdate','DESC','COL_LASTUPDATE');
	
	if ( $_REQUEST['userid'] ) {
		$col[]=array('COL_TITLE',60,'');
		$col[]=array('COL_PICS',10,'align="center"');
		$col[]=array('COL_ADDTIME',15,'align="center"');
		$col[]=array('COL_LASTUPDATE',15,'align="center"');
	}
	else {
		$col[]=array('COL_TITLE',40,'class="title"');
		$col[]=array('COL_PICS',10,'align="center"');
		$col[]=array('COL_ADDTIME',15,'align="center"');
		$col[]=array('COL_LASTUPDATE',15,'align="center"');
		$col[]=array('COL_OWNER',20,'align="center"');
	}
	
	//Benuternamen als Titel ausgeben
	if ( $_REQUEST['userid'] ) {
		list($username) = $db->first("SELECT username FROM ".PRE."_user WHERE userid='".$_REQUEST['userid']."' LIMIT 1");
		echo '<h2>'.$apx->lang->get('GALLERYOF').' '.$username.'</h2>';
	}
	
	if ( $_REQUEST['userid'] ) $ownerfilter = " AND owner='".$_REQUEST['userid']."' ";
	else $ownerfilter = '';
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_user_gallery WHERE 1 ".$ownerfilter);
	pages('action.php?action=gallery.show&amp;sortby='.$_REQUEST['sortby'],$count);
	
	$data=$db->fetch("SELECT id,title,addtime,lastupdate,owner,allowcoms FROM ".PRE."_user_gallery WHERE 1 ".$ownerfilter." ".getorder($orderdef).getlimit());
	if ( count($data) ) {
		
		//Owner-Namen auslesen
		if ( !$_REQUEST['userid'] ) {
			$userids = get_ids($data,'owner');
			$usernames = $db->fetch_index("SELECT userid,username FROM ".PRE."_user WHERE userid IN (".implode(',',$userids).")",'userid');
		}
		
		foreach ( $data AS $res ) {
			++$i;
			
			//Anzahl Bilder
			list($pics) = $db->first("SELECT count(*) FROM ".PRE."_user_pictures WHERE galid='".$res['id']."'");
			
			$link = mklink(
				'user.php?action=gallery&amp;id='.$res['owner'].'&amp;galid='.$res['id'],
				'user,gallery,'.$res['owner'].','.$res['id'].',0.html',
				iif($set['main']['forcesection'],$apx->section_default,0)
			);
			
			$tabledata[$i]['COL1']='<a href="'.$link.'" target="_blank">'.replace($res['title']).'</a>';
			$tabledata[$i]['COL2']=number_format($pics,0,',','.');
			$tabledata[$i]['COL3']=mkdate($res['addtime'],'<br />');
			$tabledata[$i]['COL4']=mkdate($res['lastupdate'],'<br />');
			
			if ( !$_REQUEST['userid'] ) {
				$ownername = $usernames[$res['owner']]['username'];
				$ownerprofile = mklink(
					'user.php?action=profile&amp;id='.$res['owner'],
					'user,profile,'.$res['owner'].urlformat($res['username']).'.html',
					iif($set['main']['forcesection'],$apx->section_default,0)
				);
				$tabledata[$i]['COL5']='<a href="'.$ownerprofile.'" target="_blank">'.replace($ownername).'</a>';
			}
			
			$tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'user.gallery', 'userid='.$_REQUEST['userid'].'&do=edit&id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			$tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'user.gallery', 'userid='.$_REQUEST['userid'].'&do=del&id='.$res['id'], $apx->lang->get('CORE_DEL'));
			$tabledata[$i]['OPTIONS'] .= optionHTML('pic.gif', 'user.gallery', 'userid='.$_REQUEST['userid'].'&do=pics&galid='.$res['id'], $apx->lang->get('SHOWPICS'));  '<a href="action.php?action=user.gallery&amp;userid='.$_REQUEST['userid'].'&amp;do=pics&amp;galid='.$res['id'].'"><img src="design/pic.gif" title="'.$apx->lang->get('SHOWPICS').'" alt="'.$apx->lang->get('SHOWPICS').'" style="vertical-align:middle;" /></a>';
			
			//Kommentare + Bewertungen
			if ( $apx->is_module('comments') ) {
				$tabledata[$i]['OPTIONS'].='&nbsp;';
				list($comments)=$db->first("SELECT count(id) FROM ".PRE."_comments WHERE ( module='usergallery' AND mid='".$res['id']."' )");
				if ( $comments && $apx->is_module('comments') && $res['allowcoms'] && $apx->user->has_right('comments.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('comments.gif', 'comments.show', 'module=usergallery&mid='.$res['id'], $apx->lang->get('COMMENTS').' ('.$comments.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col,$factions);
	
	orderstr($orderdef,'action.php?action=user.gallery&amp;userid='.$_REQUEST['userid']);
	save_index($_SERVER['REQUEST_URI']);
}


//EDIT
function gallery_edit() {
	global $set,$apx,$db;
	$_REQUEST['userid'] = (int)$_REQUEST['userid'];
	$_REQUEST['id'] = (int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send'] ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] ) message('back');
		else {
			$db->dupdate(PRE.'_user_gallery','title,description,password',"WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('USER_GALLERY_EDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('user.gallery'));
		}
	}
	else {
		list($_POST['title'],$_POST['description'],$_POST['password']) = $db->first("SELECT title,description,password FROM ".PRE."_user_gallery WHERE id='".$_REQUEST['id']."' LIMIT 1");
		
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('USERID',$_REQUEST['userid']);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('DESCRIPTION',compatible_hsc($_POST['description']));
		$apx->tmpl->assign('PASSWORD',compatible_hsc($_POST['password']));
		$apx->tmpl->parse('gallery_edit');
	}
}


//DEL
function gallery_del() {
	global $set,$apx,$db;
	$_REQUEST['userid'] = (int)$_REQUEST['userid'];
	$_REQUEST['id'] = (int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( $_POST['send'] ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("DELETE FROM ".PRE."_user_gallery WHERE id='".$_REQUEST['id']."' LIMIT 1");
			
			//Bilder löschen
			if ( $db->affected_rows() ) {
				require(BASEDIR.'lib/class.mediamanager.php');
				$mm = new mediamanager();
				
				$data = $db->fetch("SELECT thumbnail,picture FROM ".PRE."_user_pictures WHERE galid='".$_REQUEST['id']."'");
				$db->query("DELETE FROM ".PRE."_user_pictures WHERE galid='".$_REQUEST['id']."'");
				if ( count($data) ) {
					foreach ( $data AS $res ) {
						$picture = $res['picture'];
						$thumbnail = $res['thumbnail'];
						if ( $picture && file_exists(BASEDIR.getpath('uploads').$picture) ) $mm->deletefile($picture);
						if ( $thumbnail && file_exists(BASEDIR.getpath('uploads').$thumbnail) ) $mm->deletefile($thumbnail);
					}
				}
				
				//Ordner löschen
				$mm->deletedir('user/gallery-'.$_REQUEST['id']);
			}
			
			logit('USER_GALLERY_DEL','ID #'.$_REQUEST['id']);
			printJSReload();
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_user_gallery WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('gallerydel',array('ID'=>$_REQUEST['id'],'USERID'=>$_REQUEST['userid']));
	}
}


//PICS
function gallery_pics() {
	global $set,$apx,$db,$html;
	$_REQUEST['userid'] = (int)$_REQUEST['userid'];
	$_REQUEST['galid'] = (int)$_REQUEST['galid'];
	if ( !$_REQUEST['galid'] ) die('missing GALID!');
	
	//AKTIONEN
	if ( $_REQUEST['do2']=='edit' ) return $this->gallery_pics_edit();
	elseif ( $_REQUEST['do2']=='del' ) return $this->gallery_pics_del();
	
	$orderdef[0]='time';
	$orderdef['time']=array('id','DESC','COL_ADDTIME');
	$orderdef['caption']=array('caption','ASC','COL_CAPTION');
	
	$col[]=array('COL_THUMBNAIL',20,'align="center"');
	$col[]=array('COL_CAPTION',80,'');

	list($id,$title)=$db->first("SELECT id,title FROM ".PRE."_user_gallery WHERE id='".$_REQUEST['galid']."' LIMIT 1");
	if ( !$id ) return;
	echo'<h2>'.$apx->lang->get('GALLERY').': '.$title.'</h2>';	
	
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_user_pictures WHERE galid='".$_REQUEST['galid']."'");
	pages('action.php?action=user.gallery&amp;userid='.$_REQUEST['userid'].'&amp;do=pics&amp;galid='.$_REQUEST['galid'].'&amp;sortby='.$_REQUEST['sortby'],$count);
	
	//Bilder auslesen
	$data=$db->fetch("SELECT * FROM ".PRE."_user_pictures WHERE galid='".$_REQUEST['galid']."' ".getorder($orderdef).getlimit());
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			$caption=shorttext(strip_tags($res['caption']),50);
			$tabledata[$i]['COL1']='<a href="../'.getpath('uploads').$res['picture'].'" target="_blank"><img src="../'.getpath('uploads').$res['thumbnail'].'" alt="thumbnail" /></a>';
			$tabledata[$i]['COL2']=iif($caption,$caption,'&nbsp;');
			
			$tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('edit.gif', 'user.gallery', 'userid='.$_REQUEST['userid'].'&do=pics&galid='.$_REQUEST['galid'].'&do2=edit&id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			$tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'user.gallery', 'userid='.$_REQUEST['userid'].'&do=pics&galid='.$_REQUEST['galid'].'&do2=del&id='.$res['id'], $apx->lang->get('CORE_DEL'));
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	
	orderstr($orderdef,'action.php?action=user.gallery&amp;userid='.$_REQUEST['userid'].'&amp;do=pics&amp;galid='.$_REQUEST['galid']);
	save_index($_SERVER['REQUEST_URI']);
}


//PICS EDIT
function gallery_pics_edit() {
	global $set,$apx,$db,$html;
	$_REQUEST['userid'] = (int)$_REQUEST['userid'];
	$_REQUEST['galid'] = (int)$_REQUEST['galid'];
	if ( !$_REQUEST['galid'] ) die('missing GALID!');
	$_REQUEST['id'] = (int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send'] ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			list($galid) = $db->first("SELECT id FROM ".PRE."_user_gallery WHERE id='".$_REQUEST['galid']."' LIMIT 1");
			if ( !$galid ) die('access denied!');
			$db->query("UPDATE ".PRE."_user_pictures SET caption='".addslashes($_POST['caption'])."' WHERE galid='".$_REQUEST['galid']."' AND id='".$_REQUEST['id']."'");
			logit('USER_GALLERYPIC_EDIT','ID #'.$_REQUEST['id']);
			printJSReload();
		}
	}
	else {
		list($caption) = $db->first("SELECT caption FROM ".PRE."_user_pictures WHERE galid='".$_REQUEST['galid']."' AND id='".$_REQUEST['id']."'");
		$input = array(
			'ID'=>$_REQUEST['id'],
			'GALID'=>$_REQUEST['galid'],
			'USERID'=>$_REQUEST['userid'],
			'CAPTION'=>compatible_hsc($caption)
		);
		tmessageOverlay('gallerypicedit',$input);
	}
}


//PICS DEL
function gallery_pics_del() {
	global $set,$apx,$db,$html;
	$_REQUEST['userid'] = (int)$_REQUEST['userid'];
	$_REQUEST['galid'] = (int)$_REQUEST['galid'];
	if ( !$_REQUEST['galid'] ) die('missing GALID!');
	$_REQUEST['id'] = (int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send'] ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			list($galid) = $db->first("SELECT id FROM ".PRE."_user_gallery WHERE id='".$_REQUEST['galid']."' LIMIT 1");
			if ( !$galid ) die('access denied!');
			list($picture,$thumbnail) = $db->first("SELECT picture,thumbnail FROM ".PRE."_user_pictures WHERE id='".$_REQUEST['id']."' AND galid='".$_REQUEST['galid']."' LIMIT 1");
			require_once(BASEDIR.'lib/class.mediamanager.php');
			$mm=new mediamanager;
			if ( $picture && file_exists(BASEDIR.getpath('uploads').$picture) ) $mm->deletefile($picture);
			if ( $thumbnail && file_exists(BASEDIR.getpath('uploads').$thumbnail) ) $mm->deletefile($thumbnail);
			$db->query("DELETE FROM ".PRE."_user_pictures WHERE id='".$_REQUEST['id']."' AND galid='".$_REQUEST['galid']."' LIMIT 1");
			logit('USER_GALLERYPIC_DEL','ID #'.$_REQUEST['id']);
			printJSReload();
		}
	}
	else {
		$input = array(
			'ID'=>$_REQUEST['id'],
			'GALID'=>$_REQUEST['galid'],
			'USERID'=>$_REQUEST['userid']
		);
		tmessageOverlay('gallerypicdel',$input);
	}
}



//////////////////////////////////////////////////////////////////////////////////////////////////////


//***************************** Benutzergruppen zeigen *****************************
function gshow() {
	global $set,$apx,$db,$html;
	
	quicklink('user.gadd');
	
	$orderdef[0]='group';
	$orderdef['group']=array('name','ASC','COL_GROUP');
	
	$col[]=array('COL_GROUP',75,'class="title"');
	$col[]=array('COL_USERS',25,'align="center"');
	
	$data=$db->fetch("SELECT a.*,count(b.groupid) AS count FROM ".PRE."_user_groups AS a LEFT JOIN ".PRE."_user AS b USING(groupid) GROUP BY a.groupid ".getorder($orderdef));
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$obj;
			
			$tabledata[$obj]['COL1']=replace($res['name']);
			$tabledata[$obj]['COL2']=replace($res['count']);
			
			//Optionen
			if ( $apx->user->has_right('user.gedit') ) $tabledata[$obj]['OPTIONS'].=optionHTML('edit.gif', 'user.gedit', 'id='.$res['groupid'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$obj]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $res['groupid']>3 && $apx->user->has_right('user.gdel') && !$res['count'] ) $tabledata[$obj]['OPTIONS'].=optionHTMLOverlay('del.gif', 'user.gdel', 'id='.$res['groupid'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$obj]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('user.gclean') && $res['count'] ) $tabledata[$obj]['OPTIONS'].=optionHTMLOverlay('clean.gif', 'user.gclean', 'id='.$res['groupid'], $apx->lang->get('CLEAN'));
			else $tabledata[$obj]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	
	orderstr($orderdef,'action.php?action=user.gshow');
	save_index($_SERVER['REQUEST_URI']);
}


//***************************** Benutzergruppe hinzufügen *****************************
function gadd() {
	global $set,$apx,$db;
	
	if ( $_POST['send']==1 ) {
		if ( !in_array($_POST['gtype'],array('admin','indiv','public','guest')) ) $_POST['gtype']='public';
		list($checkname)=$db->first("SELECT groupid FROM ".PRE."_user_groups WHERE name='".addslashes($_POST['name'])."' LIMIT 1");
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['name'] || ( $_POST['gtype']=='indiv' && !$_POST['right'] ) ) infoNotComplete();
		elseif ( count($apx->sections) && ( !count($_POST['section_access']) || ( $_POST['gtype']=='indiv' && !count($_POST['section_access']) ) ) ) infoNotComplete();
		elseif ( $checkname ) info($apx->lang->get('INFO_GROUPEXISTS'));
		else {
		
			//INDIV
			if ( $_POST['gtype']=='indiv' ) {
				$newr=array();
				$newsp=array();
				
				//Rechte
				if ( is_array($_POST['right']) ) {
					foreach ( $_POST['right'] AS $theaction => $trash ) {
						$newr[]=$theaction;
					} 
				$ins_rights=serialize($newr);
				}
				
				//Sonderrechte
				if ( is_array($_POST['spright']) ) {
					foreach ( $_POST['spright'] AS $theaction => $trash ) {
						if ( !in_array($theaction,$newr) ) continue;
						$newsp[]=$theaction; 
					}
				$ins_sprights=serialize($newsp);
				}
				
				//Sektionen
				if ( $_POST['section_access'][0]=='all' ) $section_access='all';
				else $section_access=serialize($_POST['section_access']);
			}
			
			//PUBLIC -> Nur Sektionen
			else {
				$_POST['gtype']='public';
				$section_access=serialize(array());
				if ( $_POST['section_access'][0]=='all' ) $section_access='all';
				else $section_access=serialize($_POST['section_access']);
			}
			
			$db->query("INSERT INTO ".PRE."_user_groups VALUES ('','".addslashes($_POST['name'])."','".addslashes($_POST['gtype'])."','".addslashes($ins_rights)."','".addslashes($ins_sprights)."','".addslashes($section_access)."')");
			logit('USER_GADD','ID #'.$db->insert_id());
			printJSRedirect('action.php?action=user.gshow');
		}
	}
	else {
		$_POST['gtype']='indiv';
		
		$apx->lang->dropall('expl');
		
		//Rechte
		foreach ( $apx->modules AS $module => $trash ) {
			foreach ( $apx->actions[$module] AS $action => $info ) {
				//Standardrechte filtern
				if ( $info[3] ) continue;
				
				++$obj;
				$actiondata[$obj]['ACTION']=$module.'.'.$action;
				$actiondata[$obj]['TITLE']=$apx->lang->get('TITLE_'.strtoupper($module).'_'.strtoupper($action));
				$actiondata[$obj]['ID']=$module.'.'.$action;
				$actiondata[$obj]['RIGHT']=iif($_POST['right'][$module.'.'.$action],1,0);
				$actiondata[$obj]['SPRIGHT']=iif($_POST['spright'][$module.'.'.$action],1,0);
				$actiondata[$obj]['HASSP']=iif($info[0],1,0);
				$actiondata[$obj]['INFO']=$apx->lang->get('EXPL_'.strtoupper($module).'_'.strtoupper($action));
			}
			
			++$mobj;
			$moduledata[$mobj]['TITLE']=$apx->lang->get('MODULENAME_'.strtoupper($module));
			$moduledata[$mobj]['ID']=$module;
			$moduledata[$mobj]['ACTION']=$actiondata;
			
			$actiondata=array();
		}
		
		//Sektionen
		if ( is_array($apx->sections) && count($apx->sections) ) {
			if ( !isset($_POST['section_access']) || $_POST['section_access'][0]=='all' ) $_POST['section_access']=array('all');
			$section_access='<option value="all"'.iif($_POST['section_access'][0]=='all',' selected="selected"').' style="font-weight:bold;">'.$apx->lang->get('ALLSEC').'</option>';
			
			foreach ( $apx->sections AS $id => $info ) {
				$section_access.='<option value="'.$id.'"'.iif(in_array($id,$_POST['section_access']),' selected="selected"').'>'.replace($info['title']).'</option>';
			}
		}
		
		$apx->tmpl->assign('NAME',compatible_hsc($_POST['name']));
		$apx->tmpl->assign('GTYPE',$_POST['gtype']);
		$apx->tmpl->assign('SECTION_ACCESS',$section_access);
		$apx->tmpl->assign('MODULE',$moduledata);
		$apx->tmpl->assign('ACTION','gadd');
		
		$apx->tmpl->parse('gadd_gedit');
	}
}


//***************************** Benutzergruppe bearbeiten *****************************
function gedit() {
	global $set,$apx,$db;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	//Standard-Gruppen
	if ( $_REQUEST['id']==1 ) $_POST['gtype']='admin';
	if ( $_REQUEST['id']==2 ) $_POST['gtype']='public';
	if ( $_REQUEST['id']==3 ) $_POST['gtype']='guest';
	
	
	if ( $_POST['send']==1 ) {
		if ( !in_array($_POST['gtype'],array('admin','indiv','public','guest')) ) $_POST['gtype']='public';
		list($checkname)=$db->first("SELECT groupid FROM ".PRE."_user_groups WHERE ( name='".addslashes($_POST['name'])."' AND groupid!='".$_REQUEST['id']."' ) LIMIT 1");
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['name'] || ( $_POST['gtype']=='indiv' && !$_POST['right'] ) ) infoNotComplete();
		elseif ( count($apx->sections) && ( !count($_POST['section_access']) || ( $_POST['gtype']=='indiv' && !count($_POST['section_access']) ) ) ) infoNotComplete();
		elseif ( $checkname ) info($apx->lang->get('INFO_GROUPEXISTS'));
		else {
			
			//ADMIN
			if ( $_POST['gtype']=='admin' ) {
				$section_access='all';
			}
			
			//INDIV
			if ( $_POST['gtype']=='indiv' ) {
				$newr=array();
				$newsp=array();
				
				//Rechte
				if ( is_array($_POST['right']) ) {
					foreach ( $_POST['right'] AS $theaction => $trash ) {
						$newr[]=$theaction;
					} 
				$ins_rights=serialize($newr);
				}
				
				//Sonderrechte
				if ( is_array($_POST['spright']) ) {
					foreach ( $_POST['spright'] AS $theaction => $trash ) {
						if ( !in_array($theaction,$newr) ) continue;
						$newsp[]=$theaction; 
					}
				$ins_sprights=serialize($newsp);
				}
				
				//Sektionen
				if ( $_POST['section_access'][0]=='all' ) $section_access='all';
				else $section_access=serialize($_POST['section_access']);
			}
			
			//GÄSTE + PUBLIC -> Nur Sektionen
			else {
				$section_access=serialize(array());
				if ( $_POST['section_access'][0]=='all' ) $section_access='all';
				else $section_access=serialize($_POST['section_access']);
			}
			
			$db->query("UPDATE ".PRE."_user_groups SET name='".addslashes($_POST['name'])."',gtype='".addslashes($_POST['gtype'])."',rights='".$ins_rights."',sprights='".$ins_sprights."',section_access='".$section_access."' WHERE groupid='".$_REQUEST['id']."'");
			logit('USER_GEDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('user.gshow'));
		}
	}
	
	//Erster Durchlauf!
	else {
		$res=$db->first("SELECT * FROM ".PRE."_user_groups WHERE groupid='".$_REQUEST['id']."'");
		$_POST['name']=$res['name'];
		$_POST['gtype']=$res['gtype'];
		
		if ( $res['section_access']=='all' ) $_POST['section_access'][0]='all';
		else $_POST['section_access']=unserialize($res['section_access']);
		
		if ( $res['gtype']=='indiv' ) {
			$rights=unserialize($res['rights']);
			$sprights=unserialize($res['sprights']);
			foreach ( $rights AS $right ) $_POST['right'][$right]=true;
			if ( is_array($sprights) ) foreach ( $sprights AS $spright ) $_POST['spright'][$spright]=true;
		}
		
		
		$apx->lang->dropall('expl');
		
		foreach ( $apx->modules AS $module => $trash ) {
			foreach ( $apx->actions[$module] AS $action => $info ) {
				//Standardrechte filtern
				if ( $info[3] ) continue;
				
				++$obj;
				$actiondata[$obj]['ACTION']=$module.".".$action;
				$actiondata[$obj]['TITLE']=$apx->lang->get('TITLE_'.strtoupper($module).'_'.strtoupper($action));
				$actiondata[$obj]['ID']=$module.'.'.$action;
				$actiondata[$obj]['RIGHT']=iif($_POST['right'][$module.'.'.$action],1,0);
				$actiondata[$obj]['SPRIGHT']=iif($_POST['spright'][$module.'.'.$action],1,0);
				$actiondata[$obj]['HASSP']=iif($info[0],1,0);
				$actiondata[$obj]['INFO']=$apx->lang->get('EXPL_'.strtoupper($module).'_'.strtoupper($action));
			}
			
			++$mobj;
			$moduledata[$mobj]['TITLE']=$apx->lang->get('MODULENAME_'.strtoupper($module));
			$moduledata[$mobj]['ID']=$module;
			$moduledata[$mobj]['ACTION']=$actiondata;
			
			$actiondata=array();
		}
		
		//Sektionen
		if ( is_array($apx->sections) && count($apx->sections) ) {
			if ( !isset($_POST['section_access']) || $_POST['section_access'][0]=='all' ) $_POST['section_access']=array('all');
			$section_access='<option value="all"'.iif($_POST['section_access'][0]=='all',' selected="selected"').' style="font-weight:bold;">'.$apx->lang->get('ALLSEC').'</option>';
			
			foreach ( $apx->sections AS $id => $info ) {
				$section_access.='<option value="'.$id.'"'.iif(in_array($id,$_POST['section_access']),' selected="selected"').'>'.replace($info['title']).'</option>';
			}
		}
		
		$apx->tmpl->assign('NAME',compatible_hsc($_POST['name']));
		$apx->tmpl->assign('GTYPE',$_POST['gtype']);
		$apx->tmpl->assign('SECTION_ACCESS',$section_access);
		$apx->tmpl->assign('MODULE',$moduledata);
		$apx->tmpl->assign('ACTION','gedit');
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		
		$apx->tmpl->parse('gadd_gedit');
	}
}



//***************************** Benutzergruppe leeren + löschen *****************************
function gclean() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		elseif ( $_POST['moveto'] ) {
			$db->query("UPDATE ".PRE."_user SET groupid='".intval($_POST['moveto'])."' WHERE groupid='".$_REQUEST['id']."'");
			logit('USER_GCLEAN',"ID #".$_REQUEST['id']);
			
			//Kategorie löschen
			if ( $_POST['delgroup'] && $_REQUEST['id']>3 ) {
				$db->query("DELETE FROM ".PRE."_user_groups WHERE groupid='".$_REQUEST['id']."' LIMIT 1");
				logit('USER_GDEL',"ID #".$_REQUEST['id']);
			}
			
			printJSRedirect(get_index('user.gshow'));
			return;
		}
	}
	
	//Andere Gruppen auflisten
	$data=$db->fetch("SELECT groupid,name FROM ".PRE."_user_groups WHERE groupid!='".$_REQUEST['id']."' ORDER BY name ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			$grouplist.='<option value="'.$res['groupid'].'" '.iif($_POST['moveto']==$res['groupid'],' selected="selected"').'>'.replace($res['name']).'</option>';
		}
	}
	
	list($title) = $db->first("SELECT username FROM ".PRE."_user WHERE userid='".$_REQUEST['id']."' LIMIT 1");
	$apx->tmpl->assign('ID',$_REQUEST['id']);
	$apx->tmpl->assign('TITLE', compatible_hsc($title));
	$apx->tmpl->assign('DELGROUP',(int)$_POST['delgroup']);
	$apx->tmpl->assign('GROUPLIST',$grouplist);
	$apx->tmpl->assign('DELETEABLE',$_REQUEST['id']>3);
	
	tmessageOverlay('gclean');
}



//***************************** Benutzergruppe löschen *****************************
function gdel() {
	global $set,$apx,$db;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( $_REQUEST['id']<=3 ) die('can not delete group!');
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	list($count)=$db->first("SELECT count(userid) FROM ".PRE."_user WHERE groupid='".$_REQUEST['id']."'");
	if ( $count ) die('usergroup is still in use!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("DELETE FROM ".PRE."_user_groups WHERE groupid='".$_REQUEST['id']."' LIMIT 1");
			logit('USER_GDEL','ID #'.$_REQUEST['id']);
			printJSReload();
		}
	}
	else {
		list($title) = $db->first("SELECT name FROM ".PRE."_user_groups WHERE groupid='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		$input['ID']=$_REQUEST['id'];
		tmessageOverlay('deltitle',$input,'/');
	}
}



//////////////////////////////////////////////////////////////////////////////////////////////////////


//***************************** Eigenes Benutzerprofil *****************************
function myprofile() {
	global $set,$apx,$db;
	
	//Weiterleisten auf Loginseite, wenn nicht angemeldet
	if ( !$apx->user->info['userid'] ) {
		header('Location: index.php');
	}
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['username'] || ( ( $_POST['pwd1'] || $_POST['pwd2'] ) && ( !$_POST['pwd1'] || !$_POST['pwd2'] ) ) || !$_POST['email'] ) infoNotComplete();
		elseif ( $_POST['pwd1']!=$_POST['pwd2'] ) info($apx->lang->get('INFO_PWNOMATCH'));
		elseif ( strlen($_POST['signature'])>$set['user']['sigmaxlen'] ) info($apx->lang->get('INFO_SIGTOOLONG'));
		elseif ( !checkmail($_POST['email']) ) info($apx->lang->get('INFO_NOMAIL'));
		else {
			if ( substr($_POST['homepage'],0,4)=='www.' ) $_POST['homepage']='http://'.$_POST['homepage'];
			
			if ( $_POST['pwd1'] ) {
				$_POST['salt']=random_string();
				$_POST['password']=md5(md5($_POST['pwd1']).$_POST['salt']);
			}
			
			if ( $_POST['bd_day'] && $_POST['bd_mon'] && $_POST['bd_year'] ) $_POST['birthday']=sprintf('%02d-%02d-%04d',$_POST['bd_day'],$_POST['bd_mon'],$_POST['bd_year']);
			elseif ( $_POST['bd_day'] && $_POST['bd_day'] ) $_POST['birthday']=sprintf('%02d-%02d',$_POST['bd_day'],$_POST['bd_mon']);
			else $_POST['birthday']='';
			
			//Location bestimmen
			$_POST['locid'] = user_get_location($_POST['plz'],$_POST['city'],$_POST['country']);
			
			$db->dupdate(PRE.'_user',iif($_POST['pwd1'],'password,salt,').'username,email,homepage,icq,aim,yim,msn,skype,realname,gender,birthday,city,plz,country,locid,interests,work,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10,signature,pub_lang,pub_invisible,pub_hidemail,pub_poppm,pub_usegb,pub_gbmail,pub_profileforfriends,pub_showbuddies,pub_theme,admin_lang,admin_editor'.iif($apx->is_module('forum'), ',forum_autosubscribe'),"WHERE userid='".$apx->user->info['userid']."'");
			logit('USER_MYPROFILE');
			
			if ( $_POST['pwd1'] ) {
				$apx->session->destroy();
				setcookie($set['main']['cookie_pre'].'_admin_userid',0,time()-99999,'/');
				setcookie($set['main']['cookie_pre'].'_admin_password',0,time()-99999,'/');
				unset(
					$_COOKIE[$set['main']['cookie_pre'].'_admin_userid'],
					$_COOKIE[$set['main']['cookie_pre'].'_admin_password']
				);
			}
			printJSRedirect('action.php?action=user.myprofile');
		}
	}
	
	//Erster Durchlauf
	else {
		$ex=array('userid','password','birthday','reg_time','reg_email','lastonline','lastactive');
		foreach ( $apx->user->info AS $key => $val ) {
			if ( in_array($key,$ex) ) continue;
			$_POST[$key]=$val;
		}
		
		list($_POST['bd_day'],$_POST['bd_mon'],$_POST['bd_year'])=explode('-',$apx->user->info['birthday']);
		
		//Sprache
		foreach ( $apx->languages AS $id => $name ) {
			$lang_admin.='<option value="'.$id.'"'.iif($_POST['admin_lang']==$id,' selected="selected"').'>'.$name.'</option>';
			$lang_pub.='<option value="'.$id.'"'.iif($_POST['pub_lang']==$id,' selected="selected"').'>'.$name.'</option>';
		}
		
		//Themes
		$handle=opendir(BASEDIR.getpath('tmpldir'));
		while ( $file=readdir($handle) ) {
			if ( $file=='.' || $file=='..' || !is_dir(BASEDIR.getpath('tmpldir').$file) ) continue;
			$themes[]=$file;
		}
		closedir($handle);
		sort($themes);
		foreach ( $themes AS $themeid ) {
			$themelist.='<option value="'.$themeid.'"'.iif($themeid==$_POST['pub_theme'],' selected="selected"').'>'.$themeid.'</option>';
		}
		
		//Custom-Felder
		for ( $i=1; $i<=10; $i++ ) {
			$fieldname=$set['user']['cusfield_names'][$i-1];
			$apx->tmpl->assign('CUSFIELD'.$i.'_NAME',replace($fieldname));
			$apx->tmpl->assign('CUSTOM'.$i,compatible_hsc($_POST['custom'.$i]));
		}
		
		$apx->tmpl->assign('USERNAME_LOGIN',replace($_POST['username_login']));
		$apx->tmpl->assign('USERNAME',compatible_hsc($_POST['username']));
		$apx->tmpl->assign('EMAIL',compatible_hsc($_POST['email']));
		$apx->tmpl->assign('HOMEPAGE',compatible_hsc($_POST['homepage']));
		$apx->tmpl->assign('ICQ',(int)$_POST['icq']);
		$apx->tmpl->assign('AIM',compatible_hsc($_POST['aim']));
		$apx->tmpl->assign('YIM',compatible_hsc($_POST['yim']));
		$apx->tmpl->assign('MSN',compatible_hsc($_POST['msn']));
		$apx->tmpl->assign('SKYPE',compatible_hsc($_POST['skype']));
		$apx->tmpl->assign('REALNAME',compatible_hsc($_POST['realname']));
		$apx->tmpl->assign('CITY',compatible_hsc($_POST['city']));
		$apx->tmpl->assign('COUNTRY',compatible_hsc($_POST['country']));
		$apx->tmpl->assign('PLZ',compatible_hsc($_POST['plz']));
		$apx->tmpl->assign('INTERESTS',compatible_hsc($_POST['interests']));
		$apx->tmpl->assign('WORK',compatible_hsc($_POST['work']));
		$apx->tmpl->assign('GENDER',(int)$_POST['gender']);
		$apx->tmpl->assign('BD_DAY',(int)$_POST['bd_day']);
		$apx->tmpl->assign('BD_MON',(int)$_POST['bd_mon']);
		$apx->tmpl->assign('BD_YEAR',(int)$_POST['bd_year']);
		$apx->tmpl->assign('SIGNATURE',compatible_hsc($_POST['signature']));
		$apx->tmpl->assign('MAXLEN',$set['user']['sigmaxlen']);
		$apx->tmpl->assign('PUB_INVISIBLE',(int)$_POST['pub_invisible']);
		$apx->tmpl->assign('PUB_HIDEMAIL',(int)$_POST['pub_hidemail']);
		$apx->tmpl->assign('PUB_POPPM',(int)$_POST['pub_poppm']);
		$apx->tmpl->assign('PUB_SHOWBUDDIES',(int)$_POST['pub_showbuddies']);
		$apx->tmpl->assign('PUB_USEGB',(int)$_POST['pub_usegb']);
		$apx->tmpl->assign('PUB_GBMAIL',(int)$_POST['pub_gbmail']);
		$apx->tmpl->assign('PUB_THEME',$themelist);
		$apx->tmpl->assign('PUB_LANG',$lang_pub);
		$apx->tmpl->assign('PUB_PROFILEFORFRIENDS',(int)$_POST['pub_profileforfriends']);
		$apx->tmpl->assign('FORUM_AUTOSUBSCRIBE',(int)$_POST['forum_autosubscribe']);
		$apx->tmpl->assign('ADMIN_LANG',$lang_admin);
		$apx->tmpl->assign('ADMIN_EDITOR',(int)$_POST['admin_editor']);
	
		$apx->tmpl->parse('myprofile');
	}
}



//////////////////////////////////////////////////////////////////////////////////////////////////////


//***************************** Benutzerprofil zeigen *****************************
function profile() {
global $set,$apx,$tmpl,$db,$user;
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	$res=$db->first("SELECT a.userid,a.username,a.email,a.reg_time,a.reg_email,a.lastactive,b.name FROM ".PRE."_user AS a LEFT JOIN ".PRE."_user_groups AS b USING(groupid) WHERE a.userid='".$_REQUEST['id']."'");
	
	$apx->tmpl->assign('USERID',$res['userid']);
	$apx->tmpl->assign('USERNAME',replace($res['username']));
	$apx->tmpl->assign('REGDATE',mkdate($res['reg_time']));
	$apx->tmpl->assign('REGEMAIL',replace($res['reg_email']));
	$apx->tmpl->assign('EMAIL',replace($res['email']));
	$apx->tmpl->assign('LASTACTIVE',mkdate($res['lastactive']));
	$apx->tmpl->assign('GROUPNAME',replace($res['name']));
	
	$apx->tmpl->parse('profile');
}



//////////////////////////////////////////////////////////////////////////////////////////////////////

//***************************** eMail senden *****************************
function sendmail() {
	global $set,$db,$apx;
	
	//Senden durchführen
	if ( $_REQUEST['doit'] ) {
		$this->sendmail_exec();
		return;
	}
	
	//Gruppen selected
	if ( !is_array($_POST['groupid']) || $_POST['groupid'][0]=='all' ) $_POST['groupid']=array('all');
	
	//Daten speichern
	if ( $_POST['send'] ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['subject'] || !$_POST['text'] || !count($_POST['groupid']) ) infoNotComplete();
		else {
			$groups = array();
			if ( $_POST['groupid'][0]!='all' ) {
				$groups = array_map('intval', $_POST['groupid']);
			}
			$data = array(
				'subject' => $_POST['subject'],
				'text' => $_POST['text'],
				'groups' => $groups
			);
			$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='user' AND varname='sendmail_data' LIMIT 1");
			//die('action.php?action=user.sendmail&doit=1');
			printJSRedirect('action.php?action=user.sendmail&doit=1&sectoken='.$apx->session->get('sectoken'));
		}
	}
	else {
		
		//Sektionen auflisten
		$grouplist = '<option value="all"'.iif($_POST['groupid'][0]=='all', 'selected="selected"').' style="font-weight:bold;">'.$apx->lang->get('ALL').'</option>';
		$data=$db->fetch("SELECT groupid,name FROM ".PRE."_user_groups ORDER BY name ASC");
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				$grouplist.='<option value="'.$res['groupid'].'"'.iif(in_array($res['groupid'], $_POST['groupid']),' selected="selected"').'>'.replace($res['name']).'</option>';
			}
		}
		
		$apx->tmpl->assign('GROUP', $grouplist);
		$apx->tmpl->assign('SUBJECT', compatible_hsc($_POST['subject']));
		$apx->tmpl->assign('TEXT', compatible_hsc($_POST['text']));
		$apx->tmpl->parse('sendmail');
	}
}


//Senden durchführen
function sendmail_exec() {
	global $apx, $db, $set;
	
	//Token prüfen
	if ( !checkToken() ) {
		printInvalidToken();
		return;
	}
	
	//FORWARDER
	if ( !isset($_REQUEST['done']) ) {
		tmessage('sending',array('FORWARDER'=>'action.php?action=user.sendmail&amp;doit=1&amp;sectoken='.$apx->session->get('sectoken').'&amp;done=0'));
		return;
	}
	
	//VARS
	$done=(int)$_REQUEST['done'];
	$countPerCall = 50;
	@set_time_limit(600);
	
	//Newsletter-Info auslesen
	$newsletter=$set['user']['sendmail_data'];
	if ( !isset($newsletter['text']) ) die('no valid newsletter!');
	
	//SEND NEWSLETTER
	if ( is_array($newsletter['groups']) && count($newsletter['groups']) ) {
		$data = $db->fetch("SELECT email, username FROM ".PRE."_user WHERE active=1 AND reg_key='' AND groupid IN (".implode(',', $newsletter['groups']).") ORDER BY email ASC LIMIT ".$done.",".$countPerCall);
	}
	else {
		$data = $db->fetch("SELECT email, username FROM ".PRE."_user WHERE active=1 AND reg_key='' ORDER BY email ASC LIMIT ".$done.",".$countPerCall);
	}
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$this->sendmail_send($res['email'], $res['username'], $newsletter['subject'], $newsletter['text']);
		}
		
		////// FORWARDER
		
		//Vorgang beendet
		if ( $i<$countPerCall ) {
			$db->query("UPDATE ".PRE."_config SET value='' WHERE module='user' AND varname='sendmail_data' LIMIT 1");
			logit('USER_SENDMAIL');
			message($apx->lang->get('MSG_OK'));
			return;
		}
		
		//Weiter gehts...
		else {
			tmessage('sending',array('FORWARDER'=>'action.php?action=user.sendmail&amp;doit=1&amp;sectoken='.$apx->session->get('sectoken').'&amp;done='.($done+$countPerCall)));
			return;
		}
	}
	
	//Keine Empfänger, das wars...
	else {
		$db->query("UPDATE ".PRE."_config SET value='' WHERE module='user' AND varname='sendmail_data' LIMIT 1");
		logit('USER_SENDMAIL');
		message($apx->lang->get('MSG_OK'));
		return;
	}
}



//Rundmail verschicken
function sendmail_send($email, $username, $subject, $text) {
	global $set,$db,$apx;
	
	$text = str_replace('{USERNAME}', $username, $text);
	mail(
		$email,
		$subject,
		$text,
		"From: ".$set['main']['mailbotname']."<".$set['main']['mailbot'].">"
	);
}



//***************************** PM senden *****************************
function sendpm() {
	global $set,$db,$apx;
	
	//Senden durchführen
	if ( $_REQUEST['doit'] ) {
		$this->sendpm_exec();
		return;
	}
	
	//Gruppen selected
	if ( !is_array($_POST['groupid']) || $_POST['groupid'][0]=='all' ) $_POST['groupid']=array('all');
	
	//Daten speichern
	if ( $_POST['send'] ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['subject'] || !$_POST['text'] || !count($_POST['groupid']) ) infoNotComplete();
		else {
			$groups = array();
			if ( $_POST['groupid'][0]!='all' ) {
				$groups = array_map('intval', $_POST['groupid']);
			}
			$data = array(
				'subject' => $_POST['subject'],
				'text' => $_POST['text'],
				'groups' => $groups
			);
			$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='user' AND varname='sendpm_data' LIMIT 1");
			//die('action.php?action=user.sendpm&doit=1');
			printJSRedirect('action.php?action=user.sendpm&doit=1&sectoken='.$apx->session->get('sectoken'));
		}
	}
	else {
		//Sektionen auflisten
		$grouplist = '<option value="all"'.iif($_POST['groupid'][0]=='all', 'selected="selected"').' style="font-weight:bold;">'.$apx->lang->get('ALL').'</option>';
		$data=$db->fetch("SELECT groupid,name FROM ".PRE."_user_groups ORDER BY name ASC");
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				$grouplist.='<option value="'.$res['groupid'].'"'.iif(in_array($res['groupid'], $_POST['groupid']),' selected="selected"').'>'.replace($res['name']).'</option>';
			}
		}
		
		$apx->tmpl->assign('GROUP', $grouplist);
		$apx->tmpl->assign('SUBJECT', compatible_hsc($_POST['subject']));
		$apx->tmpl->assign('TEXT', compatible_hsc($_POST['text']));
		$apx->tmpl->parse('sendpm');
	}
}


//Senden durchführen
function sendpm_exec() {
	global $apx, $db, $set;
	
	//Token prüfen
	if ( !checkToken() ) {
		printInvalidToken();
		return;
	}
	
	//FORWARDER
	if ( !isset($_REQUEST['done']) ) {
		tmessage('sending',array('FORWARDER'=>'action.php?action=user.sendpm&amp;doit=1&amp;sectoken='.$apx->session->get('sectoken').'&amp;done=0'));
		return;
	}
	
	//VARS
	$done=(int)$_REQUEST['done'];
	$countPerCall = 50;
	@set_time_limit(600);
	
	//Newsletter-Info auslesen
	$newsletter=$set['user']['sendpm_data'];
	if ( !isset($newsletter['text']) ) die('no valid newsletter!');
	$newsletter['text_clear'] = $newsletter['text'];
	while ( preg_match('#\[([a-z0-9]+)(=.*?)?\](.*?)\[/\\1\]#si',$newsletter['text_clear']) ) {
		$text=preg_replace('#\[([a-z0-9]+)(=.*?)?\](.*?)\[/\\1\]#si','\\3',$newsletter['text_clear']);
	}
	
	//SEND NEWSLETTER
	if ( is_array($newsletter['groups']) && count($newsletter['groups']) ) {
		$data = $db->fetch("SELECT userid, email, pub_poppm, pub_mailpm FROM ".PRE."_user WHERE active=1 AND reg_key='' AND groupid IN (".implode(',', $newsletter['groups']).") ORDER BY email ASC LIMIT ".$done.",".$countPerCall);
	}
	else {
		$data = $db->fetch("SELECT userid, email, pub_poppm, pub_mailpm FROM ".PRE."_user WHERE active=1 AND reg_key='' ORDER BY email ASC LIMIT ".$done.",".$countPerCall);
	}
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$this->sendpm_send($res, $newsletter['subject'], $newsletter['text'], $newsletter['text_clear']);
		}
		
		////// FORWARDER
		
		//Vorgang beendet
		if ( $i<$countPerCall ) {
			$db->query("UPDATE ".PRE."_config SET value='' WHERE module='user' AND varname='sendpm_data' LIMIT 1");
			logit('USER_SENDPM');
			message($apx->lang->get('MSG_OK'));
			return;
		}
		
		//Weiter gehts...
		else {
			tmessage('sending',array('FORWARDER'=>'action.php?action=user.sendpm&amp;doit=1&amp;sectoken='.$apx->session->get('sectoken').'&amp;done='.($done+$countPerCall)));
			return;
		}
	}
	
	//Keine Empfänger, das wars...
	else {
		$db->query("UPDATE ".PRE."_config SET value='' WHERE module='user' AND varname='sendpm_data' LIMIT 1");
		logit('USER_SENDPM');
		message($apx->lang->get('MSG_OK'));
		return;
	}
}



//PM verschicken
function sendpm_send($res, $subject, $text, $cleartext) {
	global $apx, $db, $set;
	list($touser, $email, $pop, $mailpm) = $res;
	
	//PM erstellen
	$db->query("INSERT INTO ".PRE."_user_pms (fromuser,touser,subject,text,time,addsig) VALUES ('".$apx->user->info['userid']."','".$touser."','".addslashes($subject)."','".addslashes($text)."','".time()."','0')");
	if ( $pop ) $db->query("UPDATE ".PRE."_user SET pmpopup='1' WHERE userid='".$touser."' LIMIT 1");
	
	//eMail-Benachrichtigung bei neuer PM
	if ( $mailpm ) {
		$inboxlink=HTTP_HOST.mklink('user.php?action=pms','user,pms.html');
		$input=array(
			'USERNAME'=>$apx->user->info['username'],
			'WEBSITE'=>$set['main']['websitename'],
			'INBOX'=>$inboxlink,
			'SUBJECT'=>$subject,
			'TEXT'=>$cleartext
		);
		sendmail($email,'NEWPM',$input);
	}
}


} //END CLASS


?>