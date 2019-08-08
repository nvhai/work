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

    var paymentMethodNoteSelector = '#payment_form_sagepaysuiteserver .payment-method-note';

    return Component.extend({
        defaults: {
            template: 'Aheadworks_OneStepCheckout/actions-toolbar/renderer/ebizmarts/sagepaysuite/server-method'
        },

        /**
         * @inheritdoc
         */
        initMethodsRenderComponent: function () {
            this._super();

            if (this.methodRendererComponent) {
                this.initMethodReplacement(
                    'showPaymentError',
                    this.methodRendererComponent,
                    function (message) {
                        var messageContainer = $('#' + this.getCode() + '-payment-errors'),
                            note = $(paymentMethodNoteSelector);

                        messageContainer.html(message)
                            .show();
                        note.show();
                    }
                ).initMethodReplacement(
                    'resetPaymentErrors',
                    this.methodRendererComponent,
                    function () {
                        var messageContainer = $('#' + this.getCode() + '-payment-errors'),
                            note = $(paymentMethodNoteSelector);

                        messageContainer.hide();
                        note.show();
                    }
                );
            }

            return this;
        },

        /**
         * Init object method replacement
         *
         * @param {string} target
         * @param {Object} context
         * @param {Function} replacement
         * @returns {Component}
         */
        initMethodReplacement: function (target, context, replacement) {
            var flag = 'is_' + target + 'Wrapped';

            if (!_.isBoolean(context[flag]) || !context[flag]) {
                context[target] = wrapper.wrap(context[target], function () {
                    var args = _.toArray(arguments);

                    replacement.apply(context, args.splice(1));
                });
                context[flag] = true;
            }

            return this;
        },

        /**
         * Prepare payment
         */
        preparePayment: function () {
            var self = this;

            this._beforeAction().done(function () {
                self._getMethodRenderComponent().preparePayment();
            });
        }
    });
});
