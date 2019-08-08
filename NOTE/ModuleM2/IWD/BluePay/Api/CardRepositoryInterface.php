<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use IWD\BluePay\Api\Data\CardInterface;

/**
 * Card CRUD interface.
 * @api
 */
interface CardRepositoryInterface
{
    /**
     * Save card.
     *
     * @param CardInterface $card
     * @return CardInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(CardInterface $card);

    /**
     * Retrieve cards matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \IWD\BluePay\Api\Data\CardSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Retrieve card.
     *
     * @param string $hash
     * @return CardInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByHash($hash);

    /**
     * Retrieve cards matching the customer id.
     *
     * @param int $customerId
     * @return \IWD\BluePay\Api\Data\CardSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSavedCcListForCustomer($customerId);

    /**
     * Delete card.
     *
     * @param CardInterface $card
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(CardInterface $card);
}
