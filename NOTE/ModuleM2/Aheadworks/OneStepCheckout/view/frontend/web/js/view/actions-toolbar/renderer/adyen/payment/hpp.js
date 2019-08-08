/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Aheadworks_OneStepCheckout/js/view/actions-toolbar/renderer/default',
    'Magento_Checkout/js/model/full-screen-loader',
    'Aheadworks_OneStepCheckout/js/view/place-order/aggregate-validator',
    'Aheadworks_OneStepCheckout/js/view/place-order/aggregate-checkout-data',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function (
    $,
    _,
    registry,
    Component,
    fullScreenLoader,
    aggregateValidator,
    aggregateCheckoutData,
    quote,
    $t
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Aheadworks_OneStepCheckout/actions-toolbar/renderer/adyen/payment/hpp'
        },

        /**
         * Redirect to Adyen
         */
        continueToAdyen: function () {
            var self = this;

            this._beforeAction().done(function () {
                self._getMethodRenderComponent().continueToAdyen();
            });
        },

        /**
         * Redirect to Adyen brand
         */
        continueToAdyenBrand: function () {
            var self = this,
                methodRenderer,
                context;

            this._beforeAction().done(function () {
                methodRenderer = self._getMethodRenderComponent();
                if (methodRenderer) {
                    context = self._getPaymentMethodContext();
                    if (context) {
                        methodRenderer.continueToAdyenBrandCode.apply(context);
                    }
                }
            });
        },

        /**
         * Get selected payment method context
         *
         * @returns {Object|undefined}
         */
        _getPaymentMethodContext: function () {
            var paymentMethods = this._getMethodRenderComponent().getAdyenHppPaymentMethods(),
                paymentMethod = quote.paymentMethod();

            return _.find(paymentMethods, function (method) {
                return _.has(paymentMethod, 'additional_data')
                    && method.value == paymentMethod.additional_data.brand_code;
            });
        },

        /**
         * @inheritdoc
         */
        _beforeAction: function () {
            var self = this,
                deferred = $.Deferred(),
                methodListComponent;

            if (this.isPlaceOrderActionAllowed()) {
                aggregateValidator.validate().done(function () {
                    if (!self.isMethodSelectionOnAdyenSide()
                        && !self._getMethodRenderComponent().isBrandCodeChecked()
                    ) {
                        methodListComponent = registry.get('checkout.paymentMethod.methodList');
                        methodListComponent.errorValidationMessage($t('Please specify a payment method.'));
                        deferred.reject();
                    } else {
                        fullScreenLoader.startLoader();
                        aggregateCheckoutData.setCheckoutData().done(function () {
                            fullScreenLoader.stopLoader();
                            deferred.resolve();
                        });
                    }
                });
            }

            return deferred;
        },

        /**
         * Check if payment method selection performed on Adyen side
         *
         * @returns {boolean}
         */
        isMethodSelectionOnAdyenSide: function () {
            return this._getMethodRenderComponent().isPaymentMethodSelectionOnAdyen();
        }
    });
});
