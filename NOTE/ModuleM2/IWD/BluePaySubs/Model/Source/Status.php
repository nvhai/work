<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model\Source;

use IWD\BluePaySubs\Api\Data\SubscriptionInterface;

/**
 * Status Class
 */
class Status extends \Magento\Catalog\Model\Product\Attribute\Source\Status
{
    const STATUS_ACTIVE = 'active';
    const STATUS_STOPPED = 'stopped';
    const STATUS_EXPIRED = 'expired';
    const STATUS_PAYMENT_FAILED = 'failed';
    const STATUS_DELETED = 'deleted';
    const STATUS_ERROR = 'error';
    const STATUS_RUNNING = 'running';

    /**
     * @var array Possible status values
     */
    protected static $allowedStatuses = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_STOPPED => 'Stopped',
        self::STATUS_EXPIRED => 'Expired',
        self::STATUS_PAYMENT_FAILED => 'Payment Failed',
        self::STATUS_DELETED => 'Deleted',
        self::STATUS_ERROR => 'Error',
        self::STATUS_RUNNING => 'Active'
    ];

    /**
     * @var array Possible status changes (for buttons, et al.)
     *
     * Can set status to key if current status is one of keys
     */
    protected static $allowedChangeMap = [
        self::STATUS_ACTIVE => [
            self::STATUS_PAYMENT_FAILED,
            self::STATUS_STOPPED
        ],
        self::STATUS_STOPPED => [
            self::STATUS_ACTIVE,
            self::STATUS_PAYMENT_FAILED,
        ],
        self::STATUS_PAYMENT_FAILED => [
            self::STATUS_ACTIVE,
            self::STATUS_STOPPED,
        ],
    ];

    /**
     * Get possible status values.
     *
     * @return \string[]
     */
    public function getAllowedStatuses()
    {
        return static::getOptionArray();
    }

    /**
     * Get possible period values.
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return static::$allowedStatuses;
    }

    /**
     * Check whether the given status is one of the allowed values.
     *
     * @param string $status
     * @return bool
     */
    public function isAllowedStatus($status)
    {
        if (in_array($status, array_keys(static::getOptionArray())) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = static::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $opts = [];

        foreach (static::getOptionArray() as $key => $value) {
            $opts[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $opts;
    }

    /**
     * Check whether the given status can be set on the subscription in its current state.
     *
     * @param SubscriptionInterface $subscription
     * @param string $newStatus
     * @return bool
     */
    public function canSetStatus(SubscriptionInterface $subscription, $newStatus)
    {
        if ($this->isAllowedStatus($newStatus) === false) {
            return false;
        }

        $oldStatus = $subscription->getStatus();

        if (!isset(static::$allowedChangeMap[$oldStatus])
            || in_array($newStatus, static::$allowedChangeMap[$oldStatus])) {
            return true;
        }

        return false;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return bool
     */
    public function isActive(SubscriptionInterface $subscription)
    {
        return $subscription->getStatus() == self::STATUS_ACTIVE;
    }
}
