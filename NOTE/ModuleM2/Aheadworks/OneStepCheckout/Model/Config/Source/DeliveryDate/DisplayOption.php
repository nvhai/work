<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Config\Source\DeliveryDate;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class DisplayOption
 * @package Aheadworks\OneStepCheckout\Model\Config\Source\DeliveryDate
 */
class DisplayOption implements OptionSourceInterface
{
    /**
     * 'No' option
     */
    const NO = 0;

    /**
     * 'Date only' option
     */
    const DATE = 1;

    /**
     * 'Date and time' option
     */
    const DATE_AND_TIME = 2;

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
                    'value' => self::NO,
                    'label' => __('No')
                ],
                [
                    'value' => self::DATE,
                    'label' => __('Date Only')
                ],
                [
                    'value' => self::DATE_AND_TIME,
                    'label' => __('Date and Time')
                ]
            ];
        }
        return $this->options;
    }
}
