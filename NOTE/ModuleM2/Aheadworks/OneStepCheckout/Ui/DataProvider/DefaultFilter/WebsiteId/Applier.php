<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\WebsiteId;

use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\FilterableInterface;
use Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\WebsiteId as Filter;
use Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilterApplierInterface;

/**
 * Class Applier
 * @package Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\WebsiteId
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
        $filterValue = $this->filter->getValue();
        if ($filterValue != 0) {
            $collection->addWebsiteIdFilter($filterValue);
        }
    }
}
