<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Config\Source\DeliveryDate;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class DayOfMonth
 *
 * @package Aheadworks\OneStepCheckout\Model\Config\Source\DeliveryDate
 */
class DayOfMonth implements OptionSourceInterface
{
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
            $this->options = [];
            for ($day = 1; $day < 32; $day++) {
                $this->options[] = ['value' => $day, 'label' => $day];
            }
        }
        return $this->options;
    }
}
