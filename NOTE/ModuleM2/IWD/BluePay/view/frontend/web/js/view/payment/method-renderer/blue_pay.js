/*
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
define([
    'IWD_BluePay/js/view/payment/payment-form',
    'jquery',
    'Magento_Checkout/js/action/set-payment-information',
    'Magento_Payment/js/model/credit-card-validation/validator'
], function (Component, $, setPaymentInformationAction) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'IWD_BluePay/payment/payment-form'
        },

        getCode: function () {
            return 'iwd_bluepay';
        },

        isActive: function () {
            return true;
        },

        validate: function () {
            var $form = $('#' + this.getCode() + '-form');
            return $form.validation() && $form.validation('isValid');
        }
    });
});