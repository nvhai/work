<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Adminhtml\System\Config\DeliveryDate\Renderer;

use Aheadworks\OneStepCheckout\Model\Config\Source\DeliveryDate\Time as TimeSource;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * Class Time
 * @package Aheadworks\OneStepCheckout\Block\Adminhtml\System\Config\DeliveryDate\Renderer
 */
class Time extends Select
{
    /**
     * @var TimeSource
     */
    private $timeSource;

    /**
     * @param Context $context
     * @param TimeSource $timeSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        TimeSource $timeSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->timeSource = $timeSource;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->timeSource->toOptionArray());
        }
        $this->setClass('time-slot-select');
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
