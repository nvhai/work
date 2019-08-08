<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model;

use IWD\BluePaySubs\Api\Data\LogInterface;
use IWD\BluePaySubs\Setup\InstallSchema;

/**
 * Subscription log - change record
 */
class Log extends \Magento\Framework\Model\AbstractModel implements LogInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = InstallSchema::TABLE_IWD_BLUEPAY_SUBS_LOG . '_log';

    /**
     * @var string
     */
    protected $_eventObject = 'log';

    /**
     * @var Source\Status
     */
    protected $statusSource;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendSession;

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = self::ID;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Source\Status $statusSource
     * @param \Magento\Backend\Model\Auth\Session\Proxy $backendSession
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \IWD\BluePaySubs\Model\Source\Status $statusSource,
        \Magento\Backend\Model\Auth\Session\Proxy $backendSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->statusSource = $statusSource;
        $this->backendSession = $backendSession;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Set subscription log is associated to.
     *
     * @param Subscription $subscription
     * @return $this
     */
    public function setSubscription(Subscription $subscription)
    {
        $this->setData(self::SUBS_ID, $subscription->getId());

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setStatus($newStatus)
    {
        if ($this->statusSource->isAllowedStatus($newStatus)) {
            $this->setData(self::STATUS, $newStatus);
        }

        return $this;
    }

    /**
     * Get subscription status.
     *
     * @return string $this
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set associated order Increment ID.
     *
     * We save increment ID rather than order ID because the order has not yet been saved when subscription
     * generation occurs. No ID to be had in that case.
     *
     * @param string $orderIncrementId
     * @return $this
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        return $this->setData(self::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    /**
     * Get associated order increment ID.
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getData(self::ORDER_INCREMENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setTransactionId($transactionId)
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * @inheritdoc
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * Set log message.
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Get log message.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('IWD\BluePaySubs\Model\ResourceModel\Log');
    }

    /**
     * Finalize before saving.
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();

        if ($this->isObjectNew()) {
            /**
             * Set date.
             */
            $now = new \DateTime('@' . time());
            $this->setData(self::CREATED_AT, $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));

            /**
             * Set agent (if any).
             */
            if ($this->hasData(self::AGENT_ID) == false) {
                $this->determineAgent();
            }
        }

        return $this;
    }

    /**
     * Attempt to determine whether this action was triggered by the customer, an admin, or neither. Result is stored
     * with the log.
     *
     * @return $this
     */
    protected function determineAgent()
    {
        $id = 0;

        try {
            if ($this->_appState->getAreaCode() == \Magento\Framework\App\Area::AREA_FRONTEND) {
                // Frontend: Customer action.
                $id = -1;
            } elseif ($this->_appState->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
                // Admin: Admin user action (record who).
                $id = $this->backendSession->getUser()->getId();
            }
        } catch (\Exception $e) {
            $id = 0;
        }
        $this->setAgentId($id);

        return $this;
    }

    /**
     * Set ID of agent responsible for the logged action. admin user_id, or -1 for customer.
     *
     * @param int $agentId
     * @return $this
     */
    public function setAgentId($agentId)
    {
        return $this->setData(self::AGENT_ID, $agentId);
    }

    /**
     * Get ID of agent responsible for the logged action. admin user_id, or -1 for customer.
     *
     * @return int
     */
    public function getAgentId()
    {
        return $this->getData(self::AGENT_ID);
    }

    /**
     * Get created-at date.
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }
}
