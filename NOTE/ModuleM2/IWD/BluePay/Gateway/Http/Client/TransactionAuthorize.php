<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Gateway\Http\Client;

use IWD\BluePay\Gateway\Request;

/**
 * Class TransactionAuthorize
 */
class TransactionAuthorize extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function prepare(\IWD\BluePay\Api\AdapterInterface $adapter, array $data)
    {
        $adapter->setCustomerInformation($data[Request\CustomerDataBuilder::CUSTOMER]);
        if(empty($data[Request\PaymentDataBuilder::TRANS_ID])) {
            $adapter->setPaymentInformation($data[Request\PaymentDataBuilder::PAYMENT])
                ->auth($data[Request\PaymentDataBuilder::AMOUNT]);
        }
        else {
            $adapter->auth($data[Request\PaymentDataBuilder::AMOUNT], $data[Request\PaymentDataBuilder::TRANS_ID]);
        }

        return $this;
    }
}
