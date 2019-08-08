<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model;

use IWD\BluePaySubs\Api\Data;
use IWD\BluePaySubs\Api\SubscriptionRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class SubscriptionRepository
 */
class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * @var \IWD\BluePaySubs\Model\ResourceModel\Subscription
     */
    protected $resource;

    /**
     * @var \IWD\BluePaySubs\Model\SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var \IWD\BluePaySubs\Model\ResourceModel\Subscription\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Data\SubscriptionSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \IWD\BluePaySubs\Api\Data\SubscriptionInterfaceFactory
     */
    protected $dataSubscriptionFactory;

    /**
     * @param \IWD\BluePaySubs\Model\ResourceModel\Subscription $resource
     * @param \IWD\BluePaySubs\Model\SubscriptionFactory $subscriptionFactory
     * @param Data\SubscriptionInterfaceFactory $dataSubscriptionFactory
     * @param \IWD\BluePaySubs\Model\ResourceModel\Subscription\CollectionFactory $collectionFactory
     * @param Data\SubscriptionSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        \IWD\BluePaySubs\Model\ResourceModel\Subscription $resource,
        \IWD\BluePaySubs\Model\SubscriptionFactory $subscriptionFactory,
        \IWD\BluePaySubs\Api\Data\SubscriptionInterfaceFactory $dataSubscriptionFactory,
        \IWD\BluePaySubs\Model\ResourceModel\Subscription\CollectionFactory $collectionFactory,
        Data\SubscriptionSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resource = $resource;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataSubscriptionFactory = $dataSubscriptionFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Save Subscription data
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @return \IWD\BluePaySubs\Api\Data\SubscriptionInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\SubscriptionInterface $subscription)
    {
        try {
            $this->resource->save($subscription);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $subscription;
    }

    /**
     * Load Subscription data by given ID
     *
     * @param string $subscriptionId
     * @return \IWD\BluePaySubs\Api\Data\SubscriptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($subscriptionId)
    {
        $subscription = $this->subscriptionFactory->create();

        $this->resource->load($subscription, $subscriptionId);

        if (!$subscription->getId()) {
            throw new NoSuchEntityException(__('Subscription with id "%1" does not exist.', $subscriptionId));
        }

        return $subscription;
    }

    /**
     * Load Subscription data by given Subscription Identity
     *
     * @param string $subscriptionId
     * @return \IWD\BluePaySubs\Api\Data\SubscriptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function load($subscriptionId)
    {
        return $this->getById($subscriptionId);
    }

    /**
     * Load Subscription data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param SearchCriteriaInterface $criteria
     * @return \IWD\BluePaySubs\Model\ResourceModel\Subscription\Collection
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        /** @var \IWD\BluePaySubs\Model\ResourceModel\Subscription\Collection $collection */
        $collection = $this->collectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($criteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }

        // Add sort order(s)
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }

        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());

        $collection->load();

        /** @var \IWD\BluePaySubs\Api\Data\SubscriptionSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    /**
     * Delete Subscription
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\SubscriptionInterface $subscription)
    {
        try {
            $this->resource->delete($subscription);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    /**
     * Delete Subscription by given Subscription Identity
     *
     * @param string $subscriptionId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($subscriptionId)
    {
        return $this->delete($this->getById($subscriptionId));
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \IWD\BluePaySubs\Model\ResourceModel\Subscription\Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \IWD\BluePaySubs\Model\ResourceModel\Subscription\Collection $collection
    ) {
        $fields = [];
        $conds  = [];

        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[]  = $filter->getField();
            $conds[]   = [$condition => $filter->getValue()];
        }

        if ($fields) {
            $collection->addFieldToFilter($fields, $conds);
        }
    }
}
