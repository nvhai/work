/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/payment/method-list',
        'Aheadworks_OneStepCheckout/js/model/checkout-section/service-busy-flag',
        'Aheadworks_OneStepCheckout/js/model/shipping-information/service-busy-flag'
    ],
    function (
        ko,
        quote,
        shippingService,
        paymentMethodList,
        sectionsServiceBusyFlag,
        shippingInfoServiceBusyFlag
    ) {
        'use strict';

        return ko.computed(function () {
            return paymentMethodList().length > 0
                && (!quote.isQuoteVirtual() && (shippingService.getShippingRates())().length > 0
                || quote.isQuoteVirtual())
                && !sectionsServiceBusyFlag()
                && !shippingInfoServiceBusyFlag();
        });
    }
);
