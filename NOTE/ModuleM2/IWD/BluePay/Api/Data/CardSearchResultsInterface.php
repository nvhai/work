<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for saved card search results.
 * @api
 */
interface CardSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get cards list.
     *
     * @return \IWD\BluePay\Api\Data\CardInterface[]
     */
    public function getItems();

    /**
     * Set cards list.
     *
     * @param \IWD\BluePay\Api\Data\CardInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
