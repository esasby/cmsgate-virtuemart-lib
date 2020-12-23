<?php
namespace esas\cmsgate\virtuemart;
define('PATH_VIRTUEMART', JPATH_SITE . '/components/com_virtuemart/');

use DOMDocument;
use esas\cmsgate\ConfigFields;
use esas\cmsgate\joomla\InstallHelperJoomla;
use esas\cmsgate\Registry;
use esas\cmsgate\utils\XMLUtils;
use Exception;
use JDatabaseQuery;
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
    public static function dbPaymentMethodAdd()
    {
        $extensionId = CmsgateModelVirtuemart::getExtensionId();
        if (empty($extensionId)) {
            throw new Exception('Can not detect extension id');
        }
        if (!empty(CmsgateModelVirtuemart::getPaymentMethodId()))
            return;
        $paymentMethod = new stdClass();
        $paymentMethod->payment_jplugin_id = $extensionId;
        $paymentMethod->payment_element = Registry::getRegistry()->getModuleDescriptor()->getModuleMachineName();
        $paymentMethod->published = 1;
        $paymentMethod->ordering = 0;
        $paymentMethod->shared = 0;
        $paymentMethod->virtuemart_vendor_id = 1;
        $paymentMethod->payment_params = self::createDefaultParams();
        if (!JFactory::getDbo()->insertObject(
            CmsgateModelVirtuemart::DB_TABLE_VIRTUEMART_PAYMENT_METHODS,
            $paymentMethod,
            CmsgateModelVirtuemart::DB_FIELD_PAYMENT_METHOD_ID))
            throw new Exception('Can not add new payment method');
        foreach (JLanguageHelper::getLanguages() as $lang) {
            $tableName = CmsgateModelVirtuemart::DB_TABLE_VIRTUEMART_PAYMENT_METHODS . "_" . str_replace("-", "_", strtolower($lang->lang_code));
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

    public static function dbPaymentMethodDelete()
    {
        $paymentMethodId = CmsgateModelVirtuemart::getPaymentMethodId();
        if (empty(CmsgateModelVirtuemart::getPaymentMethodId()))
            return;
        $db = JFactory::getDbo();
        /** @var JDatabaseQuery $query */
        $query = $db->getQuery(true);
        foreach (JLanguageHelper::getLanguages() as $lang) {
            $tableName = CmsgateModelVirtuemart::DB_TABLE_VIRTUEMART_PAYMENT_METHODS . "_" . str_replace("-", "_", strtolower($lang->lang_code));
            if (!CmsgateModelVirtuemart::isTableExists($tableName))
                continue;
            $query->delete($tableName);
            $query->where($db->quoteName(CmsgateModelVirtuemart::DB_FIELD_PAYMENT_METHOD_ID) . ' = ' . $paymentMethodId);
            $db->setQuery($query);
            $db->execute();
            $query->clear();
        }
        $query->delete($db->quoteName(CmsgateModelVirtuemart::DB_TABLE_VIRTUEMART_PAYMENT_METHODS));
        $query->where($db->quoteName(CmsgateModelVirtuemart::DB_FIELD_PAYMENT_METHOD_ID) . ' = ' . $paymentMethodId);
        $db->setQuery($query);
        return $db->execute();
    }

    private static function createDefaultParams()
    {
        $params = array();
        foreach (Registry::getRegistry()->getManagedFieldsFactory()->getManagedFields() as $managedField) {
            $params[] = $managedField->getKey() . '=' . json_encode($managedField->getDefault());
        }
        return implode("|", $params);
    }

    public static function deleteFiles() {
        $ret = true;
        $ret = $ret && self::deleteWithLogging(PATH_VIRTUEMART . 'models/' . Registry::getRegistry()->getPaySystemName() . ".php");
        $ret = $ret && self::deleteWithLogging(PATH_VIRTUEMART . 'controllers/' . Registry::getRegistry()->getPaySystemName().  ".php");
        return $ret;
    }
}