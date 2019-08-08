<?php

namespace IWD\BluePay\Api\Data;

use Magento\Vault\Api\Data\PaymentTokenInterface;

interface PaymentTokenFactoryInterface
{
    /**
     * Payment Token types
     * @var string
     */
    const TOKEN_TYPE_ACCOUNT = 'account';
    const TOKEN_TYPE_CREDIT_CARD = 'card';

    /**
     * Create payment token entity
     * @param $type string|null
     * @return PaymentTokenInterface
     */
    public function create($type = null);
}