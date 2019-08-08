<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Gateway\Http\Client;

class TransactionVoid extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function prepare(\IWD\BluePay\Api\AdapterInterface $adapter, array $data)
    {
        $adapter->void($data['transaction_id']);

        return $this;
    }
}
