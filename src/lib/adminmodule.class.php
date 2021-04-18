<?php

interface IAdminModule
{

}

class AdminModule implements IAdminModule
{
    private \Module $module;

    public function __construct(\Module &$module)
    {
        $this->module = &$module;
    }

    public function &module(): \Module
    {
        return $this->module;
    }

    /**
     * 
     * @param action Name of the 
     */
    protected function registerAction(string $action, int $special_right = 0, int $visible = 0, int $order = -1, int $rights_for_all = 0)
    {
        $m = $this->module->id();
        $actionClass = "\\Modules\\" . ucwords($m) . "\\AdminAction\\" . ucwords($action);
        $this->registeredModules[$action] = array( $special_right, $visible, $order, $rights_for_all, $actionClass);
    }

    public function getActions()
    {
        return $this->registeredModules;
    }

    /**
     * @param string $functionName name of the tremplate class
     * @param string $templateId function name to call the template class
     * @return void
     */
    public function registerTemplateFunction(string $functionName, string $templateId): void
    {
        $m = "\\Modules\\" . ucwords($this->module->id()) . "\\AdminTemplateFunction\\" . ucwords($functionName);
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
            $t = new $t($this);
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
        if ($this->registeredModules[$action] ?? NULL)
        {
            $obj = new $this->registeredModules[$action][4]($this);
            $obj->execute($this->apx);
        }
        else
        {
            ApexxError::ERROR("Action \"" . $action . "\" not found in module \"" . $this->module->id() . "\"!");
        }
    }    
}