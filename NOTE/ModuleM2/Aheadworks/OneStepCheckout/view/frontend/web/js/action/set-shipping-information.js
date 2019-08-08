/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'underscore',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/action/select-billing-address',
        'Aheadworks_OneStepCheckout/js/model/shipping-information/service-busy-flag',
        'Aheadworks_OneStepCheckout/js/model/same-as-shipping-flag'
    ],
    function (
        _,
        quote,
        resourceUrlManager,
        storage,
        errorProcessor,
        selectBillingAddressAction,
        serviceBusyFlag,
        sameAsShippingFlag
    ) {
        'use strict';
        return function () {
            var payload;

            if (!quote.billingAddress() || !quote.isQuoteVirtual() && sameAsShippingFlag.sameAsShipping()) {
                selectBillingAddressAction(quote.shippingAddress());
            }

            payload = {
                addressInformation: {
                    shipping_address: _.extend(
                        {},
                        quote.shippingAddress(),
                        {'same_as_billing': !quote.isQuoteVirtual() && sameAsShippingFlag.sameAsShipping() ? 1 : 0}
                    ),
                    billing_address: quote.billingAddress(),
                    shipping_method_code: quote.shippingMethod().method_code,
                    shipping_carrier_code: quote.shippingMethod().carrier_code
                }
            };

            serviceBusyFlag(true);

            return storage.post(
                resourceUrlManager.getUrlForSetShippingInformation(quote),
                JSON.stringify(payload)
            ).done(
                function () {
                    serviceBusyFlag(false);
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            );
        }
    }
);
