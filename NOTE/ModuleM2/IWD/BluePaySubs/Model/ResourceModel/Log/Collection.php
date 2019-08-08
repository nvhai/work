<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model\ResourceModel\Log;

use IWD\BluePaySubs\Setup\InstallSchema;

/**
 * Log collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'log_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = InstallSchema::TABLE_IWD_BLUEPAY_SUBS_LOG . '_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'iwd_log_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'IWD\BluePaySubs\Model\Log',
            'IWD\BluePaySubs\Model\ResourceModel\Log'
        );
    }

    /**
     * Add subscription filter to the current collection.
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @return $this
     */
    public function addSubscriptionFilter(\IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription)
    {
        $this->addFieldToFilter('main_table.subs_id', $subscription->getId());

        return $this;
    }

    /**
     * Join orders table for ID and total.
     *
     * @return $this
     */
    public function includeOrderInfo()
    {
        $this->getSelect()->joinLeft(
            [
                'sales_order' => $this->getTable('sales_order')
            ],
            'sales_order.increment_id=main_table.order_increment_id',
            [
                'order_id'            => 'entity_id',
                'grand_total'         => 'grand_total',
                'order_currency_code' => 'order_currency_code',
            ]
        );

        return $this;
    }
}
