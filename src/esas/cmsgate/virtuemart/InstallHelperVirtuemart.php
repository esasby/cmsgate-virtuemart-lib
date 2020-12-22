<?php


namespace esas\cmsgate\virtuemart;


use DOMDocument;
use esas\cmsgate\ConfigFields;
use esas\cmsgate\joomla\InstallHelperJoomla;
use esas\cmsgate\Registry;
use esas\cmsgate\utils\XMLUtils;
use Exception;
use \JFactory;
use \JLanguageHelper;
use stdClass;

class InstallHelperVirtuemart extends InstallHelperJoomla
{
    const CONFIG_XPATH_STR = "/extension/vmconfig/fields[@name='params']";

    /**
     * @return void
     * @throws Exception
     */
    public static function generateVmConfig()
    {
        $filePath = JPATH_PLUGINS . DS . "vmpayment" . DS . Registry::getRegistry()->getModuleDescriptor()->getModuleMachineName() . DS . Registry::getRegistry()->getModuleDescriptor()->getModuleMachineName() . ".xml";
        $moduleConfigXML = new DOMDocument();
        $moduleConfigXML->load($filePath);
        XMLUtils::truncatePath($moduleConfigXML, self::CONFIG_XPATH_STR);
        foreach (Registry::getRegistry()->getConfigFormsArray() as $configForm) {
            $fieldSet = $configForm->generate();
            XMLUtils::inject($moduleConfigXML, $fieldSet, self::CONFIG_XPATH_STR);
        }
        XMLUtils::saveFormatted($moduleConfigXML, $filePath);
    }

    /**
     * @throws Exception
     */
    public static function dbAddPaymentMethod()
    {
        $extensionId = CmsgateModelVirtuemart::getExtensionId();
        if (empty($extensionId)) {
            throw new Exception('Can not detect extension id');
        }
        $db = JFactory::getDBO();
        $query = "SELECT * FROM `#__virtuemart_paymentmethods` WHERE payment_element = " . $db->quote(Registry::getRegistry()->getModuleDescriptor()->getModuleMachineName());
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if (count($rows) != 0)
            return;
        $paymentMethod = new stdClass();
        $paymentMethod->payment_jplugin_id = $extensionId;
        $paymentMethod->payment_element = Registry::getRegistry()->getModuleDescriptor()->getModuleMachineName();
        $paymentMethod->published = 1;
        $paymentMethod->ordering = 0;
        $paymentMethod->shared = 0;
        $paymentMethod->virtuemart_vendor_id = 1;
        $paymentMethod->payment_params = self::createDefaultParams();
        if (!JFactory::getDbo()->insertObject('#__virtuemart_paymentmethods', $paymentMethod, "virtuemart_paymentmethod_id"))
            throw new Exception('Can not add new payment method');
        foreach (JLanguageHelper::getLanguages() as $lang) {
            $tableName = '#__virtuemart_paymentmethods' . "_" . str_replace("-", "_", strtolower($lang->lang_code));
            if (!CmsgateModelVirtuemart::isTableExists($tableName))
                continue;
            $paymentMethod_i18n = new stdClass();
            $paymentMethod_i18n->virtuemart_paymentmethod_id = $paymentMethod->virtuemart_paymentmethod_id;
            $paymentMethod_i18n->payment_name = Registry::getRegistry()->getTranslator()->getConfigFieldDefault(ConfigFields::paymentMethodName(), $lang->language);
            $paymentMethod_i18n->payment_desc = Registry::getRegistry()->getTranslator()->getConfigFieldDefault(ConfigFields::paymentMethodDetails(), $lang->language);
            $paymentMethod_i18n->slug = Registry::getRegistry()->getModuleDescriptor()->getModuleMachineName();
            if (!JFactory::getDbo()->insertObject($tableName, $paymentMethod_i18n))
                throw new Exception('Can not add new payment method i18n');
        }
    }

    private static function createDefaultParams() {
        $params = array();
        foreach (Registry::getRegistry()->getManagedFieldsFactory()->getManagedFields() as $managedField) {
            $params[] = $managedField->getKey() . '=' . json_encode($managedField->getDefault());
        }
        return implode("|", $params);
    }
}