<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Ui\DataProvider;

use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\AbstractCollection as ReportCollection;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;

/**
 * Class Reporting
 * @package Aheadworks\OneStepCheckout\Ui\DataProvider
 */
class Reporting implements ReportingInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var FilterPool
     */
    private $filterPool;

    /**
     * @var DefaultFilterPool
     */
    private $defaultFilterPool;

    /**
     * @var Aggregation
     */
    private $aggregation;

    /**
     * @param CollectionFactory $collectionFactory
     * @param FilterPool $filterPool
     * @param DefaultFilterPool $defaultFilterPool
     * @param Aggregation $aggregation
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        FilterPool $filterPool,
        DefaultFilterPool $defaultFilterPool,
        Aggregation $aggregation
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filterPool = $filterPool;
        $this->defaultFilterPool = $defaultFilterPool;
        $this->aggregation = $aggregation;
    }

    /**
     * {@inheritdoc}
     */
    public function search(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ReportCollection $collection */
        $collection = $this->collectionFactory->getReport(
            $searchCriteria->getRequestName(),
            $this->aggregation->getAggregation()
        );
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $this->defaultFilterPool->applyFilters($collection);
        $this->filterPool->applyFilters($collection, $searchCriteria);
        foreach ($searchCriteria->getSortOrders() as $sortOrder) {
            if ($sortOrder->getField()) {
                $collection->setOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }
        return $collection;
    }
}
