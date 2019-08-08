/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'ko',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, Component, quote) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_OneStepCheckout/totals/discount'
            },
            totals: quote.getTotals(),

            /**
             * Check if total displayed
             *
             * @returns {boolean}
             */
            isDisplayed: function() {
                return this.getPureValue() != 0;
            },

            /**
             * Get coupon code
             *
             * @returns {string|null}
             */
            getCouponCode: function() {
                if (!this.totals()) {
                    return null;
                }
                return this.totals()['coupon_code'];
            },

            /**
             * Get pure total value
             *
             * @returns {Number}
             */
            getPureValue: function() {
                return this.totals() && this.totals().discount_amount
                    ? parseFloat(this.totals().discount_amount)
                    : 0;
            },

            /**
             * Get formatted total value
             *
             * @returns {string}
             */
            getValue: function() {
                return this.getFormattedPrice(this.getPureValue());
            }
        });
    }
);
