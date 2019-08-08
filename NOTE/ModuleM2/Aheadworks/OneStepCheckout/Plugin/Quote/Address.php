<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Plugin\Quote;

use Magento\Quote\Api\Data\AddressInterface;

/**
 * Class Address
 * @package Aheadworks\OneStepCheckout\Plugin\Quote
 */
class Address
{
    /**
     * @param AddressInterface $subject
     * @param \Closure $proceed
     * @param string|string[] $street
     * @return null|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSetStreet(AddressInterface $subject, \Closure $proceed, $street)
    {
        return empty($street) ? $proceed('') : $proceed($street);
    }
}
