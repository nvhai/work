<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Api\Data;

use IWD\BluePaySubs\Model\Subscription;

/**
 * Subscription log - change record
 *
 * @api
 */
interface LogInterface
{
    const ID = 'log_id';
    const SUBS_ID = 'subs_id';
    const CREATED_AT = 'created_at';
    const STATUS = 'status';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const TRANSACTION_ID = 'transaction_id';
    const AGENT_ID = 'agent_id';
    const DESCRIPTION = 'description';
    const ADDITIONAL_INFORMATION = 'additional_information';

    /**
     * Get log ID
     *
     * @return int
     */
    public function getId();

    /**
     * Set log ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set subscription log is associated to.
     *
     * @param Subscription $subscription
     * @return $this
     */
    public function setSubscription(Subscription $subscription);

    /**
     * Set subscription status.
     *
     * @param string $newStatus
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setStatus($newStatus);

    /**
     * Get subscription status.
     *
     * @return string
     */
    public function getStatus();

    /**
     * Get associated order increment ID.
     *
     * @return string
     */
    public function getOrderIncrementId();

    /**
     * Set associated order increment ID.
     *
     * @param string $orderIncrementId
     * @return $this
     */
    public function setOrderIncrementId($orderIncrementId);

    /**
     * Get associated transaction ID.
     *
     * @return string
     */
    public function getTransactionId();

    /**
     * Set associated transaction ID.
     *
     * @param string $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId);

    /**
     * Set log message.
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get log message.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set ID of agent responsible for the logged action. admin user_id, or -1 for customer.
     *
     * @param int $agentId
     * @return $this
     */
    public function setAgentId($agentId);

    /**
     * Get ID of agent responsible for the logged action. admin user_id, or -1 for customer.
     *
     * @return int
     */
    public function getAgentId();

    /**
     * Get created-at date.
     *
     * @return string
     */
    public function getCreatedAt();
}
