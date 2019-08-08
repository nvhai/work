<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Ui\DataProvider;

use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\FilterableInterface;

/**
 * Interface DefaultFilterApplierInterface
 * @package Aheadworks\OneStepCheckout\Ui\DataProvider
 */
interface DefaultFilterApplierInterface
{
    /**
     * Apply default filter
     *
     * @param FilterableInterface $collection
     * @return void
     */
    public function apply(FilterableInterface $collection);
}
