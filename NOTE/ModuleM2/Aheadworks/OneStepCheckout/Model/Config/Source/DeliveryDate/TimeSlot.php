<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Config\Source\DeliveryDate;

use Aheadworks\OneStepCheckout\Model\Config;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class TimeSlot
 * @package Aheadworks\OneStepCheckout\Model\Config\Source\DeliveryDate
 */
class TimeSlot implements OptionSourceInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var DateTimeFormatterInterface
     */
    private $dateTimeFormatter;

    /**
     * @var array
     */
    private $options;

    /**
     * @param Config $config
     * @param TimezoneInterface $localeDate
     * @param DateTimeFormatterInterface $dateTimeFormatter
     */
    public function __construct(
        Config $config,
        TimezoneInterface $localeDate,
        DateTimeFormatterInterface $dateTimeFormatter
    ) {
        $this->config = $config;
        $this->localeDate = $localeDate;
        $this->dateTimeFormatter = $dateTimeFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [['value' => '', 'label' => ' ']];
            foreach ($this->config->getDeliveryDateTimeSlots() as $timeSlot) {
                $startTime = $timeSlot['start_time'];
                $endTime = $timeSlot['end_time'];
                $fromLabel = $this->dateTimeFormatter->formatObject(
                    (new \DateTime(null))->setTimestamp($startTime),
                    $this->localeDate->getTimeFormat(\IntlDateFormatter::SHORT)
                );
                $toLabel = $this->dateTimeFormatter->formatObject(
                    (new \DateTime(null))->setTimestamp($endTime),
                    $this->localeDate->getTimeFormat(\IntlDateFormatter::SHORT)
                );
                $this->options[] = [
                    'value' => $startTime . '-' . $endTime,
                    'label' => $fromLabel . ' - ' . $toLabel
                ];
            }
        }
        return $this->options;
    }
}
