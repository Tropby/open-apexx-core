<?php

interface ISetup
{
    function install(\Apexx $apx);
    function update(\Apexx $apx, int $installed_version);
    function uninstall(\Apexx $apx);
}

abstract class Setup implements ISetup
{

}