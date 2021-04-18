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


//***************************** Eigenes Benutzerprofil *****************************
function myprofile() {

}



//////////////////////////////////////////////////////////////////////////////////////////////////////


//***************************** Benutzerprofil zeigen *****************************
function profile() {

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