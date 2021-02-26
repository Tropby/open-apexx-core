<?php 

class Path 
{
	//Variable schützen
	private $pathcfg=array();
	static private Path $instance;

	private function __construct()
	{
		//PFAD-KONFIGURATION - NICHT ÄNDERN!
		$this->pathcfg['moduledir']           = 'modules/';
		$this->pathcfg['module']              = 'modules/{MODULE}/';

		$this->pathcfg['tmpldir']             = 'templates/';
		$this->pathcfg['tmpl_base_public']    = 'templates/{THEME}/';
		$this->pathcfg['tmpl_base_admin']     = 'admin/templates/';
		$this->pathcfg['tmpl_modules_public'] = 'templates/{THEME}/{MODULE}/';
		$this->pathcfg['tmpl_modules_admin']  = 'modules/{MODULE}/admin/';

		$this->pathcfg['langdir']             = 'language/';
		$this->pathcfg['lang_base']           = 'language/{LANGID}/';
		$this->pathcfg['lang_modules']        = 'language/{LANGID}/{MODULE}/';

		$this->pathcfg['lib']             	  = 'lib/';

		$this->pathcfg['uploads']             = 'uploads/';
		$this->pathcfg['content']             = 'content/';
		$this->pathcfg['cache']               = 'cache/';
	}

	static public function &instance() : Path
	{
		if(!isset(Path::$instance))
			Path::$instance = new Path();
		return Path::$instance;
	}

	/**
	 * @deprecated Extrracting path variables will be removed
	 */
	public function path(){
		return $this->pathcfg;
	}

	//Pfad holen
	public function getPath($id,$input=array()) {
		global $pathcfg;
		$path=$this->pathcfg[$id];
		
		foreach ( $input AS $find => $replace ) {
			$path=str_replace('{'.$find.'}',$replace,$path);
		}
		
		return $path;
	}

	//Pfad zum Modul
	public function getModulePath($modulename) {
		return $this->getPath('module',array('MODULE'=>$modulename));
	}

	/**
	 * Absolute path to module
	 * @param string module Module ID
	 * @return string Path to module
	 */
	public function getAbsoliteModulePath($modulename)
	{
		return BASEDIR.$this->getModulePath($modulename);
	}

}

/**
 * Pfad holen
 * @deprecated use Path class
 */
function getpath($id, $input = array())
{
	return Path::instance()->getpath($id, $input);
}

/**
 * Pfad zum Modul
 * @deprecated use Path class
 */
function getmodulepath($modulename)
{
	return Path::instance()->getmodulepath($modulename);
}

?>