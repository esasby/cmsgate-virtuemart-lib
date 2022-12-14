<?php

namespace esas\cmsgate\virtuemart;

use esas\cmsgate\joomla\CmsgateModelJoomla;
use esas\cmsgate\Registry;
use esas\cmsgate\utils\Logger;
use JDatabaseQuery;
use \JFactory;
use VmConfig;
use vmLanguage;
use vmText;

class CmsgateModelVirtuemart extends CmsgateModelJoomla
{
    const DB_FIELD_EXT_TRX_ID = 'ext_trx_id';
    const DB_FIELD_ORDER_ID = 'virtuemart_order_id';
    const DB_FIELD_PAYMENT_METHOD_ID = 'virtuemart_paymentmethod_id';
    const DB_TABLE_VIRTUEMART_PAYMENT_METHODS = '#__virtuemart_paymentmethods';

    public static function getExtTrxIdByOrderId($orderId)
    {
        $db = JFactory::getDBO();
        $selectField = self::DB_FIELD_EXT_TRX_ID;
        /** @var JDatabaseQuery $query */
        $query = $db->getQuery(true);
        $query
            ->select($selectField)
            ->from(self::getModuleTableName())
            ->where(self::DB_FIELD_ORDER_ID . ' = ' . $orderId);
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if (count($rows) != 1) {
            Logger::getLogger(get_class(static::class))->error('Can not load extTrxId by orderId[' . $orderId . "]");
            return null;
        }
        return $rows[0]->$selectField;
    }

    public static function getOrderIdByExtTrxId($extTrxId)
    {
        $db = JFactory::getDBO();
        $selectField = self::DB_FIELD_ORDER_ID;
        /** @var JDatabaseQuery $query */
        $query = $db->getQuery(true);
        $query
            ->select($selectField)
            ->from(self::getModuleTableName())
            ->where(self::DB_FIELD_EXT_TRX_ID . " = '" . $extTrxId . "'");
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if (count($rows) != 1) {
//            saveToLog("payment.log", 'Can not load orderId by extTrxId[' . $extTrxId . "]");
            return null;
        }
        return $rows[0]->$selectField;
    }

    public static function saveExtTrxId($orderId, $extTrxId)
    {
        $db = JFactory::getDBO();
        /** @var JDatabaseQuery $query */
        $query = $db->getQuery(true);
        $query
            ->insert(self::getModuleTableName())
            ->columns(array($db->quoteName(self::DB_FIELD_ORDER_ID), $db->quoteName(self::DB_FIELD_EXT_TRX_ID)))
            ->values($db->quote($orderId) . ", " . $db->quote($extTrxId));
        $db->setQuery($query);
        if (@$db->execute())
        {
//            saveToLog("payment.log", 'Can not save extTrxId[' . $extTrxId . "]");
        }
    }

    public static function getModuleConfig()
    {
        $db = JFactory::getDBO();
        $selectField = 'payment_params';
        /** @var JDatabaseQuery $query */
        $query = $db->getQuery(true);
        $query
            ->select($selectField)
            ->from(self::DB_TABLE_VIRTUEMART_PAYMENT_METHODS)
            ->where("payment_element = '" . Registry::getRegistry()->getModuleDescriptor()->getModuleMachineName() . "'");
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if (count($rows) != 1) {
//            saveToLog("payment.log", 'Can not load module config');
            return null;
        }
        $ret = array();
        //?????????? ?????????? ???????? ???????? ???? vmtable.php
        $params = explode('|', $rows[0]->$selectField);
        foreach ($params as $item) {
            $item = explode('=', $item);
            $ret[$item[0]] = json_decode($item[1]);
        }
        return $ret;
    }

    public static function getOrderStatuses()
    {
        if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
        VmConfig::loadConfig();
        vmLanguage::loadJLang('com_virtuemart_orders', TRUE);
        $options = array();
        $db = JFactory::getDBO();

        $query = 'SELECT `order_status_code` AS value, `order_status_name` AS text
                 FROM `#__virtuemart_orderstates`
                 WHERE `virtuemart_vendor_id` = 1
                 ORDER BY `ordering` ASC ';

        $db->setQuery($query);
        $values = $db->loadObjectList();
        foreach ($values as $value) {
            $ret[$value->value] = vmText::_($value->text);
        }
        return $ret;
    }

    public static function getPaymentMethodId() {
        $db = JFactory::getDBO();
        /** @var JDatabaseQuery $query */
        $query = $db->getQuery(true);
        $query
            ->select(self::DB_FIELD_PAYMENT_METHOD_ID)
            ->from(self::DB_TABLE_VIRTUEMART_PAYMENT_METHODS)
            ->where("payment_element = '" . Registry::getRegistry()->getModuleDescriptor()->getModuleMachineName() . "'");
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if (count($rows) != 1) {
//            saveToLog("payment.log", 'Can not load module config');
            return null;
        }
        return $rows[0]->virtuemart_paymentmethod_id;
    }

    public static function getModuleTableName()
    {
        return '#__virtuemart_payment_plg_' . Registry::getRegistry()->getModuleDescriptor()->getModuleMachineName();
    }

}