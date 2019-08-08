<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Adminhtml\System\Config\DeliveryDate\Renderer;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Model\Config\Source\Locale\Weekdays as WeekdaysSource;
use Aheadworks\OneStepCheckout\Model\Config\Source\DeliveryDate\DayOfMonth as DayOfMonthSource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Html\Select;

/**
 * Class Period
 * @package Aheadworks\OneStepCheckout\Block\Adminhtml\System\Config\DeliveryDate\Renderer
 */
class Period extends Template
{
    /**
     * @var WeekdaysSource
     */
    private $weekdaysSource;

    /**
     * @var DayOfMonthSource
     */
    private $dayOfMonthSource;

    /**
     * @var Select
     */
    private $weekdayRenderer;

    /**
     * @var Select
     */
    private $dayOfMonthRenderer;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'system/config/delivery_date/period.phtml';

    /**
     * @param Context $context
     * @param WeekdaysSource $weekdaysSource
     * @param DayOfMonthSource $dayOfMonthSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        WeekdaysSource $weekdaysSource,
        DayOfMonthSource $dayOfMonthSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->weekdaysSource = $weekdaysSource;
        $this->dayOfMonthSource = $dayOfMonthSource;
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

    /**
     * Get weekday select renderer
     *
     * @return Select
     * @throws LocalizedException
     */
    public function getWeekdayRenderer()
    {
        if (!$this->weekdayRenderer) {
            /** @var Select $renderer */
            $this->weekdayRenderer = $this->getLayout()->createBlock(
                Select::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->weekdayRenderer->setOptions($this->weekdaysSource->toOptionArray())
                ->setName($this->getName() . '[weekday]')
                ->setId($this->getId() . '_weekday');
        }
        return $this->weekdayRenderer;
    }

    /**
     * Get day of month select renderer
     *
     * @return Select
     * @throws LocalizedException
     */
    public function getDayOfMonthRenderer()
    {
        if (!$this->dayOfMonthRenderer) {
            /** @var Select $renderer */
            $this->dayOfMonthRenderer = $this->getLayout()->createBlock(
                Select::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->dayOfMonthRenderer->setOptions($this->dayOfMonthSource->toOptionArray())
                ->setName($this->getName() . '[day_of_month]')
                ->setId($this->getId() . '_day_of_month');
        }
        return $this->dayOfMonthRenderer;
    }

    /**
     * Get weekdays select html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getWeekdaysSelectHtml()
    {
        return $this->getWeekdayRenderer()->toHtml();
    }

    /**
     * Get day of month select html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getDayOfMonthSelectHtml()
    {
        return $this->getDayOfMonthRenderer()->toHtml();
    }
}
