/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_CheckoutAgreements/js/model/agreements-assigner',
    'Aheadworks_OneStepCheckout/js/model/newsletter/assigner',
    'Aheadworks_OneStepCheckout/js/model/order-note/assigner',
    'Aheadworks_OneStepCheckout/js/model/delivery-date/assigner'
], function (
    $,
    quote,
    urlBuilder,
    storage,
    errorProcessor,
    customer,
    fullScreenLoader,
    agreementsAssigner,
    newsletterAssigner,
    orderNoteAssigner,
    deliveryDateAssigner
) {
    'use strict';

    return function () {
        return function (messageContainer) {
            var serviceUrl,
                payload,
                paymentData = quote.paymentMethod();

            agreementsAssigner(paymentData);
            newsletterAssigner(paymentData);
            orderNoteAssigner(paymentData);
            deliveryDateAssigner(paymentData);

            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/set-payment-information', {
                    cartId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    email: quote.guestEmail,
                    paymentMethod: paymentData
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/set-payment-information', {});
                payload = {
                    cartId: quote.getQuoteId(),
                    paymentMethod: paymentData
                };
            }
            fullScreenLoader.startLoader();

            return storage.post(
                serviceUrl, JSON.stringify(payload)
            ).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                    fullScreenLoader.stopLoader();
                }
            );
        };
    };
});
