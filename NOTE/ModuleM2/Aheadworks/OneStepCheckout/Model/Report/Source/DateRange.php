<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Report\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Locale\ListsInterface as LocaleLists;

/**
 * Class DateRange
 * @package Aheadworks\OneStepCheckout\Model\Report\Source
 */
class DateRange implements OptionSourceInterface
{
    /**
     * 'Today' date range
     */
    const TODAY = 'today';

    /**
     * 'Yesterday' date range
     */
    const YESTERDAY = 'yesterday';

    /**
     * 'Last 7 days' date range
     */
    const LAST_7_DAYS = 'last_7_days';

    /**
     * 'Last week' date range
     */
    const LAST_WEEK = 'last_week';

    /**
     * 'Last business week' date range
     */
    const LAST_BUSINESS_WEEK = 'last_business_week';

    /**
     * 'This month' date range
     */
    const THIS_MONTH = 'this_month';

    /**
     * 'Last month' date range
     */
    const LAST_MONTH = 'last_month';

    /**
     * 'Custom' date range
     */
    const CUSTOM = 'custom';

    /**
     * @var array
     */
    private $options;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LocaleLists
     */
    private $localeList;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param LocaleLists $localeList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LocaleLists $localeList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->localeList = $localeList;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $firstDayOfWeek = $this->scopeConfig->getValue('general/locale/firstday');
            $lastDayOfWeek = $firstDayOfWeek + 6 > 6
                ? 0
                : $firstDayOfWeek + 6;
            $businessWeekdays = $this->getBusinessWeekdays();

            $this->options = [
                [
                    'value' => self::TODAY,
                    'label' => __('Today')
                ],
                [
                    'value' => self::YESTERDAY,
                    'label' => __('Yesterday')
                ],
                [
                    'value' => self::LAST_7_DAYS,
                    'label' => __('Last 7 Days')
                ],
                [
                    'value' => self::LAST_WEEK,
                    'label' => __('Last Week')
                        . ' (' . $this->getWeekdaysRangeLabel($firstDayOfWeek, $lastDayOfWeek) . ')'
                ],
                [
                    'value' => self::LAST_BUSINESS_WEEK,
                    'label' => __('Last Business Week')
                        . ' (' . $this->getWeekdaysRangeLabel(reset($businessWeekdays), end($businessWeekdays)) . ')'
                ],
                [
                    'value' => self::THIS_MONTH,
                    'label' => __('This Month')
                ],
                [
                    'value' => self::LAST_MONTH,
                    'label' => __('Last Month')
                ],
                [
                    'value' => self::CUSTOM,
                    'label' => __('Custom Date Range')
                ]
            ];
        }
        return $this->options;
    }

    /**
     * Get weekdays keyed by index
     *
     * @return array
     */
    private function getWeekdaysKeyedByIndex()
    {
        $weekdaysKeyedByIndex = [];
        foreach ($this->localeList->getOptionWeekdays() as $weekday) {
            $weekdaysKeyedByIndex[$weekday['value']] = $weekday['label'];
        }
        return $weekdaysKeyedByIndex;
    }

    /**
     * Get business weekdays
     *
     * @return array
     */
    private function getBusinessWeekdays()
    {
        $weekdays = array_keys($this->getWeekdaysKeyedByIndex());
        $weekendDays = explode(',', $this->scopeConfig->getValue('general/locale/weekend'));
        return array_diff($weekdays, $weekendDays);
    }

    /**
     * Get weekdays range label
     *
     * @param int $first
     * @param int $last
     * @return string
     */
    private function getWeekdaysRangeLabel($first, $last)
    {
        $weekdays = $this->getWeekdaysKeyedByIndex();
        return substr($weekdays[$first], 0, 3) . ' - ' . substr($weekdays[$last], 0, 3);
    }
}
