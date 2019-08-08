<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model\ResourceModel\Subscription;

use IWD\BluePaySubs\Setup\InstallSchema;
use IWD\BluePaySubs\Model\Subscription;

/**
 * Subscription collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = InstallSchema::TABLE_IWD_BLUEPAY_SUBS . '_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'iwd_subs_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'IWD\BluePaySubs\Model\Subscription',
            'IWD\BluePaySubs\Model\ResourceModel\Subscription'
        );
    }

    /**
     * Join quote currency code.
     *
     * @return $this
     */
    public function joinQuoteCurrency()
    {
        $this->join(
            [
                'quote' => $this->getTable('quote'),
            ],
            'quote.entity_id=main_table.' . Subscription::QUOTE_ID,
            [
                'quote_currency_code',
            ]
        );

        return $this;
    }
}
