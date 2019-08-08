<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model;

use Aheadworks\OneStepCheckout\Api\PaymentMethodsManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * Class PaymentMethodsManagement
 * @package Aheadworks\OneStepCheckout\Model
 */
class PaymentMethodsManagement implements PaymentMethodsManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        PaymentMethodManagementInterface $paymentMethodManagement,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethods(
        $cartId,
        AddressInterface $shippingAddress,
        AddressInterface $billingAddress = null
    ) {
        if (!$shippingAddress->getCustomerAddressId()) {
            $shippingAddress->setCustomerAddressId(null);
        }

        if (!$shippingAddress->getCountryId()) {
            return [];
        }

        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $quote
            ->setIsMultiShipping(false)
            ->setShippingAddress($shippingAddress);
        if ($billingAddress) {
            if (!$billingAddress->getCustomerAddressId()) {
                $billingAddress->setCustomerAddressId(null);
            }
            $quote->setBillingAddress($billingAddress);
        }

        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(__('Unable to retrieve payment methods. Please check input data.'));
        }

        return $this->paymentMethodManagement->getList($cartId);
    }
}
