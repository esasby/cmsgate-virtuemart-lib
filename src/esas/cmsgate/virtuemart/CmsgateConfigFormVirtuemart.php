<?php


namespace esas\cmsgate\virtuemart;


use esas\cmsgate\joomla\CmsgateConfigFormJoomla;

class CmsgateConfigFormVirtuemart extends CmsgateConfigFormJoomla
{
    public function getInjectionPath()
    {
        return "vmconfig/fields";
    }

}