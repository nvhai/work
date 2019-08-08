/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/payment-service',
        'Aheadworks_OneStepCheckout/js/action/redirect-on-empty-quote'
    ],
    function (
        quote,
        urlBuilder,
        storage,
        errorProcessor,
        fullScreenLoader,
        customer,
        methodConverter,
        paymentService,
        redirectOnEmptyQuoteAction
    ) {
        'use strict';

        return function (itemId) {
            var serviceUrl;

            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/awOsc/guest-carts/:cartId/cart-items/:itemId', {
                    cartId: quote.getQuoteId(),
                    itemId: itemId
                });
            } else {
                serviceUrl = urlBuilder.createUrl('/awOsc/carts/mine/cart-items/:itemId', {
                    itemId: itemId
                });
            }
            fullScreenLoader.startLoader();

            return storage.delete(
                serviceUrl
            ).done(
                function (response) {
                    var cartDetails = response.cart_details,
                        paymentDetails = response.payment_details;

                    quote.setTotals(paymentDetails.totals);
                    quote.setQuoteData(cartDetails);
                    paymentService.setPaymentMethods(methodConverter(paymentDetails.payment_methods));

                    if (cartDetails.items_count < 1) {
                        redirectOnEmptyQuoteAction.execute();
                    }
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            ).always(function () {
                fullScreenLoader.stopLoader();
            });
        };
    }
);
