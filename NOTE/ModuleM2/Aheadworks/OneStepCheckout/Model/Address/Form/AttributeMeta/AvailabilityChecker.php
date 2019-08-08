<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Framework\Module\Manager as ModuleManager;

/**
 * Class AvailabilityChecker
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta
 */
class AvailabilityChecker
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @param ModuleManager $moduleManager
     */
    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Check if attribute is available on checkout address form
     *
     * @param AttributeMetadataInterface $attributeMeta
     * @return bool
     */
    public function isAvailableOnForm(AttributeMetadataInterface $attributeMeta)
    {
        if ($this->moduleManager->isEnabled('Magento_CustomerCustomAttributes')) {
            return true;
        }
        return !$attributeMeta->isUserDefined();
    }
}
