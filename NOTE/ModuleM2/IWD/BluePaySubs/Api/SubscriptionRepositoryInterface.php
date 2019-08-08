<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Api;

/**
 * SubscriptionRepositoryInterface
 *
 * @api
 */
interface SubscriptionRepositoryInterface
{
    /**
     * Save subscription.
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @return \IWD\BluePaySubs\Api\Data\SubscriptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\SubscriptionInterface $subscription);

    /**
     * Retrieve subscription.
     *
     * @param int $subscriptionId
     * @return \IWD\BluePaySubs\Api\Data\SubscriptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($subscriptionId);

    /**
     * Retrieve subscription.
     *
     * @param int $subscriptionId
     * @return \IWD\BluePaySubs\Api\Data\SubscriptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load($subscriptionId);

    /**
     * Retrieve subscriptions matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \IWD\BluePaySubs\Api\Data\SubscriptionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete subscription.
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\SubscriptionInterface $subscription);

    /**
     * Delete subscription by ID.
     *
     * @param int $subscriptionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($subscriptionId);
}
