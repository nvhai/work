<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Plugin\Checkout\Block;

use IWD\BluePaySubs\Setup\InstallData;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Registry;
use Magento\Checkout\Model\Session\Proxy as CheckoutSession;

class QuoteShortcutButtons
{
    /**
     * @var \IWD\BluePaySubs\Helper\Data
     */
    protected $helper;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * QuoteShortcutButtons constructor.
     * @param \IWD\BluePaySubs\Helper\Data $helper
     * @param Registry $registry
     */
    public function __construct(
        \IWD\BluePaySubs\Helper\Data $helper,
        Registry $registry,
        CheckoutSession $checkoutSession
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param ObserverInterface $subject
     * @param callable $proceed
     * @param EventObserver $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(ObserverInterface $subject, callable $proceed, EventObserver $observer)
    {
        if ($this->helper->moduleIsActive()) {
            if ($observer->getEvent()->getIsCatalogProduct()) {
                $product = $this->registry->registry('product');
                if ($product && $product->getData(InstallData::SUBS_ACTIVE)) {
                    return;
                }
            }
            if ($this->helper->quoteContainsSubscription($this->checkoutSession->getQuote())) {
                return;
            }
        }

        // Quote & curent product not contain subs, call standart logic
        $proceed($observer);

        return;
    }
}