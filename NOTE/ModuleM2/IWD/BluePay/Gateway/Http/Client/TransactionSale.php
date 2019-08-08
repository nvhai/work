<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Gateway\Http\Client;

use IWD\BluePay\Gateway\Request;

/**
 * Class TransactionSale
 */
class TransactionSale extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function prepare(\IWD\BluePay\Api\AdapterInterface $adapter, array $data)
    {
        $adapter->setCustomerInformation($data[Request\CustomerDataBuilder::CUSTOMER]);
        if (empty($data[Request\PaymentDataBuilder::TRANS_ID])) {
            $adapter->setPaymentInformation($data[Request\PaymentDataBuilder::PAYMENT])
                ->sale($data[Request\PaymentDataBuilder::AMOUNT]);
        } else {
            $adapter->sale($data[Request\PaymentDataBuilder::AMOUNT], $data[Request\PaymentDataBuilder::TRANS_ID]);
        }

        return $this;
    }
}
