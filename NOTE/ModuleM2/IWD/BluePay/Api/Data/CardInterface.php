<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Api\Data;

/**
 * Saved Card Information Interface.
 * It does not save CC! Only customer payment transaction ID.
 * @api
 */
interface CardInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID              = 'id';
    const CUSTOMER_ID     = 'customer_id';
    const CUSTOMER_EMAIL  = 'customer_email';
    const MASKED_ACCOUNT  = 'masked_account';
    const TRANS_ID        = 'trans_id';
    const EXPIRATION_DATE = 'expires';
    const PAYMENT_ID      = 'payment_id';
    const CUSTOMER_IP     = 'customer_ip';
    const HASH            = 'hash';
    const ADDITIONAL_DATA = 'additional';
    const CREATED_DATE    = 'created_at';
    const UPDATED_DATE    = 'updated_at';
    /**#@-*/
    
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Customer Id
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Get customer email
     *
     * @return string|null
     */
    public function getCustomerEmail();

    /**
     * Get masked account
     *
     * @return string|null
     */
    public function getMaskedAccount();

    /**
     * Get transaction id
     *
     * @return int|null
     */
    public function getTransId();

    /**
     * Get credit card expiration date
     *
     * @param $format bool
     * @return string|null
     */
    public function getExpirationDate($format = false);

    /**
     * Get payment id
     *
     * @return int|null
     */
    public function getPaymentId();

    /**
     * Get Customer Ip
     *
     * @return string|null
     */
    public function getCustomerIp();

    /**
     * Get payment hash
     *
     * @return string|null
     */
    public function getHash();

    /**
     * Get additional data
     *
     * @param $key
     * @return string|null
     */
    public function getAdditionalData($key = null);
    
    /**
     * Get created at date
     *
     * @return string|null
     */
    public function getCreatedAt();
    
    /**
     * Get updated at
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set ID
     *
     * @param int $id
     * @return CardInterface
     */
    public function setId($id);

    /**
     * Set Customer Id
     *
     * @param int $customerId
     * @return CardInterface
     */
    public function setCustomerId($customerId);

    /**
     * Set customer email
     *
     * @param string $email
     * @return CardInterface
     */
    public function setCustomerEmail($email);

    /**
     * Set masked account
     *
     * @param string $maskedAccount
     * @return CardInterface
     */
    public function setMaskedAccount($maskedAccount);

    /**
     * Set customer profile id
     *
     * @param int $transId
     * @return CardInterface
     */
    public function setTransId($transId);

    /**
     * Set credit card expiration date
     *
     * @param string $expirationDate
     * @return CardInterface
     */
    public function setExpirationDate($expirationDate);

    /**
     * Set payment id
     *
     * @param int $paymentId
     * @return CardInterface
     */
    public function setPaymentId($paymentId);

    /**
     * Set Customer Ip
     *
     * @param string $ip
     * @return CardInterface
     */
    public function setCustomerIp($ip);

    /**
     * Set payment hash
     *
     * @param string $hash
     * @return CardInterface
     */
    public function setHash($hash);

    /**
     * Set additional data
     *
     * @param string $additionalData
     * @return CardInterface
     */
    public function setAdditionalData($additionalData);

    /**
     * Add additional data
     *
     * @param string|array $key
     * @param string|null $value
     * @return CardInterface
     */
    public function addAdditionalData($key, $value = null);

    /**
     * Unset additional data
     *
     * @param string|array $key
     * @return CardInterface
     */
    public function unsetAdditionalData($key);

    /**
     * Set created at date
     *
     * @param string $createdAt
     * @return CardInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return CardInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get last 4 numbers from credit cart in format XXXX-1111
     *
     * @return string
     */
    public function getCardLast4();

    /**
     * Get card type
     *
     * @return string
     */
    public function getCardType();

    /**
     * Is credit card date expired
     *
     * @return bool
     */
    public function isExpired();

    /**
     * Is credit card used for place order
     *
     * @return bool
     */
    public function isInUse();

    /**
     * Get expiration month
     *
     * @return string
     */
    public function getExpirationMonth();

    /**
     * Get expiration year
     *
     * @return string
     */
    public function getExpirationYear();
}
