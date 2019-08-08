<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\CustomerGroupId;

use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\FilterableInterface;
use Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\CustomerGroupId as Filter;
use Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilterApplierInterface;
use Magento\Customer\Model\GroupManagement;

/**
 * Class Applier
 * @package Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\CustomerGroupId
 */
class Applier implements DefaultFilterApplierInterface
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @param Filter $filter
     */
    public function __construct(Filter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterableInterface $collection)
    {
        $customerGroupId = $this->filter->getCustomerGroupId();
        if ($customerGroupId != GroupManagement::CUST_GROUP_ALL) {
            $collection->addCustomerGroupIdFilter($customerGroupId);
        }
    }
}
