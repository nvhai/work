<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Report\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Aggregation
 * @package Aheadworks\OneStepCheckout\Model\Report\Source
 */
class Aggregation implements OptionSourceInterface
{
    /**
     * 'Day' aggregation type
     */
    const DAY = 'day';

    /**
     * 'Week' aggregation type
     */
    const WEEK = 'week';

    /**
     * 'Month' aggregation type
     */
    const MONTH = 'month';

    /**
     * 'Quarter' aggregation type
     */
    const QUARTER = 'quarter';

    /**
     * 'Year' aggregation type
     */
    const YEAR = 'year';

    /**
     * @var array
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                [
                    'value' => self::DAY,
                    'label' => __('Day')
                ],
                [
                    'value' => self::WEEK,
                    'label' => __('Week')
                ],
                [
                    'value' => self::MONTH,
                    'label' => __('Month')
                ],
                [
                    'value' => self::QUARTER,
                    'label' => __('Quarter')
                ],
                [
                    'value' => self::YEAR,
                    'label' => __('Year')
                ]
            ];
        }
        return $this->options;
    }
}
