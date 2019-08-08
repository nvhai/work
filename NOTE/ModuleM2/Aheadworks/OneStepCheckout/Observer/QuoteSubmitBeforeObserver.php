<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class QuoteSubmitBeforeObserver
 * @package Aheadworks\OneStepCheckout\Observer
 */
class QuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var OrderInterface $order */
        $order = $event->getOrder();
        /** @var Quote $quote */
        $quote = $event->getQuote();

        $order->setAwOrderNote($quote->getAwOrderNote());
        $order->setAwDeliveryDate($quote->getAwDeliveryDate());
        $order->setAwDeliveryDateFrom($quote->getAwDeliveryDateFrom());
        $order->setAwDeliveryDateTo($quote->getAwDeliveryDateTo());

        return $this;
    }
}
