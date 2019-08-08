<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Config\Source\DeliveryDate;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PeriodType
 * @package Aheadworks\OneStepCheckout\Model\Config\Source\DeliveryDate
 */
class PeriodType implements OptionSourceInterface
{
    /**
     * 'Single day' period type
     */
    const SINGLE_DAY = 'single_day';

    /**
     * 'Recurrent day of week' period type
     */
    const RECURRENT_DAY_OF_WEEK = 'recurrent_day_of_week';

    /**
     * 'Recurrent Day of Month' period type
     */
    const RECURRENT_DAY_OF_MONTH = 'recurrent_day_of_month';

    /**
     * 'From to' period type
     */
    const FROM_TO = 'from_to';

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
                    'value' => self::SINGLE_DAY,
                    'label' => __('Single Day')
                ],
                [
                    'value' => self::RECURRENT_DAY_OF_WEEK,
                    'label' => __('Recurrent Day of Week')
                ],
                [
                    'value' => self::RECURRENT_DAY_OF_MONTH,
                    'label' => __('Recurrent Day of Month')
                ],
                [
                    'value' => self::FROM_TO,
                    'label' => __('Period, From-To')
                ]
            ];
        }
        return $this->options;
    }
}
