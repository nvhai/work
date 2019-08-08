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
        'jquery',
        'jquery/ui'
    ],
    function (_, Component, $t, $) {

        return Component.extend({
            defaults: {
                formId: 'subs_billing_address',
                addresses: {},
                selectedAddressId: ''
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
                        'selectedAddressId'
                    ]);
                return this;
            },

            initialize: function () {
                var self = this,
                    addressSelector = '.new_billing_address';
                this._super();
                if(this.selectedAddressId()) {
                    $(addressSelector).hide();
                }
                else {
                    $(addressSelector).show();
                }

                this.selectedAddressId.subscribe(function (id) {
                    if(!id) {
                        $(addressSelector).show();
                        self.selectedAddressId('')
                    } else {
                        $(addressSelector).hide();
                        self.selectedAddressId(id);
                    }
                });
            },

            getCode: function () {
                return 'billing';
            },

            getAddresses: function () {
                return this.addresses;
            }
        });
    }
);