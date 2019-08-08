/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'uiRegistry',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/action/select-billing-address',
        'Aheadworks_OneStepCheckout/js/action/set-shipping-information',
        'Aheadworks_OneStepCheckout/js/model/same-as-shipping-flag',
        'Aheadworks_OneStepCheckout/js/model/checkout-section/service-enable-flag',
        'Aheadworks_OneStepCheckout/js/model/completeness-logger/service-enable-flag'
    ],
    function (
        $,
        registry,
        quote,
        selectShippingAddressAction,
        selectBillingAddressAction,
        setShippingInformationAction,
        sameAsShippingFlag,
        sectionsServiceEnableFlag,
        completenessLoggerServiceEnableFlag
    ) {
        'use strict';

        return {

            /**
             * Set all checkout data
             *
             * @returns {Deferred}
             */
            setCheckoutData: function () {
                var deferred = $.Deferred(),
                    self = this;

                this._setShippingInformation().done(
                    function () {
                        self._setPaymentInformation().done(function () {
                            deferred.resolve();
                        });
                    }
                );

                return deferred;
            },

            /**
             * Set shipping information
             *
             * @returns {Object}
             */
            _setShippingInformation: function () {
                var shippingAddressComponent = registry.get('checkout.shippingAddress'),
                    shippingAddress = quote.shippingAddress();

                if (!quote.isQuoteVirtual()) {
                    if (shippingAddressComponent.useFormData()) {
                        shippingAddress = shippingAddressComponent.copyFormDataToQuoteData(shippingAddress);
                    }

                    this._disableServices();
                    selectShippingAddressAction(shippingAddress);
                    this._enableServices();

                    return setShippingInformationAction();
                }
                return $.Deferred().resolve();
            },

            /**
             * Set payment information
             *
             * @returns {Object}
             */
            _setPaymentInformation: function () {
                var billingAddressComponent = registry.get('checkout.paymentMethod.billingAddress'),
                    billingAddress = quote.billingAddress()
                        ? quote.billingAddress()
                        : quote.shippingAddress();

                if (billingAddressComponent.useFormData()) {
                    billingAddress = billingAddressComponent.copyFormDataToQuoteData(billingAddress);
                } else if (!quote.isQuoteVirtual() && sameAsShippingFlag.sameAsShipping()) {
                    billingAddress = quote.shippingAddress()
                }

                this._disableServices();
                selectBillingAddressAction(billingAddress);
                this._enableServices();

                return $.Deferred().resolve();
            },

            /**
             * Disable services
             */
            _disableServices: function () {
                sectionsServiceEnableFlag(false);
                completenessLoggerServiceEnableFlag(false);
            },

            /**
             * Enable services
             */
            _enableServices: function () {
                sectionsServiceEnableFlag(true);
                completenessLoggerServiceEnableFlag(true);
            }
        };
    }
);
