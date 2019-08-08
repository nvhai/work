<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation\PeriodProvider;

use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation\PeriodProviderInterface;

/**
 * Class Quarter
 * @package Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation\PeriodProvider
 */
class Quarter implements PeriodProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPeriods($fromDate, $toDate)
    {
        $periods = [];
        $date = (new \DateTime($fromDate))->setTime(0, 0, 0);
        $endDate = (new \DateTime($toDate))->setTime(0, 0, 0);

        $startYear = (int)$date->format('Y');
        $date->setDate($startYear, 1, 1);

        while ($date <= $endDate) {
            $date->modify('first day of');
            $periodFromDate = $date->format('Y-m-d');
            $date->modify('+2 month');
            $date->modify('last day of');
            $periodToDate = $date->format('Y-m-d');
            $date->modify('+1 day');
            $periods[] = ['from' => $periodFromDate, 'to' => $periodToDate];
        }

        return $periods;
    }
}
