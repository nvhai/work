<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Adminhtml\System\Config\DeliveryDate;

use Aheadworks\OneStepCheckout\Block\Adminhtml\System\Config\DeliveryDate\Renderer\Period;
use Aheadworks\OneStepCheckout\Block\Adminhtml\System\Config\DeliveryDate\Renderer\PeriodType;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

/**
 * Class NonDeliveryPeriod
 * @package Aheadworks\OneStepCheckout\Block\Adminhtml\System\Config\DeliveryDate
 */
class NonDeliveryPeriod extends AbstractFieldArray
{
    /**
     * @var PeriodType
     */
    private $periodTypeRenderer;

    /**
     * @var Period
     */
    private $periodRenderer;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'system/config/non_delivery_period/field_array.phtml';

    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'period_type',
            [
                'label' => __('Period Type'),
                'renderer' => $this->getPeriodTypeRenderer()
            ]
        );
        $this->addColumn(
            'period',
            [
                'label' => __('Period/Date'),
                'renderer' => $this->getPeriodRenderer()
            ]
        );
        $this->_addAfter = false;
    }

    /**
     * Get period type renderer
     *
     * @return PeriodType
     */
    private function getPeriodTypeRenderer()
    {
        if (!$this->periodTypeRenderer) {
            $this->periodTypeRenderer = $this->getLayout()->createBlock(
                PeriodType::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->periodTypeRenderer;
    }

    /**
     * Get period renderer
     *
     * @return Period
     */
    private function getPeriodRenderer()
    {
        if (!$this->periodRenderer) {
            $this->periodRenderer = $this->getLayout()->createBlock(
                Period::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->periodRenderer;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $periodTypeRenderer = $this->getPeriodTypeRenderer();
        $weekdayRenderer = $this->getPeriodRenderer()->getWeekdayRenderer();
        $dayOfMonthRenderer = $this->getPeriodRenderer()->getDayOfMonthRenderer();
        $optionExtraAttributes = [
            'option_' . $periodTypeRenderer->calcOptionHash($row->getPeriodType()) => 'selected="selected"',
            'option_' . $weekdayRenderer->calcOptionHash($row->getPeriod('weekday')) => 'selected="selected"',
            'option_' . $dayOfMonthRenderer->calcOptionHash($row->getPeriod('day_of_month')) => 'selected="selected"'
        ];
        $row->setData('option_extra_attrs', $optionExtraAttributes);
    }

    /**
     * Get default row data
     *
     * @return array
     */
    public function getDefaultRowData()
    {
        $renderer = $this->getPeriodTypeRenderer();
        $result = [
            'option_extra_attrs' => [
                'option_' . $renderer->calcOptionHash('single_day') => '',
                'option_' . $renderer->calcOptionHash('recurrent_day_of_week') => '',
                'option_' . $renderer->calcOptionHash('recurrent_day_of_month') => '',
                'option_' . $renderer->calcOptionHash('from_to') => ''
            ]
        ];
        $columns = $this->getColumns();
        foreach (array_keys($columns) as $columnName) {
            $result[$columnName] = '';
        }
        return $result;
    }

    /**
     * Get rows
     *
     * @return array
     */
    public function getRows()
    {
        $rows = [];
        foreach ($this->getArrayRows() as $row) {
            $rows[] = $row->getData();
        }
        return $rows;
    }
}
