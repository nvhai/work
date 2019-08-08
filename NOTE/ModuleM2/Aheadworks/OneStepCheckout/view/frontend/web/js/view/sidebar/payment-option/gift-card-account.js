/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'Magento_GiftCardAccount/js/view/payment/gift-card-account',
    'Magento_GiftCardAccount/js/action/set-gift-card-information',
    'Magento_GiftCardAccount/js/action/get-gift-card-information',
    'Magento_GiftCardAccount/js/model/payment/gift-card-messages',
    'Aheadworks_OneStepCheckout/js/model/payment-option/message-processor'
], function (
    $,
    Component,
    setGiftCardInformationAction,
    getGiftCardInformationAction,
    messageContainer,
    messageProcessor
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Aheadworks_OneStepCheckout/sidebar/payment-option/gift-card-account',
            inputSelector: '#giftcard-code'
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super()
                ._initMessagesProcessing();
        },

        /**
         * Init messages processing
         *
         * @returns {Component}
         */
        _initMessagesProcessing: function () {
            var self = this;

            messageContainer.getErrorMessages().subscribe(function () {
                messageProcessor.processError($(self.inputSelector), messageContainer)
            });
            messageContainer.getSuccessMessages().subscribe(function () {
                messageProcessor.processSuccess($(self.inputSelector), messageContainer)
            });

            return this;
        },

        /**
         * @inheritdoc
         */
        setGiftCard: function () {
            if (this.validate()) {
                setGiftCardInformationAction([this.giftCartCode()]);
            }
        },

        /**
         * @inheritdoc
         */
        checkBalance: function () {
            if (this.validate()) {
                getGiftCardInformationAction.check(this.giftCartCode());
            }
        },

        /**
         * @inheritdoc
         */
        validate: function() {
            messageProcessor.resetImmediate($(this.inputSelector));

            return this._super();
        }
    });
});
