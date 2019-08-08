<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Adminhtml\Report\View;

use Aheadworks\OneStepCheckout\Model\Report\Source\CustomerGroup;
use Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\CustomerGroupId as Filter;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

/**
 * Class CustomerGroupSwitcher
 * @package Aheadworks\OneStepCheckout\Block\Adminhtml\Report\View
 */
class CustomerGroupSwitcher extends Template
{
    /**
     * @inheritdoc
     */
    protected $_template = 'Aheadworks_OneStepCheckout::report/view/customer_group_switcher.phtml';

    /**
     * @var CustomerGroup
     */
    private $groupSource;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @param Context $context
     * @param CustomerGroup $groupSource
     * @param Filter $filter
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerGroup $groupSource,
        Filter $filter,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->groupSource = $groupSource;
        $this->filter = $filter;
    }

    /**
     * Get current customer group Id
     *
     * @return int
     */
    public function getGroupId()
    {
        return $this->filter->getCustomerGroupId();
    }

    /**
     * Get current customer group title
     *
     * @return string
     */
    public function getGroupTitle()
    {
        foreach ($this->getOptions() as $option) {
            if ($option['value'] == $this->getGroupId()) {
                return $option['label'];
            }
        }
        return '';
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->groupSource->toOptionArray();
    }

    /**
     * Get filter by customer group url
     *
     * @param int $customerGroupId
     * @return string
     */
    public function getFilterUrl($customerGroupId)
    {
        return $this->getUrl(
            '*/*/*',
            [
                '_query' => [Filter::REQUEST_FIELD_NAME => $customerGroupId],
                '_current' => true
            ]
        );
    }
}
