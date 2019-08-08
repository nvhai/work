<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Api;

use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface SubscriptionManagementInterface
 * @package IWD\BluePay\Api
 */
interface SubscriptionManagementInterface
{
    /**
     * Subscription synchronize process
     *
     * @param SubscriptionInterface $subscription
     * @param int $agentId
     * @return \IWD\BluePaySubs\Api\SubscriptionManagementInterface
     * @throws \Exception
     */
    public function synchronize(SubscriptionInterface $subscription, $agentId = 0);

    /**
     * Create subscription payment account
     *
     * @param SubscriptionInterface $subscription
     * @param array $paymentData
     * @param $agentId
     * @throws LocalizedException
     * @throws \Exception
     * @return $this
     */
    public function createPaymentAccount(SubscriptionInterface $subscription, array $paymentData, $agentId = 0);

    /**
     * Change subscription payment account
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @param string $hash
     * @param $agentId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changePaymentAccount(SubscriptionInterface $subscription, $hash, $agentId = 0);

    /**
     * Change subscription billing address to the given data.
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @param array $data Array of address info
     * @param int $agentId
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function changeBillingAddress(SubscriptionInterface $subscription, $data, $agentId = 0);

    /**
     * Change subscription shipping address to the given data.
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @param array $data Array of address info
     * @param int $agentId
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function changeShippingAddress(SubscriptionInterface $subscription, $data, $agentId = 0);
}
