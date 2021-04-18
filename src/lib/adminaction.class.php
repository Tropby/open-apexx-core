<?php

interface IAdminAction
{
    public function execute();
}

abstract class AdminAction implements IAdminAction
{
    /**
     * @var \AdminModule
     */
    private \AdminModule $adminModule;

    public function __construct(\AdminModule &$adminModule)
    {
        $this->adminModule = &$adminModule;

        if( $this->adminModule->module()->apx()->config("debugCheck"))
        {
            ApexxError::DEBUG("Action Created!");
        }
    }

    public function &adminModule() : \AdminModule
    {
        return $this->adminModule;
    }
}
