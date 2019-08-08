<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation;

/**
 * Interface PeriodProviderInterface
 * @package Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation
 */
interface PeriodProviderInterface
{
    /**
     * Get periods for aggregation
     *
     * @param string $fromDate
     * @param string $toDate
     * @return array
     */
    public function getPeriods($fromDate, $toDate);
}
