<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\Attribute;

use Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\ModifierInterface;
use Aheadworks\OneStepCheckout\Model\Config;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Region as RegionSource;

/**
 * Class RegionId
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\Attribute
 */
class RegionId implements ModifierInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var RegionSource
     */
    private $regionSource;

    /**
     * @param Config $config
     * @param RegionSource $regionSource
     */
    public function __construct(
        Config $config,
        RegionSource $regionSource
    ) {
        $this->config = $config;
        $this->regionSource = $regionSource;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($metadata, $addressType)
    {
        $defaultRegionId = $this->config->getDefaultRegionId();
        if ($defaultRegionId && is_numeric($defaultRegionId)) {
            $metadata['default'] = $defaultRegionId;
        }
        $metadata['options'] = $this->regionSource->getAllOptions();
        return $metadata;
    }
}
