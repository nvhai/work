<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Ui\DataProvider;

use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\FilterableInterface;

/**
 * Class DefaultFilterPool
 * @package Aheadworks\OneStepCheckout\Ui\DataProvider
 */
class DefaultFilterPool
{
    /**
     * @var DefaultFilterApplierInterface[]
     */
    private $appliers;

    /**
     * @param array $appliers
     */
    public function __construct(array $appliers = [])
    {
        $this->appliers = $appliers;
    }

    /**
     * Apply default filters to report collection
     *
     * @param FilterableInterface $collection
     * @return void
     */
    public function applyFilters(FilterableInterface $collection)
    {
        foreach ($this->appliers as $applier) {
            $applier->apply($collection);
        }
    }
}
