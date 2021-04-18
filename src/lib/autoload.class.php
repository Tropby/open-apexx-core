<?php

/*
	Open Apexx Core
	(c) Copyright 2020 Carsten Grings

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

class Autoload
{
    static private \Apexx $apx;
    static public function apx()
    {
        return Autoload::$apx;
    }
    static public function setApx(\Apexx $apx)
    {
        Autoload::$apx = $apx;
    }
    
    static public function register()
    {        
        spl_autoload_register(
            function ($class)
            {    
                $className = $class;
                $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
                $class = strtolower($class);
                $path = "";

                // Include Module Classes
                if( file_exists(BASEDIR . $class . ".class.php") )
                {
                    $path = BASEDIR . $class . ".class.php";
                }

                // new style
                elseif (file_exists(BASEDIR . "lib/" . $class . ".class.php"))
                {
                    $path = BASEDIR . "lib/" . $class . ".class.php";
                }

                // Include Core Classes
                elseif (file_exists(BASEDIR . "lib/class." . $class . ".php"))
                {
                    $path = BASEDIR . "lib/class." . $class . ".php";
                }
                
                // Class File not found!
                else
                {
                    echo "<b>ERROR:</b> Can not find class \"".$class."\".<br />";
                }

                if (isset(Autoload::$apx) && Autoload::$apx->config('debug_autoload'))
                {
                    echo "<b>DEBUG:</b> Autoload: " . BASEDIR . $class . ".class.php<br />";
                }

                require_once($path);

                if( !class_exists($className, false) )
                {
                    echo "<b>ERROR:</b> Can not load class \"" . $className . "\". Class file \"".$path."\" does not define this class!<br />";
                    require_once("_end.php");
                    exit;
                }
            }
        );
    }
}
Autoload::register();

?>