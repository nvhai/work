/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_SalesRule/js/action/set-coupon-code',
        'Magento_SalesRule/js/action/cancel-coupon',
        'Magento_SalesRule/js/model/payment/discount-messages',
        'Aheadworks_OneStepCheckout/js/model/payment-option/coupon',
        'Aheadworks_OneStepCheckout/js/model/payment-option/message-processor'
    ],
    function (
        $,
        ko,
        Component,
        quote,
        setCouponCodeAction,
        cancelCouponAction,
        messageContainer,
        coupon,
        messageProcessor
    ) {
        'use strict';

        var totals = quote.getTotals();

        if (totals()) {
            coupon.code(totals()['coupon_code']);
        }

        return Component.extend({
            defaults: {
                template: 'Aheadworks_OneStepCheckout/sidebar/payment-option/discount',
                formSelector: '#discount-form',
                inputSelector: '#discount-code'
            },
            couponCode: coupon.code,
            isApplied: coupon.isApplied,

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super();
                coupon.isApplied(this.couponCode() != null);
            },

            /**
             * On apply button click
             */
            onApplyClick: function() {
                if (this.isValid()) {
                    this._processMessages(setCouponCodeAction(coupon.code(), this.isApplied));
                }
            },

            /**
             * On cancel button click
             */
            onCancelClick: function() {
                if (this.isValid()) {
                    coupon.code('');
                    this._processMessages(cancelCouponAction(this.isApplied));
                }
            },

            /**
             * Add process messages handlers
             *
             * @param {Deferred} deferred
             */
            _processMessages: function (deferred) {
                var input = $(this.inputSelector);

                deferred.done(function () {
                    messageProcessor.processSuccess(input, messageContainer)
                }).fail(function () {
                    messageProcessor.processError(input, messageContainer)
                });
            },

            /**
             * Check if coupon valid
             *
             * @returns {Boolean}
             */
            isValid: function () {
                var form = $(this.formSelector);

                messageProcessor.resetImmediate($(this.inputSelector));

                return form.validation() && form.validation('isValid');
            }
        });
    }
);
