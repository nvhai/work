<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\DeliveryDate;

use Aheadworks\OneStepCheckout\Model\Config;
use Aheadworks\OneStepCheckout\Model\Config\Source\DeliveryDate\DisplayOption;

/**
 * Class ConfigProvider
 * @package Aheadworks\OneStepCheckout\Model\DeliveryDate
 */
class ConfigProvider
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get delivery date options config
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'isEnabled' => $this->config->getDeliveryDateDisplayOption() != DisplayOption::NO,
            'dateRestrictions' => [
                'weekdays' => $this->config->getDeliveryDateAvailableWeekdays(),
                'nonDeliveryPeriods' => $this->config->getNonDeliveryPeriods(), // todo: to current timezone
                'minOrderDeliveryPeriod' => $this->config->getMinOrderDeliveryPeriod()
            ]
        ];
    }
}
