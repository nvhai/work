<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Observer;

/**
 * GuestCheckoutAvailableObserver Class
 */
class GuestCheckoutAvailableObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \IWD\BluePaySubs\Helper\Data
     */
    protected $helper;

    /**
     * GenerateSubscriptionsObserver constructor.
     *
     * @param \IWD\BluePaySubs\Helper\Data $helper
     */
    public function __construct(
        \IWD\BluePaySubs\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Disable guest checkout when purchasing a subscription.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->moduleIsActive() !== true) {
            return;
        }

        /** @var \Magento\Framework\DataObject $result */
        $result = $observer->getEvent()->getData('result');

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote  = $observer->getEvent()->getData('quote');

        /**
         * If it's already inactive, don't care.
         */
        if ($result->getData('is_allowed') == false) {
            return;
        }

        /**
         * Otherwise, check if we have a subscription item. If so, not available.
         */
        if ($this->helper->quoteContainsSubscription($quote)) {
            $result->setData('is_allowed', false);
        }
    }
}
