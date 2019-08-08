<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Product;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ConfigurationFactory
 * @package Aheadworks\Sarp\Model\SubscriptionEngine
 */
class ConfigurationFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create configuration instance
     *
     * @param string $className
     * @return ConfigurationInterface
     */
    public function create($className)
    {
        return $this->objectManager->create($className);
    }
}
