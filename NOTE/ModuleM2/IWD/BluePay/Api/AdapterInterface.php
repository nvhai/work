<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Api;

/**
 * Interface AdapterInterface
 * @package IWD\BluePay\Api
 */
interface AdapterInterface
{
    /**
     * @param $amount
     * @param $masterID
     * @return $this
     */
    public function sale($amount, $masterID = null);

    /**
     * @param $amount
     * @param $masterID
     * @return $this
     */
    public function auth($amount, $masterID = null);

    /**
     * @param $masterID
     * @param $amount
     * @return $this
     */
    public function capture($masterID, $amount = null);

    /**
     * @param $masterID
     * @param $amount
     * @return $this
     */
    public function refund($masterID, $amount = null);

    /**
     * @param $masterID
     * @return $this
     */
    public function void($masterID);

    /**
     * @param $params
     * @return $this
     */
    public function setCustomerInformation($params);

    /**
     * @param $params
     * @return $this
     */
    public function setPaymentInformation($params);

    /**
     * @return $this
     */
    public function process();

    /**
     * @return AdapterResponseInterface
     */
    public function getResponse();
}
