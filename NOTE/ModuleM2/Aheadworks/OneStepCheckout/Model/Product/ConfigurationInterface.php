<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Product;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

/**
 * Interface ConfigurationInterface
 * @package Aheadworks\OneStepCheckout\Model\Product
 */
interface ConfigurationInterface
{
    /**
     * todo: ItemInterface, consider another interface
     * Get options array
     *
     * @param ItemInterface $item
     * @return array
     */
    public function getOptions(ItemInterface $item);

    /**
     * todo: ItemInterface, consider another interface
     * Set options to item
     *
     * @param ItemInterface $item
     * @param array $optionsData
     * @return $this
     */
    public function setOptions(ItemInterface $item, $optionsData = []);
}
