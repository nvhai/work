<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model;

use IWD\BluePaySubs\Api\Data;
use IWD\BluePaySubs\Api\LogRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class LogRepository
 */
class LogRepository implements LogRepositoryInterface
{
    /**
     * @var \IWD\BluePaySubs\Model\ResourceModel\Log
     */
    protected $resource;

    /**
     * @var \IWD\BluePaySubs\Model\LogFactory
     */
    protected $logFactory;

    /**
     * @var \IWD\BluePaySubs\Model\ResourceModel\Log\CollectionFactory
     */
    protected $logCollectionFactory;

    /**
     * @var Data\LogSearchResultsInterfaceFactory
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
     * @var \IWD\BluePaySubs\Api\Data\LogInterfaceFactory
     */
    protected $dataLogFactory;

    /**
     * @param \IWD\BluePaySubs\Model\ResourceModel\Log $resource
     * @param \IWD\BluePaySubs\Model\LogFactory $logFactory
     * @param Data\LogInterfaceFactory $dataLogFactory
     * @param \IWD\BluePaySubs\Model\ResourceModel\Log\CollectionFactory $logCollectionFactory
     * @param Data\LogSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        \IWD\BluePaySubs\Model\ResourceModel\Log $resource,
        \IWD\BluePaySubs\Model\LogFactory $logFactory,
        \IWD\BluePaySubs\Api\Data\LogInterfaceFactory $dataLogFactory,
        \IWD\BluePaySubs\Model\ResourceModel\Log\CollectionFactory $logCollectionFactory,
        Data\LogSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resource = $resource;
        $this->logFactory = $logFactory;
        $this->logCollectionFactory = $logCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataLogFactory = $dataLogFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Save log data
     *
     * @param \IWD\BluePaySubs\Api\Data\LogInterface $log
     * @return \IWD\BluePaySubs\Api\Data\LogInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\LogInterface $log)
    {
        try {
            $this->resource->save($log);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $log;
    }

    /**
     * Load log data by given ID
     *
     * @param string $logId
     * @return \IWD\BluePaySubs\Api\Data\LogInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($logId)
    {
        $log = $this->logFactory->create();

        $this->resource->load($log, $logId);

        if (!$log->getId()) {
            throw new NoSuchEntityException(__('Subscription with id "%1" does not exist.', $logId));
        }

        return $log;
    }

    /**
     * Load log data by given ID
     *
     * @param string $logId
     * @return null|\IWD\BluePaySubs\Api\Data\LogInterface
     */
    public function getByTransactionId($transId)
    {
        $log = $this->logFactory->create();

        $this->resource->load($log, $transId, Data\LogInterface::TRANSACTION_ID);

        if (!$log->getId()) {
            return null;
        }

        return $log;
    }

    /**
     * Load log data by given log ID
     *
     * @param string $logId
     * @return \IWD\BluePaySubs\Api\Data\LogInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function load($logId)
    {
        return $this->getById($logId);
    }

    /**
     * Load log data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param SearchCriteriaInterface $criteria
     * @return \IWD\BluePaySubs\Model\ResourceModel\Log\Collection
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        /** @var \IWD\BluePaySubs\Model\ResourceModel\Log\Collection $collection */
        $collection = $this->logCollectionFactory->create();

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

        /** @var \IWD\BluePaySubs\Api\Data\LogSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    /**
     * Delete log
     *
     * @param \IWD\BluePaySubs\Api\Data\LogInterface $log
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\LogInterface $log)
    {
        try {
            $this->resource->delete($log);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    /**
     * Delete log by given log ID
     *
     * @param string $logId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($logId)
    {
        return $this->delete($this->getById($logId));
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \IWD\BluePaySubs\Model\ResourceModel\Log\Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \IWD\BluePaySubs\Model\ResourceModel\Log\Collection $collection
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
