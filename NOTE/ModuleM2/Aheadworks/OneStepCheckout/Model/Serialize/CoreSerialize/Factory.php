<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Serialize\CoreSerialize;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 * @package Aheadworks\OneStepCheckout\Model\Serialize\CoreSerialize
 */
class Factory
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
     * Create serializer instance
     *
     * @return \Magento\Framework\Serialize\SerializerInterface
     */
    public function create()
    {
        return $this->objectManager->create(\Magento\Framework\Serialize\SerializerInterface::class);
    }
}
