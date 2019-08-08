<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Api\Data;

/**
 * Subscription data storage and processing
 */
interface SubscriptionInterface
{
    const ID = 'entity_id';
    const REBILL_ID = 'rebill_id';
    const TRANSACTION_ID = 'transaction_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const LAST_DATE = 'last_date';
    const NEXT_DATE = 'next_date';
    const STATUS = 'status';
    const STORE_ID = 'store_id';
    const CUSTOMER_ID = 'customer_id';
    const QUOTE_ID = 'quote_id';
    const PERIOD_INTERVAL = 'period_interval';
    const PERIOD = 'period';
    const DESCRIPTION = 'description';
    const AMOUNT = 'amount';
    const CYCLES = 'cycles';
    const CYCLES_RUN_COUNT = 'cycles_run_count';
    const PAYMENT_FAILED_RUN_COUNT = 'payment_failed_run_count';
    const ADDITIONAL_INFORMATION = 'additional_information';

    /**
     * Get subscription ID.
     *
     * @return int
     */
    public function getId();

    /**
     * Get rebill ID
     *
     * @return int
     */
    public function getRebillId();

    /**
     * Get transaction ID
     *
     * @return int
     */
    public function getTransactionId();

    /**
     * Get created at date.
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Get updated at date.
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Get last run date.
     *
     * @return string
     */
    public function getLastDate();

    /**
     * Get next run date.
     *
     * @return string
     */
    public function getNextDate();

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus();

    /**
     * Get store Id.
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Get customer Id.
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Get quote Id.
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Get period interval
     *
     * @return int
     */
    public function getPeriodInterval();

    /**
     * Get period
     *
     * @return string
     */
    public function getPeriod();

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get amount.
     *
     * @return float
     */
    public function getAmount();

    /**
     * Get cycles.
     *
     * @return int
     */
    public function getCycles();

    /**
     * Get cycles run count.
     *
     * @return int
     */
    public function getCyclesRunCount();

    /**
     * Get payment failed run count.
     *
     * @return int
     */
    public function getPaymentFailedRunCount();

    /**
     * Get additional information.
     *
     * If $key is set, will return that value or null; otherwise, will return an array of all additional date.
     *
     * @param string|null $key
     * @return mixed|null
     */
    public function getAdditionalInformation($key = null);

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set rebill ID
     *
     * @param int $rebillId
     * @return $this
     */
    public function setRebillId($rebillId);

    /**
     * Set transaction ID
     *
     * @param int $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId);

    /**
     * Set created-at date.
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Set updated-at date.
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Set last run date.
     *
     * @param string $lastDate
     * @return $this
     */
    public function setLastDate($lastDate);

    /**
     * Set next run date.
     *
     * @param string $nextDate
     * @return $this
     */
    public function setNextDate($nextDate);

    /**
     * Set status.
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Set subscription store ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Set subscription customer ID
     *
     * @param $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Set source quote ID
     *
     * @param int|null $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);

    /**
     * Set period interval
     *
     * @param int $periodInterval
     * @return $this
     */
    public function setPeriodInterval($periodInterval);

    /**
     * Set period
     *
     * @param string $period
     * @return $this
     */
    public function setPeriod($period);


    /**
     * Set subscription description. This will typically (but not necessarily) be the item name.
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Set amount; actual amount is handled by the quote.
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Set cycles.
     *
     * @param int $cycles
     * @return $this
     */
    public function setCycles($cycles);

    /**
     * Set cycles run count.
     *
     * @param int $cyclesRunCount
     * @return $this
     */
    public function setCyclesRunCount($cyclesRunCount);

    /**
     * Set payment failed run count.
     *
     * @param int $paymentFailedRunCount
     * @return $this
     */
    public function setPaymentFailedRunCount($paymentFailedRunCount);

    /**
     * Set additional information.
     *
     * Can pass in a key-value pair to set one value, or a single parameter (associative array) to overwrite all data.
     *
     * @param string $key
     * @param string|null $value
     * @return $this
     */
    public function setAdditionalInformation($key, $value = null);

    /**
     * Set source quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return $this
     */
    public function setQuote(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * Associate a given order with the subscription, and record the transaction details.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param string|null $message
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function recordBilling(\Magento\Sales\Api\Data\OrderInterface $order, $message = null);

    /**
     * Calculate subscription amount incl. shipping amount.
     *
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function calculateAmount($amount = 0);

    /**
     * Calculate and set next run date for the subscription.
     *
     * @param int $periodInterval
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function calculateNextRun($periodInterval = 0);

    /**
     * Increment run_count by one.
     *
     * @return $this
     */
    public function incrementRunCount();

    /**
     * Check whether subscription has billed to the prescribed length.
     *
     * @return bool
     */
    public function isComplete();

    /**
     * Set last_run to the current datetime.
     *
     * @return $this
     */
    public function updateLastRunTime();

    /**
     * Add a new log to the subscription.
     *
     * @param string $message
     * @param array $params
     * @return $this
     */
    public function addLog($message, $params = []);
}
