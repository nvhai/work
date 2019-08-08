<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model\ResourceModel\Subscription;

/**
 * UiCollection Class
 */
class UiCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        // Join the quote table for all that good stuff
        $this->join(
            [
                'quote' => $this->getTable('quote')
            ],
            'quote.entity_id=main_table.quote_id',
            [
                'items_count',
                'items_qty',
                'customer_group_id',
                'customer_email',
                'customer_firstname',
                'customer_lastname',
                'quote_currency_code',
            ]
        );

        return $this;
    }
}
