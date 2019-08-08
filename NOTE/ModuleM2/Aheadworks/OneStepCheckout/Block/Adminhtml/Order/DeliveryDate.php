<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Adminhtml\Order;

use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;

/**
 * Class DeliveryDate
 * @package Aheadworks\OneStepCheckout\Block\Adminhtml\Order
 */
class DeliveryDate extends AbstractDeliveryDate
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * {@inheritdoc}
     */
    protected function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }
}
