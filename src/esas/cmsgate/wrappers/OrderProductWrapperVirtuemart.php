<?php
/**
 * Created by PhpStorm.
 * User: nikit
 * Date: 27.09.2018
 * Time: 14:01
 */

namespace esas\cmsgate\wrappers;

use Throwable;

class OrderProductWrapperVirtuemart extends OrderProductSafeWrapper
{
    private $product;

    /**
     * @param $orderProduct
     */
    public function __construct($product)
    {
        parent::__construct();
        $this->product = $product;
    }


    /**
     * Артикул товара
     * @throws Throwable
     * @return string
     */
    public function getInvIdUnsafe()
    {
        return $this->product->order_item_sku;
    }

    /**
     * Название или краткое описание товара
     * @throws Throwable
     * @return string
     */
    public function getNameUnsafe()
    {
        return $this->product->order_item_name;
    }

    /**
     * Количество товароа в корзине
     * @throws Throwable
     * @return mixed
     */
    public function getCountUnsafe()
    {
        return $this->product->product_quantity;
    }

    /**
     * Цена за единицу товара
     * @throws Throwable
     * @return mixed
     */
    public function getUnitPriceUnsafe()
    {
        return $this->product->product_final_price;
    }
}