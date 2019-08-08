<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Adminhtml\Report\CheckoutBehavior\AddressAttributes;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class MetaProviderFactory
 * @package Aheadworks\OneStepCheckout\Block\Adminhtml\Report\CheckoutBehavior\AddressAttributes
 */
class MetaProviderFactory
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
     * Create meta provider instance
     *
     * @param string $className
     * @return MetaProvider
     */
    public function create($className)
    {
        return $this->objectManager->create($className);
    }
}
