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

interface IModule
{
    public function init();
    public function startup();
    public function executePublic(string $action);
    public function executeAdmin(string $action);
    public function shutdown();
}

abstract class Module implements IModule
{
    protected apexx $apx;

    private Array $info = array();

    private \PublicModule $publicModule;
    private \AdminModule $adminModule;    
    private \Setup $setup;

    /**
     * @var Map<String, String> mapping object types to names
     */
    private Array $objectTypes = array();

    private Array $admin_actions = array();
    private Array $admin_template_functions = array();
    private Array $template_functions = array();

    public function __construct(apexx &$apx,string $id, array $dependence, array $requirement, string $version, string $author, string $contact)
    {
        $this->apx = &$apx;

        $this->info = array(
            0 => 1, 1 => 999999,
            'id' => $id,
            'dependence' => $dependence,
            'requirement' => $requirement,
            'version' => $version,
            'author' => $author,
            'contact' => $contact
        );
    }

    public function version() : string
    {
        return $this->info["version"];
    }

    public function id() : string
    {
        return $this->info["id"];
    }

    /**
     * @deprecated use id()
     */
    public function getId() : string
    {
        return $this->info['id'];
    }

    /**
     * @param string $funcname Template name of the function to call
     * @param array $params array of parameters to call the template function
     */
    public function executePublicTemplateFunction(string $funcname, array $params) : bool
    {
        if( MODE == 'admin')
        {
            if (!isset($this->adminModule))
                return $this->executePublicTemplateFunctionOldStyle($funcname, $params);

            if (!$this->adminModule->executeTemplateFunction($funcname, $params))
            {
                return $this->executePublicTemplateFunctionOldStyle($funcname, $params);
            }
            return true;
        }

        if(!isset($this->publicModule))
            return $this->executePublicTemplateFunctionOldStyle($funcname, $params);

        if( !$this->publicModule->executeTemplateFunction($funcname, $params) )
        {
            return $this->executePublicTemplateFunctionOldStyle($funcname, $params);
        }

        return true;
    }

    /**
     * @deprecated will be deleted in the future
     */
    private function executePublicTemplateFunctionOldStyle(string $funcname, array $params) : bool
    {
        if (isset($this->template_functions[$funcname]))
        {
            if( MODE == "admin")
                include_once(BASEDIR . $this->apx()->path()->getmodulepath($this->id()) . "admin_tfunctions.php");
            else
                include_once(BASEDIR . $this->apx()->path()->getmodulepath($this->id()) . "tfunctions.php");

            if (function_exists($this->template_functions[$funcname][0]))
            {
                call_user_func_array($this->template_functions[$funcname][0], $params);
            }
            else
            {
                ApexxError::ERROR("Function \"" . $this->template_functions[$funcname][0] . "\" not found for template \"" . $funcname . "\"");
            }
            return true;
        }
        return false;
    }

    public function &apx(): \apexx
    {
        return $this->apx;
    }

    protected function registerSetup(\Setup &$setup)
    {
        $this->setup = &$setup;
    }

    /**
     * @return \Setup 
     */
    public function &setup()
    {
        if( !isset($this->setup) )
            return NULL;
        return $this->setup;
    }

    /**
     * Register a Object type that will be created for another 
     * module to use functions of this module
     * @param $name Name of the object
     * @param $type Typestring od the object that will be created
     */
    protected function registerObjectType(string $name, string $type)
    {
        $this->objectTypes[$name] = $type;
    }

    /**
     * Creates a new object by Type name
     * @param $name Typename
     */
    public function createObjectByType(string $name, Array $options) : ?ApexxFunctionality
    {
        if( isset( $this->objectTypes[$name] ) )
        {
            return new $this->objectTypes[$name]($this->apx, $options);
        }
        else
        {
            return NULL;
        }
    }

    protected function registerAdminModule(\AdminModule &$module)
    {        
        $this->adminModule = &$module;
    }

    public function executeAdmin(string $action)
    {  
        if(isset($this->adminModule))
        {
            $this->adminModule->executeAction($action);
        }
        else
        {
            throw new Exception("Can not execute Admin action on NULL!");
        }
    }

    protected function registerPublicModule(\PublicModule &$module)
    {        
        $this->publicModule = &$module;
    }

    public function executePublic(string $action)
    {
        $this->publicModule->executeAction($action);
    }

    public function debugCheck()
    {
        if( !isset( $this->publicModule ) )
        {
            ApexxError::WARNING( "\"".$this->id()."\" has no public module!");
            return;
        }        
        $this->publicModule->debugCheckActions();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @deprecated
     */
    protected function register_template_function(string $id, string $function_name, bool $has_parameters, string $description = "", array $parameters = array())
    {
        $this->template_functions[$id] = array($function_name, $has_parameters, $description, $parameters);
    }

    /**
     * @deprecated
     */
    protected function register_admin_template_function(string $id, string $function_name, bool $has_parameters, string $description = "", array $parameters = array())
    {
        $this->template_functions[$id] = array($function_name, $has_parameters, $description, $parameters);
    }

    /**
     * @deprecated
     */
    protected function register_admin_action(string $action_name, int $special_right, int $visible, int $order, int $rights_for_all = 0)
    {
        $this->admin_actions[$action_name] = array(
            $special_right,
            $visible,
            $order,
            $rights_for_all
        );
    }

    /**
     * @deprecated
     */
    public function get_info()
    {
        return $this->info;
    }

    /**
     * @deprecated
     */
    public function get_admin_actions()
    {
        if( isset($this->adminModule))
            return $this->adminModule->getActions();
        else
            return $this->admin_actions;
    }

    /**
     * @deprecated
     */
    public function get_admin_template_functions()
    {
        return $this->admin_template_functions;
    }

    /**
     * @deprecated
     */
    public function get_template_functions()
    {        
        return $this->template_functions;
    } 
}

?>