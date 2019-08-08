<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Layout\Processor\PaymentOptions;

use Aheadworks\OneStepCheckout\Model\Config;

/**
 * Class Filter
 * @package Aheadworks\OneStepCheckout\Model\Layout\Processor\PaymentOptions
 */
class Filter
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
     * Filter payment options definitions
     *
     * @param array $config
     * @return array
     */
    public function filter(array $config)
    {
        if (isset($config['discount']) && !$this->config->isApplyDiscountCodeEnabled()) {
            unset($config['discount']);
        }
        return $config;
    }
}
