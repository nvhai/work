<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Api;

/**
 * Interface SubsAdapterInterface
 * @package IWD\BluePay\Api
 */
interface SubsAdapterInterface extends \IWD\BluePay\Api\AdapterInterface
{
    /**
     * Get status of rebilling
     *
     * @param int $rebillId
     * @return SubsAdapterInterface
     */
    public function getRebillStatus($rebillId);

    /**
     * Passes rebilling information into the transaction
     *
     * @param array $params
     * @return SubsAdapterInterface
     */
    public function setRebillingInformation(array $params);

    /**
     * Update rebilling information
     *
     * @param array $params
     * @return SubsAdapterInterface
     */
    public function updateRebill(array $params);

    /**
     * Update rebilling status
     *
     * @param string $rebillId
     * @param string $status
     * @return SubsAdapterInterface
     */
    public function updateRebillStatus($rebillId, $status);

    /**
     * Get rebill by transaction
     *
     * @param array $params
     * @return SubsAdapterInterface
     */
    public function getRebillByTransaction(array $params);

    /**
     * Get rebill daily report
     *
     * @param array $params
     * @return SubsAdapterInterface
     */
    public function getRebillDailyReport(array $params);

    /**
     * @return SubsAdapterResponseInterface | SubsAdapterResponseInterface[]
     */
    public function getResponse();
}
