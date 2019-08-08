<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model;

use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use IWD\BluePaySubs\Model\Source\Period;
use IWD\BluePaySubs\Model\Source\Status;
use IWD\BluePaySubs\Setup\InstallSchema;

/**
 * Subscription data storage and processing
 */
class Subscription extends \Magento\Framework\Model\AbstractModel implements SubscriptionInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = InstallSchema::TABLE_IWD_BLUEPAY_SUBS;

    /**
     * @var string
     */
    protected $_eventObject = 'subs';

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var Source\Status
     */
    protected $statusSource;

    /**
     * @var Source\Period
     */
    protected $periodSource;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $relatedObjects = [
        'before' => [],
        'after' => [],
    ];

    /**
     * @var array
     */
    protected $additionalInfo;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param LogFactory $logFactory
     * @param Source\Status $statusSource
     * @param Source\Period $periodSource
     * @param \Magento\Quote\Api\CartRepositoryInterface\Proxy $cartRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\App\Emulation $emulator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \IWD\BluePaySubs\Model\LogFactory $logFactory,
        \IWD\BluePaySubs\Model\Source\Status $statusSource,
        \IWD\BluePaySubs\Model\Source\Period $periodSource,
        \Magento\Quote\Api\CartRepositoryInterface\Proxy $cartRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $emulator,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->logFactory = $logFactory;
        $this->statusSource = $statusSource;
        $this->periodSource = $periodSource;
        $this->cartRepository = $cartRepository;
        $this->storeManager = $storeManager;
        $this->emulator = $emulator;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('IWD\BluePaySubs\Model\ResourceModel\Subscription');
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function getRebillId()
    {
        return $this->getData(self::REBILL_ID);
    }

    /**
     * @inheritdoc
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function getLastDate()
    {
        return $this->getData(self::LAST_DATE);
    }

    /**
     * @inheritDoc
     */
    public function getNextDate()
    {
        return $this->getData(self::NEXT_DATE);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return (int)$this->getData(self::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function getQuoteId()
    {
        return (int)$this->getData(self::QUOTE_ID);
    }

    /**
     * @inheritDoc
     */
    public function getPeriodInterval()
    {
        return $this->getData(self::PERIOD_INTERVAL);
    }

    /**
     * @inheritDoc
     */
    public function getPeriod()
    {
        return $this->getData(self::PERIOD);
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function getCycles()
    {
        return $this->getData(self::CYCLES);
    }

    /**
     * @inheritDoc
     */
    public function getCyclesRunCount()
    {
        return $this->getData(self::CYCLES_RUN_COUNT);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentFailedRunCount()
    {
        return $this->getData(self::PAYMENT_FAILED_RUN_COUNT);
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalInformation($key = null)
    {
        if (is_null($this->additionalInfo)) {
            $this->additionalInfo = json_decode(parent::getData('additional_information'), 1);
        }

        if (!is_null($key)) {
            return (isset($this->additionalInfo[$key]) ? $this->additionalInfo[$key] : null);
        }

        return $this->additionalInfo;
    }

    /**
     * @inheritDoc
     */
    public function setId($Id)
    {
        return $this->setData(self::ID, $Id);
    }

    /**
     * @inheritDoc
     */
    public function setRebillId($rebillId)
    {
        return $this->setData(self::REBILL_ID, $rebillId);
    }

    /**
     * @inheritDoc
     */
    public function setTransactionId($transactionId)
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function setLastDate($lastDate)
    {
        return $this->setData(self::LAST_DATE, $lastDate);
    }

    /**
     * @inheritDoc
     */
    public function setNextDate($nextDate)
    {
        if (!is_numeric($nextDate)) {
            $nextRun = strtotime($nextDate);
        }
        if ($nextRun == 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please provide a valid date for the next scheduled run.')
            );
        }
        $now = new \DateTime('@' . $nextRun);

        return $this->setData(self::NEXT_DATE, $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
    }

    /**
     * Set subscription status.
     *
     * @param string $newStatus
     * @param string $message Message to log for the change (optional)
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setStatus($newStatus, $message = null)
    {
        $oldStatus = $this->getStatus();

        if ($newStatus != $oldStatus) {
            if ($this->statusSource->isAllowedStatus($newStatus)) {
                $this->setData('status', $newStatus);

                $this->_eventManager->dispatch(
                    'iwd_subs_status_change',
                    [
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'message' => $message,
                        'subscription' => $this,
                    ]
                );

                /**
                 * If status changed, log the event.
                 */
                if ($oldStatus != '') {
                    if (!is_null($message)) {
                        $this->addLog($message);
                    } else {
                        $this->addLog(__("Status changed to '%1'", $this->getStatus()));
                    }
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid status "%1"', $newStatus));
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Set subscription customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritdoc
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * @inheritdoc
     */
    public function setPeriodInterval($periodInterval)
    {
        return $this->setData(self::PERIOD_INTERVAL, $periodInterval);
    }

    /**
     * @inheritdoc
     */
    public function setPeriod($period)
    {
        if ($allowedPeriod = $this->periodSource->getAllowedPeriod($period)) {
            $this->setData(self::PERIOD, $allowedPeriod);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid period "%1"', $period)
            );
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritDoc
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function setCycles($cycles)
    {
        return $this->setData(self::CYCLES, $cycles);
    }

    /**
     * @inheritDoc
     */
    public function setCyclesRunCount($cyclesRunCount)
    {
        return $this->setData(self::CYCLES_RUN_COUNT, $cyclesRunCount);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentFailedRunCount($paymentFailedRunCount)
    {
        return $this->setData(self::PAYMENT_FAILED_RUN_COUNT, $paymentFailedRunCount);
    }

    /**
     * @inheritdoc
     */
    public function setAdditionalInformation($key, $value = null)
    {
        if (!is_null($value)) {
            if (is_null($this->additionalInfo)) {
                $this->additionalInfo = [];
            }

            $this->additionalInfo[$key] = $value;
        } elseif (is_array($key)) {
            $this->additionalInfo = $key;
        }

        return parent::setData('additional_information', json_encode($this->additionalInfo));
    }

    /**
     * Set source quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return $this
     */
    public function setQuote(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $this->setData('quote', $quote);
        $this->setQuoteId($quote->getId());

        return $this;
    }

    /**
     * Get subscription quote
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Exception
     */
    public function getQuote()
    {
        if ($this->hasData('quote') !== true && $this->hasData('quote_id')) {
            // If we are not in the correct scope, we have to emulate it to load the quote.
            $emulate = ($this->storeManager->getStore()->getStoreId() != $this->getStoreId()) ? true : false;
            if ($emulate === true) {
                $this->emulator->startEnvironmentEmulation($this->getStoreId());
            }

            try {
                $quote = $this->cartRepository->get($this->getQuoteId());
            } catch (\Exception $e) {
                if ($emulate === true) {
                    $this->emulator->stopEnvironmentEmulation();
                }

                throw $e;
            }

            if ($emulate === true) {
                $this->emulator->stopEnvironmentEmulation();
            }

            $this->setData('quote', $quote);
        }

        return $this->getData('quote');
    }

    /**
     * Associate a given order with the subscription, and record the transaction details.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param string|null $message
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function recordBilling(
        \Magento\Sales\Api\Data\OrderInterface $order,
        $message = null
    )
    {
        $this->updateLastRunTime();
        $this->incrementRunCount();
        if ($this->isComplete()) {
            $this->setStatus(Status::STATUS_EXPIRED);
        }
        $this->addLog($message, ['order_increment_id' => $order->getIncrementId()]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function calculateAmount($amount = 0)
    {
        if (empty($amount)) {
            $amount = $this->getAmount();
        }
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->getQuote();
        $items = $quote->getAllVisibleItems();
        if (!$items) {
            throw new \Magento\Framework\Exception\LocalizedException(__("No product items in subscription quote."));
        }
        $item = array_shift($items);
        if ($item->getOriginalCustomPrice() != $amount) {
            $item->setOriginalCustomPrice($amount);
        }
        if ($quote->getIsVirtual() == false && $quote->getShippingAddress()) {
            $address = $quote->getShippingAddress();
            $address->setCollectShippingRates(true)
                ->collectShippingRates();
        }
        $quote->collectTotals();
        $this->setAmount($quote->getGrandTotal());
        return $this;
    }

    /**
     * Calculate and set next run date for the subscription.
     *
     * @param int $periodInterval
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function calculateNextRun($periodInterval = 0)
    {
        if (empty($periodInterval)) {
            $periodInterval = $this->getPeriodInterval();
            $period = $this->getPeriod();
        } else {
            $period = Period::PERIOD_DAY;
        }

        if (empty($periodInterval) || empty($period)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Subscription period must be set to calculate schedule.')
            );
        }

        $nextRunTime = strtotime(
            sprintf('+%s %s', $periodInterval, $period),
            time()
        );

        /**
         * Convert to UTC date and set.
         */
        $now = new \DateTime('@' . $nextRunTime);

        return $this->setNextDate($now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
    }

    /**
     * Increment run_count by one.
     *
     * @return $this
     */
    public function incrementRunCount()
    {
        $this->setData(self::CYCLES_RUN_COUNT, $this->getCyclesRunCount() + 1);

        return $this;
    }

    /**
     * Finalize before saving.
     *
     * @return $this
     * @throws \Exception
     */
    public function beforeSave()
    {
        parent::beforeSave();

        /**
         * Save child records in conjunction with the parent.
         */
        if (count($this->relatedObjects['before']) > 0) {
            /** @var \Magento\Framework\Model\AbstractModel $object */
            foreach ($this->relatedObjects['before'] as $object) {
                if ($object->getData('subs_id') != $this->getId()) {
                    $object->setData('subs_id', $this->getId());
                }

                if ($object->hasDataChanges()) {
                    if ($object instanceof \Magento\Quote\Api\Data\CartInterface) {
                        $object->setUpdatedAt('2038-01-01 00:00:00');
                    }

                    $object->save();
                }
            }
        }

        /**
         * Make sure we have the quote.
         */
        if ($this->getQuoteId() < 1 && $this->hasData('quote')) {
            $this->setQuoteId($this->getQuote()->getId());
        }

        /**
         * Update dates.
         */
        $now = new \DateTime('@' . time());

        if ($this->isObjectNew()) {
            $this->setData(self::CREATED_AT, $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
        }

        $this->setData(self::UPDATED_AT, $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));

        return $this;
    }

    /**
     * Check whether subscription has billed to the prescribed length.
     *
     * @return bool
     */
    public function isComplete()
    {
        if ($this->getStatus() === Source\Status::STATUS_EXPIRED) {
            return true;
        }

        if ($this->getCycles() > 0 && $this->getCyclesRunCount() >= $this->getCycles()) {
            return true;
        }

        return false;
    }

    /**
     * Set last_run to the current datetime.
     *
     * @return $this
     */
    public function updateLastRunTime()
    {
        $now = new \DateTime('@' . time());

        $this->setData(self::LAST_DATE, $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));

        return $this;
    }

    /**
     * Add a new log to the subscription.
     *
     * @param string $message
     * @param array $params
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addLog($message, $params = [])
    {
        /** @var \IWD\BluePaySubs\Model\Log $log */
        $log = $this->logFactory->create();
        $log->setSubscription($this);
        $log->setStatus($this->getStatus());
        $log->setDescription((string)$message);
        $log->addData($params);

        $this->addRelatedObject($log);

        return $this;
    }

    /**
     * Retrieve array of related objects
     *
     * @return array
     */
    public function getRelatedObjects()
    {
        return $this->relatedObjects;
    }

    /**
     * Add object to related objects, to be saved with the parent model
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param bool $saveBeforeParent
     * @return $this
     */
    public function addRelatedObject(\Magento\Framework\Model\AbstractModel $object, $saveBeforeParent = false)
    {
        if ($saveBeforeParent === false) {
            $this->relatedObjects['after'][] = $object;
        } else {
            $this->relatedObjects['before'][] = $object;
        }

        return $this;
    }

    /**
     * Processing object after save data
     *
     * @return $this
     * @throws \Exception
     */
    public function afterSave()
    {
        /**
         * Save child records in conjunction with the parent.
         */
        if (count($this->relatedObjects['after']) > 0) {
            /** @var \Magento\Framework\Model\AbstractModel $object */
            foreach ($this->relatedObjects['after'] as $object) {
                if ($object->getData('subs_id') != $this->getId()) {
                    $object->setData('subs_id', $this->getId());
                }

                if ($object->hasDataChanges()) {
                    if ($object instanceof \Magento\Quote\Api\Data\CartInterface) {
                        $object->setUpdatedAt('2038-01-01 00:00:00');
                    }

                    $object->save();
                }
            }
        }

        return parent::afterSave();
    }
}
