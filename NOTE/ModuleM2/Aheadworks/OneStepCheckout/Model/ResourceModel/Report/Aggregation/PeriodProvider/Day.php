<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation\PeriodProvider;

use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation\PeriodProviderInterface;

/**
 * Class Day
 * @package Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation\PeriodProvider
 */
class Day implements PeriodProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPeriods($fromDate, $toDate)
    {
        $periods = [];
        $date = (new \DateTime($fromDate))->setTime(0, 0, 0);
        $endDate = (new \DateTime($toDate))->setTime(0, 0, 0);

        while ($date <= $endDate) {
            $periodDate = $date->format('Y-m-d');
            $periods[] = ['from' => $periodDate, 'to' => $periodDate];
            $date->modify('+1 day');
        }

        return $periods;
    }
}
