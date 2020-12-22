<?php
/**
 * Created by PhpStorm.
 * User: nikit
 * Date: 27.09.2018
 * Time: 13:08
 */

namespace esas\cmsgate\wrappers;

use esas\cmsgate\CmsConnectorVirtuemart;
use esas\cmsgate\Registry;
use Throwable;

class OrderWrapperVirtuemart extends OrderSafeWrapper
{
    private $orderInfo;
    private $extId;

    /**
     * OrderWrapperVirtuemart constructor.
     */
    public function __construct($orderInfo)
    {
        parent::__construct();
        $this->orderInfo = $orderInfo;
        \JPluginHelper::importPlugin(Registry::getRegistry()->getModuleDescriptor()->getModuleMachineName());
    }


    /**
     * Уникальный номер заказ в рамках CMS
     * @return string
     * @throws Throwable
     */
    public function getOrderIdUnsafe()
    {
        return $this->orderInfo['details']['BT']->virtuemart_order_id;
    }

    public function getOrderNumberUnsafe()
    {
        return $this->orderInfo['details']['BT']->order_number;
    }


    /**
     * Полное имя покупателя
     * @return string
     * @throws Throwable
     */
    public function getFullNameUnsafe()
    {
        return $this->orderInfo['details']['BT']->first_name . " " . $this->orderInfo['details']['BT']->last_name;
    }

    /**
     * Мобильный номер покупателя для sms-оповещения
     * (если включено администратором)
     * @return string
     * @throws Throwable
     */
    public function getMobilePhoneUnsafe()
    {
        return $this->orderInfo['details']['BT']->phone_1;
    }

    /**
     * Email покупателя для email-оповещения
     * (если включено администратором)
     * @return string
     * @throws Throwable
     */
    public function getEmailUnsafe()
    {
        return $this->orderInfo['details']['BT']->email;
    }

    /**
     * Физический адрес покупателя
     * @return string
     * @throws Throwable
     */
    public function getAddressUnsafe()
    {
        return $this->orderInfo['details']['BT']->address_1 . " " . $this->orderInfo['details']['BT']->city;
    }

    /**
     * Общая сумма товаров в заказе
     * @return string
     * @throws Throwable
     */
    public function getAmountUnsafe()
    {
        return $this->orderInfo['details']['BT']->order_total;
    }

    /**
     * Валюта заказа (буквенный код)
     * @return string
     * @throws Throwable
     */
    public function getCurrencyUnsafe()
    {
//        $paymentCurrency = CurrencyDisplay::getInstance($method->payment_currency);
//        $totalInPaymentCurrency = round($paymentCurrency->convertCurrencyTo($method->payment_currency, $order['details']['BT']->order_total, false), 2);
        return $this->orderInfo['details']['BT']->user_currency_id;
    }

    /**
     * Массив товаров в заказе
     * @return OrderProductWrapper[]
     * @throws Throwable
     */
    public function getProductsUnsafe()
    {
        $products = $this->orderInfo['items'];;
        foreach ($products as $product)
            $productsWrappers[] = new OrderProductWrapperVirtuemart($product);
        return $productsWrappers;
    }

    /**
     * BillId (идентификатор хуткигрош) успешно выставленного счета
     * @return mixed
     * @throws Throwable
     */
    public function getExtIdUnsafe()
    {
        if (empty($this->extId))
            $this->extId = CmsConnectorVirtuemart::getInstance()->getModuleModel()->getExtTrxIdByOrderId($this->getOrderId());
        return $this->extId;
    }

    /**
     * Текущий статус заказа в CMS
     * @return mixed
     * @throws Throwable
     */
    public function getStatusUnsafe()
    {
        return $this->orderInfo['details']['BT']->order_status;
    }

    /**
     * Обновляет статус заказа в БД
     * @param $newStatus
     * @return mixed
     * @throws Throwable
     */
    public function updateStatus($newStatus)
    {
        $this->orderInfo['order_status'] = $newStatus;
        $this->orderInfo['customer_notified'] = 1;
        $this->orderInfo['comments'] = '';
        CmsConnectorVirtuemart::getInstance()->getOrderModel()->updateStatusForOneOrder($this->getOrderId(), $this->orderInfo, false);
    }

    /**
     * Сохраняет привязку billid к заказу
     * @param $billId
     * @return mixed
     * @throws Throwable
     */
    public function saveExtId($extId)
    {
//        $dbValues[CmsgateModelVirtuemart::DB_FIELD_ORDER_ID] = $this->getOrderId();
//        $dbValues[CmsgateModelVirtuemart::DB_FIELD_EXT_TRX_ID] = $extId;
//        JEventDispatcher::getInstance()->trigger('storePluginInternalData', array($dbValues));
        CmsConnectorVirtuemart::getInstance()->getModuleModel()->saveExtTrxId($this->getOrderId(), $extId); //через модель более простой и понятный код
        $this->extId = $extId;
    }

    public function getClientIdUnsafe()
    {
        return $this->orderInfo['details']['BT']->virtuemart_user_id;
    }

    public function getShippingAmountUnsafe()
    {
        return $this->orderInfo['details']['BT']->order_shipment;
    }
}