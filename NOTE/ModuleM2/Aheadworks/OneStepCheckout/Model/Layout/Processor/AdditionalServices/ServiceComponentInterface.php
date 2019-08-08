<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Layout\Processor\AdditionalServices;

/**
 * Interface ServiceComponentInterface
 * @package Aheadworks\OneStepCheckout\Model\Layout\Processor\AdditionalServices
 */
interface ServiceComponentInterface
{
    /**
     * Check if service component enabled
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Add config to js layout
     *
     * @param array $jsLayout
     * @return void
     */
    public function configure(&$jsLayout);
}
