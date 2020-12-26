<?php

interface IPublicAction
{
    public function execute();
}

abstract class PublicAction implements IPublicAction
{
    /**
     * @var \PublicModule
     */
    private \PublicModule $publicModule;

    public function __construct(\PublicModule &$publicModule)
    {
        $this->publicModule = &$publicModule;

        if( $this->publicModule->module()->apx()->config("debugCheck"))
        {
            ApexxError::DEBUG("Action Created!");
        }
    }

    public function &publicModule() : \PublicModule
    {
        return $this->publicModule;
    }
}
