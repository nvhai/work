<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for log search results.
 *
 * @api
 */
interface LogSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get subscriptions.
     *
     * @return \IWD\BluePaySubs\Api\Data\LogInterface[]
     */
    public function getItems();

    /**
     * Set subscriptions.
     *
     * @param \IWD\BluePaySubs\Api\Data\LogInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
