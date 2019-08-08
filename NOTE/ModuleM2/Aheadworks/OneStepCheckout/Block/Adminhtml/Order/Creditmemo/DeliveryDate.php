<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Adminhtml\Order\Creditmemo;

use Aheadworks\OneStepCheckout\Block\Adminhtml\Order\AbstractDeliveryDate;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Model\Order\Creditmemo;

/**
 * Class DeliveryDate
 * @package Aheadworks\OneStepCheckout\Block\Adminhtml\Order\Creditmemo
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
        /** @var Creditmemo $creditmemo */
        $creditmemo = $this->coreRegistry->registry('current_creditmemo');
        return $creditmemo->getOrder();
    }
}
