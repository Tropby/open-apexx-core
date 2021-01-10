<?php

interface IPublicTemplateFunction 
{    
}

abstract class PublicTemplateFunction implements IPublicTemplateFunction 
{
    private \PublicModule $publicModule;

    public function __construct(\PublicModule &$publicModule)
    {
        $this->publicModule = &$publicModule;
    }

    protected function publicModule() : \PublicModule
    {
        return $this->publicModule;
    }
}