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
use esas\cmsgate\virtuemart\CmsgateModelVirtuemart;
use esas\cmsgate\wrappers\OrderWrapper;
use esas\cmsgate\wrappers\OrderWrapperVirtuemart;
use Exception;
use Joomla\CMS\Uri\Uri;
use VmConfig;
use VmModel;

class CmsConnectorVirtuemart extends CmsConnectorJoomla
{
    private $orderModel;
    /**
     * @var CmsgateModelVirtuemart
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
     * @return CmsgateModelVirtuemart
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
        $orderInfo = $this->getOrderModel()->getOrder($orderId);
        if (empty($orderInfo))
            throw new Exception("Incorrect orderId[" . $orderId . "]");
        return new OrderWrapperVirtuemart($orderInfo);
    }

    public function createOrderWrapperByOrderNumber($orderNumber)
    {
        $orderId = $this->getOrderModel()->getOrderIdByOrderNumber($orderNumber);
        if (empty($orderId))
            throw new Exception("Incorrect orderNumber[" . $orderNumber . "]");
        return $this->createOrderWrapperByOrderId($orderId);
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
                "v1.1.1",
                "2020-12-23"
            ),
            "Cmsgate Virtuemart connector",
            "https://bitbucket.esas.by/projects/CG/repos/cmsgate-virtuemart-lib/browse",
            VendorDescriptor::esas(),
            "virtuemart",
            "plugin"
        );
    }

    public static function generateControllerPath($controller, $task)
    {
        return "index.php?option=com_virtuemart&view=" . $controller . "&task=" . $task;
    }

    public static function generatePaySystemControllerPath($task)
    {
        return self::generateControllerPath(Registry::getRegistry()->getPaySystemName(), $task);
    }

    public static function generatePaySystemControllerUrl($task)
    {
        return Uri::root() . self::generatePaySystemControllerPath($task);
    }
}