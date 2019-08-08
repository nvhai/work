<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\DateRange;

use Aheadworks\OneStepCheckout\Model\Report\Source\DateRange as DateRangeSource;
use Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\DateRange as Filter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ListsInterface as LocaleLists;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Resolver
 * @package Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\DateRange
 */
class Resolver
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var LocaleLists
     */
    private $localeList;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param TimezoneInterface $localeDate
     * @param LocaleLists $localeList
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        TimezoneInterface $localeDate,
        LocaleLists $localeList,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->localeDate = $localeDate;
        $this->localeList = $localeList;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Resolve date range dates
     *
     * @param string $range
     * @return \DateTime[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function resolve($range)
    {
        $timezone = $this->localeDate->getConfigTimezone(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        switch ($range) {
            case DateRangeSource::TODAY:
                return [
                    'from' => new \DateTime('now', new \DateTimeZone($timezone)),
                    'to' => new \DateTime('now', new \DateTimeZone($timezone))
                ];

            case DateRangeSource::YESTERDAY:
                return [
                    'from' => new \DateTime('yesterday', new \DateTimeZone($timezone)),
                    'to' => new \DateTime('yesterday', new \DateTimeZone($timezone))
                ];

            case DateRangeSource::LAST_7_DAYS:
                return [
                    'from' => new \DateTime('6 days ago', new \DateTimeZone($timezone)),
                    'to' => new \DateTime('now', new \DateTimeZone($timezone))
                ];

            case DateRangeSource::LAST_WEEK:
                $firstDayOfWeek = $this->scopeConfig->getValue('general/locale/firstday');
                $lastDayOfWeek = $firstDayOfWeek + 6 > 6
                    ? 0
                    : $firstDayOfWeek + 6;
                $from = new \DateTime(
                    'previous ' . $this->getWeekdayKeyByNum($firstDayOfWeek),
                    new \DateTimeZone($timezone)
                );
                $to = new \DateTime(
                    'previous ' . $this->getWeekdayKeyByNum($lastDayOfWeek),
                    new \DateTimeZone($timezone)
                );
                if ($to->getTimestamp() < $from->getTimestamp()) {
                    $from->modify('-7 days');
                }
                return ['from' => $from, 'to' => $to];

            case DateRangeSource::LAST_BUSINESS_WEEK:
                $businessWeekdays = $this->getBusinessWeekdays();
                $firstBusinessDayOfWeek = reset($businessWeekdays);
                $lastBusinessDyaOfWeek = end($businessWeekdays);
                $from = new \DateTime(
                    'previous ' . $this->getWeekdayKeyByNum($firstBusinessDayOfWeek),
                    new \DateTimeZone($timezone)
                );
                $to = new \DateTime(
                    'previous ' . $this->getWeekdayKeyByNum($lastBusinessDyaOfWeek),
                    new \DateTimeZone($timezone)
                );
                if ($to->getTimestamp() < $from->getTimestamp()) {
                    $from->modify('-7 days');
                }
                return ['from' => $from, 'to' => $to];

            case DateRangeSource::THIS_MONTH:
                return [
                    'from' => new \DateTime('first day of this month', new \DateTimeZone($timezone)),
                    'to' => new \DateTime('now', new \DateTimeZone($timezone))
                ];

            case DateRangeSource::LAST_MONTH:
                return [
                    'from' => new \DateTime('first day of last month', new \DateTimeZone($timezone)),
                    'to' => new \DateTime('last day of last month', new \DateTimeZone($timezone))
                ];
        }
        return [];
    }

    /**
     * Get weekday key by number
     *
     * @param int $index
     * @return string|null
     */
    private function getWeekdayKeyByNum($index)
    {
        $days = [
            0 => 'sun',
            1 => 'mon',
            2 => 'tue',
            3 => 'wed',
            4 => 'thu',
            5 => 'fri',
            6 => 'sat',
        ];
        return isset($days[$index]) ? $days[$index] : null;
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
}
