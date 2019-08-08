define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Amasty_Rewards/js/action/add-reward',
    'Amasty_Rewards/js/action/cancel-reward'
], function ($, Component, quote, setRewardPointAction, cancelRewardPointAction) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_Rewards/checkout/payment/rewards',
            isApplied: false,
            pointsUsed: 0,
            pointsLeft: 0,
            noticeMessage: '',
            minimumPointsValue: 0,
            disableElem: false
        },

        initObservable: function () {
            this._super();
            this.observe(['pointsUsed', 'pointsLeft', 'isApplied', 'noticeMessage', 'disableElem']);

            return this;
        },

        /**
         * @return {exports}
         */
        initialize: function() {
            this._super();
            this.isApplied(false);

            if (this.pointsUsed() > 0) {
                this.isApplied(true);
            }

            if (this.getMinimumPointsValue() > this.pointsLeft() + Number.parseFloat(this.pointsUsed())) {
                this.disableElem(true);
            }

            return this;
        },

        /**
         * @return {*|Boolean}
         */
        isDisplayed: function () {
            return this.customerId;
        },

        /**
         * Coupon code application procedure
         */
        apply: function () {
            if (this.validate()) {
                setRewardPointAction(this.pointsUsed, this.isApplied, this.pointsLeft, this.rateForCurrency, this.noticeMessage);
            }
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
            cancelRewardPointAction(this.isApplied);
            this.pointsLeft((Number.parseFloat(this.pointsLeft()) + Number.parseFloat(this.pointsUsed())).toFixed(2));
        },

        /**
         *
         * @return {*}
         */
        getRewardsCount: function () {
            return this.pointsLeft();
        },

        /**
         *
         * @return {*}
         */
        getPointsRate: function () {
            return this.pointsRate;
        },

        /**
         *
         * @return {*}
         */
        getCurrentCurrency: function () {
            return this.currentCurrencyCode;
        },

        /**
         *
         * @return {*}
         */
        getRateForCurrency: function () {
            return this.rateForCurrency;
        },

        /**
         * @return {*}
         */
        getMinimumPointsValue: function () {
            return parseFloat(this.minimumPointsValue);
        },

        /**
         * @return {Boolean}
         */
        canApply: function () {
            return !(this.disableElem() || this.isApplied());
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            var form = '#discount-reward-form',
                valueValid = (this.pointsLeft() - this.pointsUsed() >= 0) && this.pointsUsed() > 0;

            return $(form).validation() && $(form).validation('isValid') && valueValid;
        }
    });
});
