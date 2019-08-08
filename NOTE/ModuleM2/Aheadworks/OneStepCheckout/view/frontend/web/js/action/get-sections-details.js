/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'underscore',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/shipping-service',
        'Aheadworks_OneStepCheckout/js/model/estimation-data-resolver',
        'Aheadworks_OneStepCheckout/js/model/estimation-data',
        'Aheadworks_OneStepCheckout/js/model/checkout-section/cache-key-generator',
        'Aheadworks_OneStepCheckout/js/model/checkout-section/cache',
        'Aheadworks_OneStepCheckout/js/model/checkout-section/service-enable-flag',
        'Aheadworks_OneStepCheckout/js/model/checkout-section/service-busy-flag'
    ],
    function (
        $,
        _,
        quote,
        storage,
        urlBuilder,
        customer,
        errorProcessor,
        methodConverter,
        paymentService,
        shippingService,
        estimationDataResolver,
        estimationData,
        cacheKeyGenerator,
        cacheStorage,
        serviceEnableFlag,
        serviceBusyFlag
    ) {
        'use strict';

        /**
         * Set sections details
         *
         * @param {Object} sectionsDetails
         */
        function setSectionsDetails (sectionsDetails) {
            if (_.has(sectionsDetails, 'payment_methods')) {
                paymentService.setPaymentMethods(methodConverter(sectionsDetails['payment_methods']));
            }
            if (_.has(sectionsDetails, 'shipping_methods')) {
                shippingService.setShippingRates(sectionsDetails['shipping_methods']);
            }
            if (_.has(sectionsDetails, 'totals')) {
                quote.setTotals(sectionsDetails['totals']);
            }
        }

        /**
         * Prepare sections data
         *
         * @param {Array} sections
         * @returns {Array}
         */
        function prepareSectionsData (sections) {
            var sectionsData = [];

            _.each(sections, function (section) {
                sectionsData.push({code: section});
            });

            return sectionsData;
        }

        return function (sections, useCache, messageContainer) {
            var serviceUrl,
                shippingAddress = estimationData.getShippingAddress(),
                billingAddress = estimationData.getBillingAddress(),
                payload = {
                    sections: prepareSectionsData(sections),
                    shippingAddress: shippingAddress,
                    billingAddress: billingAddress
                },
                cacheKey = cacheKeyGenerator.generateCacheKey({
                    shippingAddress: shippingAddress,
                    billingAddress: billingAddress,
                    totals: quote.totals()
                }),
                cache = cacheStorage.get(cacheKey);

            if (!serviceEnableFlag()) {
                return $.Deferred().resolve();
            }

            if (cache && useCache) {
                setSectionsDetails(cache);
                return $.Deferred().resolve();
            } else {
                if (!customer.isLoggedIn()) {
                    serviceUrl = urlBuilder.createUrl('/awOsc/guest-carts/:cartId/sections-details', {
                        cartId: quote.getQuoteId()
                    });
                } else {
                    serviceUrl = urlBuilder.createUrl('/awOsc/carts/mine/sections-details', {});
                }

                serviceBusyFlag(true);

                return storage.post(
                    serviceUrl,
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        setSectionsDetails(response);
                        cacheStorage.set(cacheKey, response);
                        serviceBusyFlag(false);
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response, messageContainer);
                    }
                );
            }
        };
    }
);
