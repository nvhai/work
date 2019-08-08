<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Report;

/**
 * Class Aggregation
 * @package Aheadworks\OneStepCheckout\Model\Report
 */
class Aggregation
{
    /**
     * Get aggregations
     *
     * @return array
     */
    public function getAggregations()
    {
        return ['day', 'week', 'month', 'quarter', 'year'];
    }
}
