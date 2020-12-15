<?php


namespace esas\cmsgate\virtuemart;


use DOMDocument;
use esas\cmsgate\Registry;
use esas\cmsgate\utils\XMLUtils;
use Exception;
use JText;

class InstallHelperVirtuemart
{
    const CONFIG_XPATH_STR = "/extension/vmconfig/fields[@name='params']";

    public static function generateVmConfig()
    {
        try {
            $filePath = JPATH_PLUGINS . DS . "vmpayment" . DS .  Registry::getRegistry()->getModuleDescriptor()->getModuleMachineName() . DS . Registry::getRegistry()->getModuleDescriptor()->getModuleMachineName() . ".xml";
            $moduleConfigXML = new DOMDocument();
            $moduleConfigXML->load($filePath);
            XMLUtils::truncatePath($moduleConfigXML, self::CONFIG_XPATH_STR);
            foreach (Registry::getRegistry()->getConfigFormsArray() as $configForm) {
                $fieldSet = $configForm->generate();
                XMLUtils::inject($moduleConfigXML, $fieldSet, self::CONFIG_XPATH_STR);
            }
            XMLUtils::saveFormatted($moduleConfigXML, $filePath);
        } catch (Exception $e) {
            echo JText::sprintf($e->getMessage());
            return false;
        }
    }
}