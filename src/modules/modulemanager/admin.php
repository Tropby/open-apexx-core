<?php

/*
	Open Apexx Module Manager
	Copyright (C) 2020 Carsten Grings

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 2.1 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');

class Action 
{
	/*
	 delTree
	 Delete a directory with all its child files/directories
	 */
	private function delTree($dir) 
	{
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file) 
		{
			(is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}
	
	/*
	 scanTree
	 Scans a file tree ans returns what files will be overwritten.
	 */
	 private function scanTree( $newPath, $oldPath )
	{
		$new = scandir($newPath);
				
		$filelist = array();
		foreach( $new as $n )
		{
			if( $n == "." || $n == ".." ) continue;
			
			if( is_file($newPath.$n) )
			{
				if( file_exists( $oldPath.$n ) )
				{
					$filelist[] = array(
						"OVERWRITE" => 1,
						"FILE" => $oldPath.$n
					);
				}
				else
				{
					$filelist[] = array(
						"OVERWRITE" => 0,
						"FILE" => $oldPath.$n
					);
				}
			}
			else if( is_dir( $newPath.$n ) )
			{
				$fl = $this->scanTree( $newPath.$n."/", $oldPath.$n."/");
				foreach( $fl as $f )
				{
					$filelist[] = $f;
				}
			}
		}
		
		return $filelist;
	}
	
	/*
	 scanTree
	 Scans a file tree ans returns what files will be overwritten.
	 */
	private function scanTemplates( $newPath, $oldPath )
	{
		$filelist = array();
		if( is_dir($newPath) )
		{
			$new = scandir($newPath);
					
			foreach( $new as $n )
			{
				if( $n == "." || $n == ".." ) continue;
				
				if( is_file($newPath.$n) )
				{
					if( file_exists( $oldPath.$n ) )
					{
						$filelist[] = array(
							"COPY" => 0,
							"FILE" => $oldPath.$n
						);
					}
					else
					{
						$filelist[] = array(
							"COPY" => 1,
							"FILE" => $oldPath.$n
						);
					}
				}
				else if( is_dir( $newPath.$n ) )
				{
					$fl = $this->scanTemplates( $newPath.$n."/", $oldPath.$n."/");
					foreach( $fl as $f )
					{
						$filelist[] = $f;
					}
				}
			}
		}
		
		return $filelist;
	}
	
	 private function copyTemplates( $newPath, $oldPath )
	{
		if( is_dir($newPath) )
		{
			$new = scandir($newPath);
			
			@mkdir($oldPath);
			
			$filelist = array();
			foreach( $new as $n )
			{
				if( $n == "." || $n == ".." ) continue;
				
				if( is_file($newPath.$n) )
				{
					if( file_exists( $oldPath.$n ) )
					{
						$filelist[] = array(
							"COPY" => 0,
							"FILE" => $oldPath.$n
						);
					}
					else
					{
						$filelist[] = array(
							"COPY" => 1,
							"FILE" => $oldPath.$n
						);
						copy( $newPath.$n, $oldPath.$n );
					}
				}
				else if( is_dir( $newPath.$n ) )
				{
					@mkdir($oldPath.$n);
					
					$fl = $this->scanTree( $newPath.$n."/", $oldPath.$n."/");
					foreach( $fl as $f )
					{
						$filelist[] = $f;
					}
				}
			}
		}
		
		return $filelist;
	}		

	/*
	 copyTree
	 Copies a file tree to the apexx system
	 */
	private function copyTree( $newPath, $oldPath )
	{
		$new = scandir($newPath);
		$filelist = array();
								
		foreach( $new as $n )
		{
			if( $n == "." || $n == ".." ) continue;
			
			if( is_file($newPath.$n) )
			{
				if( !rename( $newPath.$n, $oldPath.$n ) )
				{
					$filelist[] = array(
						"MOVE_FAILED" => 1,
						"FILE" => $oldPath.$n
					);
				}
				else
				{
					$filelist[] = array(
						"MOVE_FAILED" => 0,
						"FILE" => $oldPath.$n
					);
				}
			}
			else if( is_dir( $newPath.$n ) )
			{
				@mkdir($oldPath.$n);
				
				$fl = $this->copyTree( $newPath.$n."/", $oldPath.$n."/");
				foreach( $fl as $f )
				{
					$filelist[] = $f;
				}
			}
		}
		
		return $filelist;
	}
	
	private function doPackageStep1( $filename )
	{
		global $apx;
		
		$this->delTree(BASEDIR."tmp");
		mkdir(BASEDIR."tmp");
		
		$zip = new ZipArchive;
		if ($zip->open($filename) === TRUE) 
		{
			$zip->extractTo(BASEDIR.'tmp');
			$zip->close();
			
			$modules = scandir(BASEDIR.'tmp/modules');
			
			if( count($modules) > 3 )
				die( "Can not install multible modules at one time!" );
			
			$m = $modules[2];
			
			include_once( BASEDIR."tmp/modules/".$m."/init.php" );												
			$apx->tmpl->assign( "MODULE_ID", $module["id"]);
			$apx->tmpl->assign( "MODULE_VERSION", $module["version"]);
			$apx->tmpl->assign( "MODULE_AUTHOR", $module["author"]);											
			
			if( file_exists(BASEDIR."modules/".$m."/init.php" ) )
			{
				include_once( BASEDIR."tmp/modules/".$m."/init.php" );
				$apx->tmpl->assign( "CURRENT_ID", $module["id"]);
				$apx->tmpl->assign( "CURRENT_VERSION", $module["version"]);
				$apx->tmpl->assign( "CURRENT_AUTHOR", $module["author"]);																		
			}
						
			
			$apx->tmpl->assign( "FILE_INFO", $this->scanTree(BASEDIR.'tmp/', BASEDIR) );
			$apx->tmpl->assign( "TEMPLATE_INFO", $this->scanTemplates( BASEDIR."tmp/templates/examples/".$m."/", BASEDIR."templates/default/".$m."/" ));
			
			$apx->tmpl->assign( "MODULE", $m);
			$apx->tmpl->parse("install_step1");
		} 
		else 
		{
			die( "Can not unzip module." );
		}					
	}
	
	public function repos()
	{
		global $apx;
		
		if( isset($_REQUEST['install']) )
		{
			$fileUrl = base64_decode($_REQUEST['install']);
			$saveTo = BASEDIR.'uploads/package.zip';

			$fp = fopen($saveTo, 'w+');

			$ch = curl_init($fileUrl);
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_exec($ch);

			$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			curl_close($ch);

			fclose($fp);
		
			if( $statusCode == 200 )
				$this->doPackageStep1($saveTo);
			else
				die("Can not download file!");
		}
		else if(isset($_REQUEST["show"]) && isset($_REQUEST["repos"]) && isset($_REQUEST["user"]) )
		{
			$ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/".$_REQUEST["user"]."/".$_REQUEST["repos"]."/releases");
			curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
            curl_setopt($ch, CURLOPT_NOBODY, FALSE); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
			
			$result = explode( "\r\n\r\n", $result)[1];
			
			$data = json_decode($result);
			
			$tmpldata = array();
			foreach( $data as $d )
			{
				$tmpldata[] = array(
					"VERSION" => $d->name,
					"INFO" => nl2br( utf8_decode( $d->body ) ),
					"LINK" => base64_encode($d->assets[0]->browser_download_url),
					"USER" => $d->author->login
				);
			}
			
			$apx->tmpl->assign( "RELEASES", $tmpldata);
			$apx->tmpl->parse("releases");	
			
		}
		else
		{
			$repos = file("https://raw.githubusercontent.com/Tropby/open-apexx-core/master/reposlist.txt");
			
			$tmplrepos = array();
			foreach( $repos as $r )
			{
				$r = explode( ";", $r );
				$id = $r[0];
				$user = $r[1];
				$name = $r[2];
				
				$tmplrepos[] = array(
					"ID" => $id,
					"USER" => $user,
					"NAME" => $name
				);
			}
			
			$apx->tmpl->assign( "REPOS", $tmplrepos);
			$apx->tmpl->parse("repos");			
		}
	}
	
	/*
	 install
	 Installs a package to the apexx system
	 */
	public function install()
	{
		global $apx; 
		
		switch( (int)$_REQUEST["step"] )
		{
			case 2:
			
				$modules = scandir(BASEDIR.'tmp/modules');
			
				$filelist = $this->copyTree(BASEDIR.'tmp/', BASEDIR);
				$fp = fopen(BASEDIR.getmodulepath($modules[2])."filelist.txt", "w");
				foreach($filelist as $f)
				{
					fwrite($fp, $f["FILE"]."\n");
				}
				fclose($fp);

				$templatelist = $this->copyTemplates( BASEDIR."templates/examples/".$modules[2]."/", BASEDIR."templates/default/".$modules[2]."/" );
				
				$apx->tmpl->assign( "FILE_INFO", $filelist );
				$apx->tmpl->assign( "TEMPLATE_INFO", $templatelist );
				$apx->tmpl->assign( "MODULE", $modules[2]);
				$apx->tmpl->parse("install_step2");
				break;
			
			case 1:
				if (!empty($_FILES) && $_FILES['package_upload']['error'] == UPLOAD_ERR_OK)
				{
					move_uploaded_file($_FILES['package_upload']['tmp_name'], BASEDIR."uploads/package.zip");					
					$this->doPackageStep1(BASEDIR.'uploads/package.zip');
				}
				else
				{
					die("ERROR file upload failed!");
				}
			
				break;
				
			default:
				$apx->tmpl->parse("install");				
				break;
		}
		
	}
	
	function deleteModule()
	{
		global $_REQUEST;
		
		$f = file(BASEDIR.getmodulepath($_REQUEST["id"])."filelist.txt");
		foreach( $f as $v )
		{
			if( file_exists(trim($v)) )
			{
				unlink(trim($v));
				echo "lösche: ".$v."<br />";
			}
		}
		
		echo "lösche: ".BASEDIR.getmodulepath($_REQUEST["id"])."<br />";
		$this->delTree(BASEDIR.getmodulepath($_REQUEST["id"]));
		
		echo "lösche: ".BASEDIR."templates/examples/".$_REQUEST["id"]."<br />";
		$this->delTree(BASEDIR."templates/examples/".$_REQUEST["id"]);				
	}
	
	/*
	 show
	 Shows a list of all available modules on the server
	 */
	function show()
	{
		global $apx, $db;
		
		$modules = scandir(BASEDIR."modules");
		
		$tmplmodules = array();
		$i=0;
		
		foreach( $modules as $module )
		{
			if( $module == "." || $module == ".." ) continue;
			
			$tmplmodules[$i]["ID"] = $module;
			if( file_exists(BASEDIR.getmodulepath($module)."filelist.txt") )
			{
				$f = file(BASEDIR.getmodulepath($module)."filelist.txt");
				foreach( $f as $v )
				{
					$tmplmodules[$i]["FILE_LIST"][] = array( 
						"NAME" => str_replace( BASEDIR, "/", $v ), 
						"SIZE" => ( file_exists(trim($v)) ? filesize(trim($v)) : "???" ),
						"MISSING" => !file_exists(trim($v))
					);
				}
				
				$d = $db->first("SELECT * FROM ".PRE."_modules WHERE module='".$module."' LIMIT 1;");
				
				if(!$d["active"] && !$d["installed"])
					$tmplmodules[$i]["REMOVE_LINK"] = "action.php?action=modulemanager.deleteModule&id=".$module;
			}
			$i++;
		}
		
		$apx->tmpl->assign("MODULES", $tmplmodules);
		$apx->tmpl->parse('index');
	}
	
}