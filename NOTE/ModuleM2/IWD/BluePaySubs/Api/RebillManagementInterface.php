<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Api;

use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Interface RebillManagementInterface
 * @package IWD\BluePay\Api
 */
interface RebillManagementInterface
{
    /**
     * Format for rebilling date
     */
    const REBILL_DATE_FORMAT = 'Y-m-d';

    /**
     * Create subscription rebill
     *
     * @param array $data
     * @param OrderItemInterface $item
     * @param $shippingAmount
     * @return SubsAdapterResponseInterface|null
     */
    public function createRebill(array $data, OrderItemInterface $item, $shippingAmount);

    /**
     * Create/authorize rebill payment
     *
     * @param array $data
     * @return SubsAdapterResponseInterface|null
     */
    public function createRebillPayment(array $data);

    /**
     * Update rebill
     *
     * @param SubscriptionInterface $subscription
     * @return SubsAdapterResponseInterface|null
     */
    public function updateRebill(SubscriptionInterface $subscription);

    /**
     * Update rebilling status
     *
     * @param SubscriptionInterface $subscription
     * @param $newStatus
     * @return SubsAdapterResponseInterface|null
     */
    public function updateRebillStatus(SubscriptionInterface $subscription, $newStatus);

    /**
     * @param SubscriptionInterface $subscription
     * @return SubsAdapterResponseInterface|null
     */
    public function getRebillStatus(SubscriptionInterface $subscription);

    /**
     * @param $startDate
     * @param string $endDate
     * @return SubsAdapterResponseInterface[]|null
     * @throws \Exception
     */
    public function getRebillDailyReport($startDate, $endDate = '');

    /**
     * @param $transId
     * @param $startDate
     * @param string $endDate
     * @return SubsAdapterResponseInterface|null
     * @throws \Exception
     */
    public function getRebillByTransaction($transId, $startDate, $endDate = '');
}
