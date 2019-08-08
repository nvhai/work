<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Order;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;

/**
 * Class Note
 * @package Aheadworks\OneStepCheckout\Block\Order
 */
class Note extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'order/note.phtml';

    /**
     * {@inheritdoc}
     */
    protected $_isScopePrivate = true;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->getOrderNote()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Get order note
     *
     * @return string
     */
    public function getOrderNote()
    {
        /** @var Order $order */
        $order = $this->coreRegistry->registry('current_order');
        return $order->getAwOrderNote();
    }
}
