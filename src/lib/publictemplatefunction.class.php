<?php

interface IPublicTemplateFunction 
{    
}

abstract class PublicTemplateFunction implements IPublicTemplateFunction 
{
    private \Module $module;
    private string $templateId;

    public function __construct(\Module &$module)
    {
        $this->module = &$module;
    }

    protected function module()
    {
        return $this->module;
    }
}