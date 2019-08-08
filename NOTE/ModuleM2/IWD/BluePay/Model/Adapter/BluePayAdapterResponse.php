<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Model\Adapter;

use Magento\Framework\DataObject;
use IWD\BluePay\Api\AdapterResponseInterface;

class BluePayAdapterResponse extends DataObject implements AdapterResponseInterface
{
    /**
     * @inheritdoc
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * @return string
     */
    public function getPaymentAccount()
    {
        return $this->getData(self::PAYMENT_ACCOUNT);
    }

    /**
     * @return string
     */
    public function getRemoteIp()
    {
        return $this->getData(self::REMOTE_IP);
    }

    /**
     * @return string
     */
    public function getCardExpire()
    {
        return $this->getData(self::CARD_EXPIRE);
    }

    /**
     * @return bool
     */
    public function isSuccess() {
        return ($this->getData('Result') == self::RESPONSE_CODE_APPROVED
            && $this->getMessage() != self::RESPONSE_MESSAGE_DUPLICATE);
    }

    /**
     * @return mixed|string
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }
}