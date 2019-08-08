<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Api;

/**
 * Interface GuestPaymentMethodsManagementInterface
 * @package Aheadworks\OneStepCheckout\Api
 * @api
 */
interface GuestPaymentMethodsManagementInterface
{
    /**
     * Get list of available payment methods list
     *
     * @param string $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface $shippingAddress
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return \Magento\Quote\Api\Data\PaymentMethodInterface[]
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getPaymentMethods(
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $shippingAddress,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    );
}
