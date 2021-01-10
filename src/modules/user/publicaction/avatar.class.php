<?php

namespace Modules\User\PublicAction;

class Avatar extends \PublicAction
{
	public function execute()
	{
		$apx = $this->publicModule()->module()->apx();
		$db = $apx->db();
		$user = $apx->get_registered_object('user');

		if (!isset($user->info['userid']) || !$user->info['userid'])
		{
			(new Login($this->publicModule()))->execute();
			return;
		}

		$apx->lang->drop('avatar');
		$apx->headline($apx->lang->get('HEADLINE_AVATAR'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
		$apx->titlebar($apx->lang->get('HEADLINE_AVATAR'));
		$extensions = array('GIF', 'JPG', 'JPE', 'JPEG', 'PNG');

		if ($_POST['send'])
		{

			//Neuen Avatar hochladen
			if (is_uploaded_file($_FILES['avatar']['tmp_name']))
			{
				$mm = new \mediamanager;
				$ext = $mm->getext($_FILES['avatar']['name']);
				$newfile = 'avatar_' . $user->info['userid'] . '_' . time() . '.' . strtolower($ext);

				//AVATARE AUTOMATISCH VERKLEINERN
				if ($apx->config('user')['avatar_resize'])
				{
					$img = new \image;

					if (!in_array($ext, $extensions)) message($apx->lang->get('MSG_NOTALLOWED'), 'javascript:history.back()');
					else
					{
						$tempname = 'avatar_' . md5(microtime()) . '.tmp';
						$mm->uploadfile($_FILES['avatar'], 'temp', $tempname);
						$info = getimagesize(BASEDIR . $apx->path()->getpath('uploads') . 'temp/' . $tempname);

						//Kein Bild => löschen und Ende
						if ($info[0] == 0 || $info[1] == 0)
						{
							$mm->deletefile('temp/' . $tempname);
							message($apx->lang->get('MSG_MAXDIM'), 'javascript:history.back()');
							require('lib/_end.php');
						}

						//Bild zu groß => verkleinern
						if ($info[0] > $apx->config('user')['avatar_maxdim'] || $info[1] > $apx->config('user')['avatar_maxdim'])
						{
							if ($ext == 'GIF') $ext = 'jpg';
							$newfile = 'avatar_' . $user->info['userid'] . '_' . time() . '.' . strtolower($ext);

							list($picture, $picturetype) = $img->getimage('temp/' . $tempname);
							$scaled = $img->resize(
								$picture,
								$apx->config('user')['avatar_maxdim'],
								$apx->config('user')['avatar_maxdim'],
								1,
								0
							);

							if ($scaled != $picture) imagedestroy($picture);
							$img->saveimage($scaled, $picturetype, 'user/' . $newfile);
							imagedestroy($scaled);
						}

						//Alles OK => Kopieren
						else
						{
							$mm->copyfile('temp/' . $tempname, 'user/' . $newfile);
						}

						$mm->deletefile('temp/' . $tempname);
						$db->query("UPDATE " . PRE . "_user SET avatar='" . addslashes($newfile) . "',avatar_title='" . addslashes($_POST['avatar_title']) . "' WHERE userid='" . $user->info['userid'] . "' LIMIT 1");
						if ($user->info['avatar']) $mm->deletefile('user/' . $user->info['avatar']);

						message($apx->lang->get('MSG_OK'), mklink('user.php?action=avatar', 'user,avatar.html'));
					}
				}

				//AVATAR 1:1 ÜBERNEHMEN
				else
				{
					if (!in_array($ext, $extensions)) message($apx->lang->get('MSG_NOTALLOWED'), 'javascript:history.back()');
					elseif ($_FILES['avatar']['size'] > $apx->config('user')['avatar_maxsize']) message($apx->lang->get('MSG_MAXSIZE'), 'javascript:history.back()');
					else
					{
						$mm->uploadfile($_FILES['avatar'], 'user', $newfile);
						$info = getimagesize(BASEDIR . $apx->path()->getpath('uploads') . 'user/' . $newfile);

						if ($info[0] > $apx->config('user')['avatar_maxdim'] || $info[1] > $apx->config('user')['avatar_maxdim'] || $info[0] == 0 || $info[1] == 0)
						{
							$mm->deletefile('user/' . $newfile);
							message($apx->lang->get('MSG_MAXDIM'), 'javascript:history.back()');
							require('lib/_end.php');
						}

						$db->query("UPDATE " . PRE . "_user SET avatar='" . addslashes($newfile) . "',avatar_title='" . addslashes($_POST['avatar_title']) . "' WHERE userid='" . $user->info['userid'] . "' LIMIT 1");
						if ($user->info['avatar']) $mm->deletefile('user/' . $user->info['avatar']);

						message($apx->lang->get('MSG_OK'), mklink('user.php?action=avatar', 'user,avatar.html'));
					}
				}
			}

			//Avatar löschen
			elseif ($_POST['delav'])
			{
				require(BASEDIR . 'lib/class.mediamanager.php');
				$mm = new \mediamanager;
				$mm->deletefile('user/' . $user->info['avatar']);

				$db->query("UPDATE " . PRE . "_user SET avatar='',avatar_title='' WHERE userid='" . $user->info['userid'] . "' LIMIT 1");
				message($apx->lang->get('MSG_OK'), mklink('user.php?action=avatar', 'user,avatar.html'));
			}

			//Nur Titel ändern
			else
			{
				$db->query("UPDATE " . PRE . "_user SET avatar_title='" . addslashes($_POST['avatar_title']) . "' WHERE userid='" . $user->info['userid'] . "' LIMIT 1");
				message($apx->lang->get('MSG_OK'), mklink('user.php?action=avatar', 'user,avatar.html'));
			}
		}
		else
		{
			if ($user->info['avatar'])
			{
				$apx->tmpl->assign('CURRENT_AVATAR', $user->mkavatar($user->info));
				$apx->tmpl->assign('CURRENT_TITLE', $user->mkavtitle($user->info));
			}

			$apx->tmpl->assign('MAX_DIMENSIONS', $apx->config('user')['avatar_maxdim']);
			$apx->tmpl->assign('MAX_FILESIZE', user_getsize($apx->config('user')['avatar_maxsize'], 0));

			$postto = mklink(
				'user.php?action=avatar',
				'user,avatar.html'
			);

			$apx->tmpl->assign('POSTTO', $postto);
			$apx->tmpl->assign('AVATAR_TITLE', compatible_hsc($user->info['avatar_title']));
			$apx->tmpl->parse('avatar');
		}
	}
}
