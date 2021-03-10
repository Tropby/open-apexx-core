<?php

class ApexxFunctionality
{
    protected \Apexx $apx;
    protected Array $options;

    public function __construct(\Apexx $apx, Array $options)
    {
        $this->apx = $apx;
        $this->options = $options;
    }
}

?>