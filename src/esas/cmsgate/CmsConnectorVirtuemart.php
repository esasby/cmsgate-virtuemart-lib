<?php
/**
 * Created by IntelliJ IDEA.
 * User: nikit
 * Date: 13.04.2020
 * Time: 12:23
 */

namespace esas\cmsgate;
if (!class_exists('VmModel'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'vmmodel.php');

use esas\cmsgate\cscart\CSCartPaymentMethod;
use esas\cmsgate\cscart\CSCartPaymentProcessor;
use esas\cmsgate\descriptors\CmsConnectorDescriptor;
use esas\cmsgate\descriptors\VendorDescriptor;
use esas\cmsgate\descriptors\VersionDescriptor;
use esas\cmsgate\lang\LocaleLoaderVirtuemart;
use esas\cmsgate\utils\RequestParamsCSCart;
use esas\cmsgate\virtuemart\CmsgateModel;
use esas\cmsgate\wrappers\OrderWrapper;
use esas\cmsgate\wrappers\OrderWrapperVirtuemart;
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
        parent::__construct();
        $this->orderModel = VmModel::getModel('orders');
        $this->moduleModel = VmModel::getModel(Registry::getRegistry()->getModuleDescriptor()->getModuleMachineName());
    }

    /**
     * @return bool|mixed
     */
    public function getOrderModel()
    {
        return $this->orderModel;
    }

    /**
     * @return CmsgateModel
     */
    public function getModuleModel()
    {
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
                "v1.0.1",
                "2020-12-02"
            ),
            "Cmsgate Virtuemart connector",
            "https://bitbucket.esas.by/projects/CG/repos/cmsgate-virtuemart-lib/browse",
            VendorDescriptor::esas(),
            "virtuemart",
            "plugin"
        );
    }
}