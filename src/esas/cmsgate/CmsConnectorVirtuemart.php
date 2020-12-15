<?php
/**
 * Created by IntelliJ IDEA.
 * User: nikit
 * Date: 13.04.2020
 * Time: 12:23
 */

namespace esas\cmsgate;
if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');

use esas\cmsgate\descriptors\CmsConnectorDescriptor;
use esas\cmsgate\descriptors\VendorDescriptor;
use esas\cmsgate\descriptors\VersionDescriptor;
use esas\cmsgate\virtuemart\CmsgateModel;
use esas\cmsgate\wrappers\OrderWrapper;
use esas\cmsgate\wrappers\OrderWrapperVirtuemart;
use VmConfig;
use VmModel;

class CmsConnectorVirtuemart extends CmsConnectorJoomla
{
    private $orderModel;
    /**
     * @var CmsgateModel
     */
    private $moduleModel;

    public function __construct()
    {
        VmConfig::loadConfig();
        parent::__construct();
    }

    /**
     * @return bool|mixed
     */
    public function getOrderModel()
    {
        if ($this->orderModel == null)
            $this->orderModel = VmModel::getModel('orders');
        return $this->orderModel;
    }

    /**
     * @return CmsgateModel
     */
    public function getModuleModel()
    {
        if ($this->moduleModel == null) //вынесено из конструктора, т.к. в нем нельзя обращаться к Registry
            $this->moduleModel = VmModel::getModel(Registry::getRegistry()->getModuleDescriptor()->getModuleMachineName());
        return $this->moduleModel;
    }



    /**
     * Для удобства работы в IDE и подсветки синтаксиса.
     * @return $this
     */
    public static function getInstance()
    {
        return Registry::getRegistry()->getCmsConnector();
    }

    public function createCommonConfigForm($managedFields)
    {
        return null; //not implemented
    }

    public function createSystemSettingsWrapper()
    {
        return null; // not implemented
    }

    /**
     * По локальному id заказа возвращает wrapper
     * @param $orderId
     * @return OrderWrapper
     */
    public function createOrderWrapperByOrderId($orderId)
    {
        $orderInfo = $this->orderModel->getOrder($orderId);
        return new OrderWrapperVirtuemart($orderInfo);
    }

    public function createOrderWrapperByOrderNumber($orderNumber)
    {
        $orderInfo = $this->orderModel->getOrderIdByOrderNumber($orderNumber);
        return new OrderWrapperVirtuemart($orderInfo);
    }

    /**
     * Возвращает OrderWrapper для текущего заказа текущего пользователя
     * @return OrderWrapper
     */
    public function createOrderWrapperForCurrentUser()
    {
        return null; //not implemented
    }

    /**
     * По номеру транзакции внешней система возвращает wrapper
     * @param $extId
     * @return OrderWrapper
     */
    public function createOrderWrapperByExtId($extId)
    {
        $orderId = $this->moduleModel->getOrderIdByExtTrxId($extId);
        return $this->createOrderWrapperByOrderId($orderId);
    }

    public function createConfigStorage()
    {
        return new ConfigStorageVirtuemart();
    }

    public function getConstantConfigValue($key)
    {
        switch ($key) {
            case ConfigFields::useOrderNumber():
                return true;
            default:
                return parent::getConstantConfigValue($key);
        }
    }

    public function createCmsConnectorDescriptor()
    {
        return new CmsConnectorDescriptor(
            "cmsgate-virtuemart-lib",
            new VersionDescriptor(
                "v1.0.2",
                "2020-12-15"
            ),
            "Cmsgate Virtuemart connector",
            "https://bitbucket.esas.by/projects/CG/repos/cmsgate-virtuemart-lib/browse",
            VendorDescriptor::esas(),
            "virtuemart",
            "plugin"
        );
    }
}