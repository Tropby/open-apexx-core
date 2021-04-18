<?php

interface IPublicTemplateFunction 
{    
}

abstract class PublicTemplateFunction implements IPublicTemplateFunction 
{
    private \IPublicModule $publicModule;

    public function __construct(\IPublicModule &$publicModule)
    {
        $this->publicModule = &$publicModule;
    }

    protected function publicModule() : \IPublicModule
    {
        return $this->publicModule;
    }
}