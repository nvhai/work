<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Api;

/**
 * LogRepositoryInterface
 *
 * @api
 */
interface LogRepositoryInterface
{
    /**
     * Save log.
     *
     * @param \IWD\BluePaySubs\Api\Data\LogInterface $log
     * @return \IWD\BluePaySubs\Api\Data\LogInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\LogInterface $log);

    /**
     * Retrieve log.
     *
     * @param int $logId
     * @return \IWD\BluePaySubs\Api\Data\LogInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($logId);

    /**
     * Retrieve log.
     *
     * @param int $logId
     * @return \IWD\BluePaySubs\Api\Data\LogInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load($logId);

    /**
     * Retrieve logs matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \IWD\BluePaySubs\Api\Data\LogSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete log.
     *
     * @param \IWD\BluePaySubs\Api\Data\LogInterface $log
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\LogInterface $log);

    /**
     * Delete log by ID.
     *
     * @param int $logId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($logId);
}
