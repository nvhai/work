<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Adminhtml\System\Config\DeliveryDate\Renderer;

use Aheadworks\OneStepCheckout\Model\Config\Source\DeliveryDate\PeriodType as PeriodTypeSource;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * Class PeriodType
 * @package Aheadworks\OneStepCheckout\Block\Adminhtml\System\Config\DeliveryDate\Renderer
 */
class PeriodType extends Select
{
    /**
     * @var PeriodTypeSource
     */
    private $periodTypeSource;

    /**
     * @param Context $context
     * @param PeriodTypeSource $periodTypeSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        PeriodTypeSource $periodTypeSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->periodTypeSource = $periodTypeSource;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->periodTypeSource->toOptionArray());
        }
        $this->setClass('period-type-select')
            ->setExtraParams('data-role="linked-select"');
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

    /**
     * Set html id for input element
     *
     * @param string $id
     * @return $this
     */
    public function setInputId($id)
    {
        return $this->setId($id);
    }
}
