<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Layout\Processor\AdditionalServices;

use Aheadworks\OneStepCheckout\Model\Layout\Processor\AdditionalServices\GoogleAutocomplete\RegionMap;
use Aheadworks\OneStepCheckout\Model\Config;

/**
 * Interface GoogleAutocomplete
 * @package Aheadworks\OneStepCheckout\Model\Layout\Processor\AdditionalServices
 */
class GoogleAutocomplete implements ServiceComponentInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var RegionMap
     */
    private $regionMap;

    /**
     * @param Config $config
     * @param RegionMap $regionMap
     */
    public function __construct(
        Config $config,
        RegionMap $regionMap
    ) {
        $this->config = $config;
        $this->regionMap = $regionMap;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->config->isGoogleAutoCompleteEnabled();
    }

    /**
     * {@inheritdoc}
     */
    public function configure(&$jsLayout)
    {
        if (!isset($jsLayout['config'])) {
            $jsLayout['config'] = [];
        }
        $jsLayout['config']['regionMap'] = $this->regionMap->getMap();
    }
}
