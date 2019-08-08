<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Observer;

/**
 * PaymentMethodIsActive Class
 */
class PaymentMethodIsActive implements \Magento\Framework\Event\ObserverInterface
{
    const SUBS_PAYMENT_ACTIVE_CODE = 'iwd_bluepay';

    /**
     * @var \IWD\BluePaySubs\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * GenerateSubscriptionsObserver constructor.
     *
     * @param \IWD\BluePaySubs\Helper\Data $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \IWD\BluePaySubs\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Disable ineligible payment methods when purchasing a subscription. Tokenbase methods only.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->moduleIsActive() !== true) {
            return;
        }

        /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
        $method = $observer->getEvent()->getData('method_instance');

        /** @var \Magento\Framework\DataObject $result */
        $result = $observer->getEvent()->getData('result');

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        if ($this->helper->quoteContainsSubscription($quote)
            && strpos($method->getCode(), self::SUBS_PAYMENT_ACTIVE_CODE) === false) {
            $result->setData('is_available', false);
        }
    }
}
