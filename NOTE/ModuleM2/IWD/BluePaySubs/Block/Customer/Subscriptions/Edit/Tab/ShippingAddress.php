<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab;

/**
 * Class ShippingAddress
 * @package IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab
 */
class ShippingAddress extends BillingAddress
{
    /**
     * @inheritdoc
     */
    public function getCurrentAddress()
    {
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->getCurrentSubscription();

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $subscription->getQuote();

        return  $quote->getShippingAddress();
    }

    /**
     * @inheritdoc
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/bsubs_edit/shippingAddress', ['id' => $this->getCurrentSubscription()->getId()]);
    }
}