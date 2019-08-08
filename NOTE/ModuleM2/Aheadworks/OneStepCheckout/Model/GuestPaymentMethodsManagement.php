<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model;

use Aheadworks\OneStepCheckout\Api\PaymentMethodsManagementInterface;
use Aheadworks\OneStepCheckout\Api\GuestPaymentMethodsManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class GuestPaymentMethodsManagement
 * @package Aheadworks\OneStepCheckout\Model
 */
class GuestPaymentMethodsManagement implements GuestPaymentMethodsManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var PaymentMethodsManagementInterface
     */
    private $paymentMethodsManagement;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param PaymentMethodsManagementInterface $paymentMethodsManagement
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        PaymentMethodsManagementInterface $paymentMethodsManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->paymentMethodsManagement = $paymentMethodsManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethods(
        $cartId,
        AddressInterface $shippingAddress,
        AddressInterface $billingAddress = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()
            ->load($cartId, 'masked_id');
        return $this->paymentMethodsManagement->getPaymentMethods(
            $quoteIdMask->getQuoteId(),
            $shippingAddress,
            $billingAddress
        );
    }
}
