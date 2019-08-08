<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation\PeriodProvider;

use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation\PeriodProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Week
 * @package Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation\PeriodProvider
 */
class Week implements PeriodProviderInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getPeriods($fromDate, $toDate)
    {
        $periods = [];
        $date = (new \DateTime($fromDate))->setTime(0, 0, 0);
        $endDate = (new \DateTime($toDate))->setTime(0, 0, 0);

        $firstDayOfWeek = $this->scopeConfig->getValue('general/locale/firstday');
        $startDayOfWeek = $date->format('N');
        if ($startDayOfWeek > $firstDayOfWeek) {
            $date->modify('-' . $startDayOfWeek - $firstDayOfWeek . ' day');
        } else {
            $date->modify('+' . $startDayOfWeek - $firstDayOfWeek . ' day');
            $date->modify('-6 day');
        }

        while ($date <= $endDate) {
            $periodFromDate = $date->format('Y-m-d');
            $date->modify('+1 week')->modify('-1 day');
            $periodToDate = $date->format('Y-m-d');
            $date->modify('+1 day');
            $periods[] = ['from' => $periodFromDate, 'to' => $periodToDate];
        }

        return $periods;
    }
}
