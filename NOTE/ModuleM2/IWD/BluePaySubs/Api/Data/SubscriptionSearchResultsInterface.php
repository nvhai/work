<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for subscription search results.
 *
 * @api
 */
interface SubscriptionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get subscriptions.
     *
     * @return \IWD\BluePaySubs\Api\Data\SubscriptionInterface[]
     */
    public function getItems();

    /**
     * Set subscriptions.
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
