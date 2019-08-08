<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Observer;

use IWD\BluePaySubs\Helper\Data as Helper;

/**
 * QuoteProductAddAfter Class
 */
class QuoteProductAddAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * GenerateSubscriptionsObserver constructor.
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Override item price et al when adding subscriptions to the cart.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->moduleIsActive() !== true) {
            return;
        }

        /** @var \Magento\Quote\Model\Quote\Item[] $quoteItems */
        $quoteItems = $observer->getEvent()->getData('items');

        try {
            foreach ($quoteItems as $quoteItem) {
                if ($this->helper->isItemSubscription($quoteItem) === true) {
                    $price = $this->helper->getItemSubscriptionPrice($quoteItem);

                    if ($price != $quoteItem->getProduct()->getFinalPrice()) {
                        $quoteItem->setOriginalCustomPrice($price);
                    }
                }
            }
        } catch (\Exception $e) {
            return;
        }
    }
}
