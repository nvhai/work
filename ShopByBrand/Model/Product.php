<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\ShopByBrand\Model;


class Product extends \Magento\Framework\Model\AbstractModel
{
    const BRANDS_ID = 'brands_id';
    protected function _construct()
    {
        $this->_init('Vnecoms\ShopByBrand\Model\ResourceModel\Product');

    }

}