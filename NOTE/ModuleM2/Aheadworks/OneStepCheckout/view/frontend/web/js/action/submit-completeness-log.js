/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Aheadworks_OneStepCheckout/js/model/completeness-logger/service-enable-flag'
    ],
    function (
        $,
        urlBuilder,
        storage,
        customer,
        quote,
        serviceEnableFlag
    ) {
        'use strict';

        return function (log) {
            var serviceUrl,
                payload = {fieldCompleteness: log};

            if (!serviceEnableFlag()) {
                return $.Deferred().resolve();
            }

            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/awOsc/guest-carts/:cartId/completeness-log', {
                    cartId: quote.getQuoteId()
                });
            } else {
                serviceUrl = urlBuilder.createUrl('/awOsc/carts/mine/completeness-log', {});
            }

            return storage.post(serviceUrl, JSON.stringify(payload));
        }
    }
);
