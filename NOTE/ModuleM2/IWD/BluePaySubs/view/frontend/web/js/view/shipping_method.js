/*
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'underscore',
        'uiComponent',
        'mage/translate',
        'jquery'
    ],
    function (_, Component, $t, $) {

        return Component.extend({
            defaults: {
                formId: 'subs_shipping_method',
                shippingRates: {},
                currentRate: ''
            },

            bindSubmit: function () {
                var self = this;

                $('#' + this.formId).on('submit', function (e) {
                    e.preventDefault();
                    self.submitForm($(this));
                });
            },

            /**
             * Handler for the form 'submit' event
             *
             * @param {Object} form
             */
            submitForm: function (form) {

                var self = this;

                form.submit();
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'currentRate'
                    ]);
                return this;
            },

            initialize: function () {
                var self = this;
                this._super();
                // this.bindSubmit();

                this.currentRate.subscribe(function (hash) {
                    parseInt(hash) === 0 ? self.currentRate('') : self.currentRate(hash);
                });

            },

            getCode: function () {
                return 'shipping';
            },

            getShippingRates: function () {
                return this.shippingRates;
            }
        });
    }
);