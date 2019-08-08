<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Api;

/**
 * Interface GuestDataFieldCompletenessLoggerInterface
 * @package Aheadworks\OneStepCheckout\Api
 */
interface GuestDataFieldCompletenessLoggerInterface
{
    /**
     * Log guest checkout fields completeness data
     *
     * @param string $cartId
     * @param \Aheadworks\OneStepCheckout\Api\Data\DataFieldCompletenessInterface[] $fieldCompleteness
     * @return void
     */
    public function log($cartId, array $fieldCompleteness);
}
