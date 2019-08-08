<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Api;

/**
 * Interface GuestCheckoutSectionsManagementInterface
 * @package Aheadworks\OneStepCheckout\Api
 * @api
 */
interface GuestCheckoutSectionsManagementInterface
{
    /**
     * Get sections details
     *
     * @param string $cartId
     * @param \Aheadworks\OneStepCheckout\Api\Data\CheckoutSectionInformationInterface[] $sections
     * @param \Magento\Quote\Api\Data\AddressInterface $shippingAddress
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return \Aheadworks\OneStepCheckout\Api\Data\CheckoutSectionsDetailsInterface
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getSectionsDetails(
        $cartId,
        $sections,
        \Magento\Quote\Api\Data\AddressInterface $shippingAddress,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    );
}
