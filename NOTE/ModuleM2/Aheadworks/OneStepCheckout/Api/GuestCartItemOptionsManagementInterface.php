<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Api;

/**
 * Interface GuestCartItemOptionsManagementInterface
 * @package Aheadworks\OneStepCheckout\Api
 * @deprecated
 */
interface GuestCartItemOptionsManagementInterface
{
    /**
     * Update cart item options
     *
     * @param int $itemId
     * @param string $cartId
     * @param string $options
     * @return \Aheadworks\OneStepCheckout\Api\Data\CartItemOptionsDetailsInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function update($itemId, $cartId, $options);
}
