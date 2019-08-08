<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Api;

/**
 * Interface CartItemManagementInterface
 * @package Aheadworks\OneStepCheckout\Api
 * @api
 */
interface CartItemManagementInterface
{
    /**
     * Remove item from cart
     *
     * @param int $itemId
     * @param int $cartId
     * @return \Aheadworks\OneStepCheckout\Api\Data\CartItemUpdateDetailsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function remove($itemId, $cartId);

    /**
     * Update cart item
     *
     * @param \Magento\Quote\Api\Data\TotalsItemInterface $item
     * @param int $cartId
     * @return \Aheadworks\OneStepCheckout\Api\Data\CartItemUpdateDetailsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function update(\Magento\Quote\Api\Data\TotalsItemInterface $item, $cartId);
}
