/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'mage/utils/wrapper',
        'Magento_Checkout/js/model/quote',
        'Aheadworks_OneStepCheckout/js/model/same-as-shipping-flag'
    ],
    function ($, wrapper, quote, sameAsShippingFlag) {
        'use strict';

        return function (selectBillingAddressAction) {
            return wrapper.wrap(selectBillingAddressAction, function (originalAction, billingAddress) {
                var address = null,
                    sameAsBilling = !quote.isQuoteVirtual() && sameAsShippingFlag.sameAsShipping();

                if (quote.shippingAddress() && billingAddress.getCacheKey() == quote.shippingAddress().getCacheKey()) {
                    address = $.extend({}, billingAddress);
                    address.saveInAddressBook = sameAsBilling && quote.shippingAddress().saveInAddressBook ? 1 : 0;
                } else {
                    address = billingAddress;
                }
                quote.billingAddress(address);
            });
        };
    }
);
