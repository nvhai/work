/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'underscore',
        'uiRegistry',
        'Magento_Checkout/js/model/quote',
        'Aheadworks_OneStepCheckout/js/model/payment-validation-invoker'
    ],
    function (
        $,
        _,
        registry,
        quote,
        paymentValidationInvoker
    ) {
        'use strict';

        return {

            /**
             * Perform overall checkout data validation
             *
             * @returns {Deferred}
             */
            validate: function () {
                var deferred = $.Deferred(),
                    isValid = true;

                if (!this._validateAddresses()) {
                    isValid = false;
                }
                if (!this._validateShippingMethod()) {
                    isValid = false;
                }
                if (!this._validateDeliveryDateFormData()) {
                    isValid = false;
                }

                this._validatePaymentMethod().done(function () {
                    if (isValid) {
                        deferred.resolve();
                    }
                });

                return deferred;
            },

            /**
             * Validate addresses data
             *
             * @returns {Boolean}
             */
            _validateAddresses: function () {
                var isValid = true,
                    provider = registry.get('checkoutProvider');

                _.each(['checkout.shippingAddress', 'checkout.paymentMethod.billingAddress'], function (query) {
                    var addressComponent = registry.get(query);

                    addressComponent.validate();
                    if (provider.get('params.invalid')) {
                        isValid = false;
                    }
                }, this);

                return isValid;
            },

            /**
             * Validate shipping method
             *
             * @returns {boolean}
             */
            _validateShippingMethod: function () {
                var shippingMethodComponent = registry.get('checkout.shippingMethod'),
                    provider = registry.get('checkoutProvider');

                shippingMethodComponent.validate();

                return !provider.get('params.invalid');
            },

            /**
             * Validate delivery date form data
             *
             * @returns {boolean}
             */
            _validateDeliveryDateFormData: function () {
                var deliveryDateComponent = registry.get('checkout.shippingMethod.delivery-date'),
                    provider = registry.get('checkoutProvider');

                deliveryDateComponent.validate();

                return !provider.get('params.invalid');
            },

            /**
             * Validate payment method
             *
             * @returns {Deferred}
             */
            _validatePaymentMethod: function () {
                var methodListComponent = registry.get('checkout.paymentMethod.methodList'),
                    methodCode,
                    methodRenderer;

                if (quote.paymentMethod()) {
                    methodCode = quote.paymentMethod().method;
                    methodRenderer = methodListComponent.getChild(methodCode);

                    return paymentValidationInvoker.invokeValidate(methodRenderer, methodCode);
                } else {
                    return methodListComponent.validate()
                        ? $.Deferred().resolve()
                        : $.Deferred().reject();
                }
            }
        };
    }
);
