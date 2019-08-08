<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Api;

/**
 * Interface AdapterResponseInterface
 * @package IWD\BluePay\Api
 */
interface AdapterResponseInterface
{
    const CARD_TYPE = 'CARD_TYPE';
    const MASKED_ACCOUNT = 'PAYMENT_ACCOUNT';
    const CC_EXPIRES = "CC_EXPIRES";

    const RESPONSE_CODE_APPROVED = 'APPROVED';

    const RESPONSE_CODE_DECLINED = 'DECLINED';

    const RESPONSE_CODE_ERROR    = 'ERROR';

    const RESPONSE_CODE_MISSING  = 'MISSING';

    const MESSAGE = 'MESSAGE';

    const RESPONSE_MESSAGE_DUPLICATE = 'DUPLICATE';

    const TRANSACTION_ID = 'TRANS_ID';

    const PAYMENT_ACCOUNT = 'PAYMENT_ACCOUNT';

    const REMOTE_IP = 'REMOTE_IP';

    const CARD_EXPIRE = 'CARD_EXPIRE';

    /**
     * @return string
     */
    public function getTransactionId();

    /**
     * @return string
     */
    public function getPaymentAccount();

    /**
     * @return string
     */
    public function getRemoteIp();

    /**
     * @return string
     */
    public function getCardExpire();

    /**
     * @return boolean
     */
    public function isSuccess();

    /**
     * @return string
     */
    public function getMessage();
}
