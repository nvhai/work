<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Adminhtml\Report\View;

use Aheadworks\OneStepCheckout\Model\Report\Source\DateRange as DateRangeSource;
use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\AbandonedCheckout as AbandonedCheckoutResource;
use Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\DateRange as Filter;
use Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\DateRange\Resolver as DateRangeResolver;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

/**
 * Class Period
 * @package Aheadworks\OneStepCheckout\Block\Adminhtml\Report\View
 */
class Period extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_OneStepCheckout::report/view/period.phtml';

    /**
     * @var DateRangeSource
     */
    private $dateRangeSource;

    /**
     * @var DateRangeResolver
     */
    private $dateRangeResolver;

    /**
     * @var AbandonedCheckoutResource
     */
    private $resource;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @param Context $context
     * @param DateRangeSource $dateRangeSource
     * @param DateRangeResolver $dateRangeResolver
     * @param AbandonedCheckoutResource $resource
     * @param Filter $filter
     * @param array $data
     */
    public function __construct(
        Context $context,
        DateRangeSource $dateRangeSource,
        DateRangeResolver $dateRangeResolver,
        AbandonedCheckoutResource $resource,
        Filter $filter,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dateRangeSource = $dateRangeSource;
        $this->dateRangeResolver = $dateRangeResolver;
        $this->resource = $resource;
        $this->filter = $filter;
    }

    /**
     * Get current date range
     *
     * @return array
     */
    public function getDateRange()
    {
        return $this->filter->getValue();
    }

    /**
     * Get date range options
     *
     * @return array
     */
    public function getDateRangeOptions()
    {
        return $this->dateRangeSource->toOptionArray();
    }

    /**
     * Retrieve first calendar date
     *
     * @return string
     */
    public function getEarliestCalendarDateAsString()
    {
        $minDate = $this->resource->fetchMinDate();
        return $this->_localeDate->date($minDate)->format('Y-m-d');
    }

    /**
     * Retrieve latest calendar date
     *
     * @return string
     */
    public function getLatestCalendarDateAsString()
    {
        return $this->_localeDate->date()->format('Y-m-d');
    }

    /**
     * Get first day of week
     *
     * @return int
     */
    public function getFirstDayOfWeek()
    {
        return $this->_scopeConfig->getValue('general/locale/firstday');
    }

    /**
     * Get ranges to dates map
     *
     * @return array
     */
    public function getRangeToDatesMap()
    {
        $map = [];
        foreach ($this->dateRangeSource->toOptionArray() as $option) {
            $range = $option['value'];
            $dates = $this->dateRangeResolver->resolve($range);
            if (!empty($dates)) {
                $map[$range] = [
                    'from' => $dates['from']->format('M d, Y'),
                    'to' => $dates['to']->format('M d, Y')
                ];
            }
        }
        return $map;
    }
}
