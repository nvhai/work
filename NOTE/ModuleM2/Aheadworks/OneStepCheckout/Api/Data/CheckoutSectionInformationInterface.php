<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Api\Data;

/**
 * Interface CheckoutSectionInformationInterface
 * @package Aheadworks\OneStepCheckout\Api\Data
 */
interface CheckoutSectionInformationInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const CODE = 'code';
    /**#@-*/

    /**
     * Get section code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set section code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);
}
