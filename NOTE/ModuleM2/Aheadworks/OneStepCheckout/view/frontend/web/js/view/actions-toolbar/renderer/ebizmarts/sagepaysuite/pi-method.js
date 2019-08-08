/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'underscore',
    'mage/utils/wrapper',
    'Aheadworks_OneStepCheckout/js/view/actions-toolbar/renderer/default'
], function ($, _, wrapper, Component) {
    'use strict';

    var submitDropInPaymentBtnSelector = '#submit_dropin_payment';

    return Component.extend({
        defaults: {
            template: 'Aheadworks_OneStepCheckout/actions-toolbar/renderer/ebizmarts/sagepaysuite/pi-method',
            isLoadCardFromBtnVisible: true,
            isSubmitPaymentBtnVisible: false
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super().observe(['isLoadCardFromBtnVisible', 'isSubmitPaymentBtnVisible']);

            return this;
        },

        /**
         * @inheritdoc
         */
        initMethodsRenderComponent: function () {
            this._super();

            if (this.methodRendererComponent) {
                this.methodRendererComponent.awOscActionRenderer = this;
                this.initCallback(
                    'sagepayTokeniseCard',
                    this.methodRendererComponent,
                    function () {
                        var actionRenderer = this.awOscActionRenderer;

                        if (actionRenderer.isDropInEnabled()) {
                            actionRenderer.isLoadCardFromBtnVisible(false);
                            actionRenderer.isSubmitPaymentBtnVisible(true);
                        }
                    }
                );
            }

            return this;
        },

        /**
         * Init object method callback
         *
         * @param {string} target
         * @param {Object} context
         * @param {Function} handler
         */
        initCallback: function (target, context, handler) {
            var flag = 'is_' + target + 'Wrapped';

            if (!_.isBoolean(context[flag]) || !context[flag]) {
                context[target] = wrapper.wrap(context[target], function (orig) {
                    var args = _.toArray(arguments);

                    orig.apply(context, args.splice(1));
                    handler.call(context);
                });
                context[flag] = true;
            }
        },

        /**
         * Check if drop-in interface is enabled
         *
         * @returns {boolean}
         */
        isDropInEnabled: function () {
            return !!window.checkoutConfig.payment.ebizmarts_sagepaysuitepi.dropin;
        },

        /**
         * Prepare payment
         */
        preparePayment: function () {
            var self = this;

            this._beforeAction().done(function () {
                self._getMethodRenderComponent().preparePayment();
            });
        },

        /**
         * Submit payment
         */
        submitPayment: function () {
            $(submitDropInPaymentBtnSelector).trigger('click');
        }
    });
});
