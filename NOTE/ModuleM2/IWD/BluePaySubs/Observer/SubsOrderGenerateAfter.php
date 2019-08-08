<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Api\OrderRepositoryInterface;
use IWD\BluePaySubs\Gateway\Request\RebillDataBuilder;

/**
 * Class SubsOrderGenerateAfter
 * @package IWD\BluePaySubs\Observer
 */
class SubsOrderGenerateAfter implements ObserverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * SubsOrderGenerateAfter constructor.
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        if($lastTransDate = $quote->getPayment()->getAdditionalInformation(RebillDataBuilder::LAST_TRANS_DATE)) {
            $order->setCreatedAt($lastTransDate);
            $this->orderRepository->save($order);
        }
    }
}