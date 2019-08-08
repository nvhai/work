<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\OrderNote;

use Aheadworks\OneStepCheckout\Model\Config;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class PaymentDataExtensionProcessor
 * @package Aheadworks\OneStepCheckout\Model\OrderNote
 */
class PaymentDataExtensionProcessor
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param Config $config
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        Config $config,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->config = $config;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Process subscriber extension attributes of payment data
     *
     * @param PaymentInterface $paymentData
     * @param int $cartId
     * @return void
     */
    public function process(PaymentInterface $paymentData, $cartId)
    {
        if ($this->config->isOrderNoteEnabled()) {
            $extensionAttributes = $paymentData->getExtensionAttributes();
            $orderNote = $extensionAttributes === null
                ? false
                : $extensionAttributes->getOrderNote();

            if ($orderNote) {
                $quote = $this->quoteRepository->getActive($cartId);
                $quote->setAwOrderNote($orderNote);
                $this->quoteRepository->save($quote);
            }
        }
    }
}
