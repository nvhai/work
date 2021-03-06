<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Gateway\Http\Client;

use IWD\BluePay\Gateway\Request;

/**
 * Class TransactionCapture
 */
class TransactionCapture extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function prepare(\IWD\BluePay\Api\AdapterInterface $adapter, array $data)
    {
        $adapter->capture(
            $data[Request\CaptureDataBuilder::TRANSACTION_ID],
            $data[Request\PaymentDataBuilder::AMOUNT]
        );

        return $this;
    }
}
