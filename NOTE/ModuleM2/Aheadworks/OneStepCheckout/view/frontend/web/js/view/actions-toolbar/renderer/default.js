/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'uiComponent',
    'uiRegistry',
    'Magento_Checkout/js/model/full-screen-loader',
    'Aheadworks_OneStepCheckout/js/model/place-order-allowed-flag',
    'Aheadworks_OneStepCheckout/js/view/place-order/aggregate-validator',
    'Aheadworks_OneStepCheckout/js/view/place-order/aggregate-checkout-data'
], function (
    $,
    Component,
    registry,
    fullScreenLoader,
    placeOrderAllowedFlag,
    aggregateValidator,
    aggregateCheckoutData
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Aheadworks_OneStepCheckout/actions-toolbar/renderer/default',
            methodCode: null
        },
        methodRendererComponent: null,
        isPlaceOrderActionAllowed: placeOrderAllowedFlag,

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super().initMethodsRenderComponent();

            return this;
        },

        /**
         * Perform before actions: overall validation, set checkout data and etc.
         *
         * @returns {Deferred}
         */
        _beforeAction: function () {
            var deferred = $.Deferred();

            if (this.isPlaceOrderActionAllowed()) {
                aggregateValidator.validate().done(function () {
                    fullScreenLoader.startLoader();
                    aggregateCheckoutData.setCheckoutData().done(function () {
                        fullScreenLoader.stopLoader();
                        deferred.resolve();
                    });
                });
            }

            return deferred;
        },

        /**
         * Init method renderer component
         *
         * @returns {Component}
         */
        initMethodsRenderComponent: function () {
            if (this.methodCode) {
                this.methodRendererComponent = registry.get('checkout.paymentMethod.methodList.' + this.methodCode);
            }

            return this;
        },

        /**
         * Get method renderer component
         *
         * @returns {Component}
         */
        _getMethodRenderComponent: function () {
            if (!this.methodRendererComponent) {
                this.initMethodsRenderComponent();
            }
            return this.methodRendererComponent;
        },

        /**
         * Place order
         *
         * @param {Object} data
         * @param {Object} event
         */
        placeOrder: function (data, event) {
            var self = this;

            if (event) {
                event.preventDefault();
            }
            this._beforeAction().done(function () {
                self._getMethodRenderComponent().placeOrder(data, event);
            });
        },

        /**
         * Dispose subscriptions
         */
        disposeSubscriptions: function () {
        }
    });
});
