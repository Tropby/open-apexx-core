<?php

interface IPublicModule
{

}

class PublicModule implements IPublicModule
{
    /** @var \PublicModule[] */
    private $registeredActions = array();

    /** @var \Module */
    private \Module $module;

    /** @var Array */
    private array $templateFunctions = array();

    /**
     * 
     */
    public function __construct(\Module &$module)
    {
        $this->module = &$module;
    }

    public function &module() : \Module
    {
        return $this->module;
    }

    /**
     * 
     * @param action Name of the 
     */
    protected function registerAction(string $action)
    {
        $m = $this->module->getId();
        $m = strtoupper($m[0]).substr($m, 1);
        $actionClass = "\\Modules\\".$m."\\PublicAction\\".$action;
        $this->registeredModules[$action] = $actionClass;
    }

    /**
     * @param string $functionName name of the tremplate class
     * @param string $templateId function name to call the template class
     * @return void
     */
    public function registerTemplateFunction(string $functionName, string $templateId) : void
    {
        $m = "\\Modules\\" . ucwords($this->module->getId()) . "\\PublicTemplateFunction\\" . ucwords($functionName);
        $this->templateFunctions[$templateId] = $m;
    }

    /**
     * @param string $templateId Tempalte Id to execute
     * @param array $params Array of parameters to execute the template
     * @return bool Template found and executed!
     */
    public function executeTemplateFunction(string $templateId, array $params): bool
    {
        if ($this->templateFunctions[$templateId])
        {
            $t = $this->templateFunctions[$templateId];
            $t = new $t($this->module);
            if (is_callable(array($t, "execute")))
            {
                call_user_func_array(array($t, "execute"), $params);
            }
            else
            {
                ApexxError::ERROR("Called template object without execute method!");
            }
            return true;
        }
        return false;
    }

    /**
     * Executes a public action
     * @param action Name of the public action to execute
     */
    public function executeAction(string $action)
    {
        if( $this->registeredModules[$action]??NULL )
        {
            $obj = new $this->registeredModules[$action]($this);
            $obj->execute($this->apx);
        }
        else
        {
            ApexxError::ERROR("Action \"".$action."\" not found in module \"".$this->module->getId()."\"!");
        }
    }

    public function debugCheckActions()
    {
        foreach($this->registeredModules as $action)
        {
            ApexxError::DEBUG("Create action \"".$action."\"");
            new $action($this);
        }
    }

    public function debugCheckTemplate()
    {
        foreach ($this->templateFunctions as $id => $t)
        {
            ApexxError::DEBUG("Create template function \"" . $id . "\"");
            $t = new $t($this->module);
            if (!is_callable(array($t, "execute")))
            {
                ApexxError::ERROR("Template object ".$id." without execute method!");
            }
        }
    }

}

?>