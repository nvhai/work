/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'underscore',
        'ko',
        'Magento_Ui/js/form/form',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'uiRegistry',
        'Aheadworks_OneStepCheckout/js/model/payment-methods-service',
        'Aheadworks_OneStepCheckout/js/model/checkout-data-completeness-logger'
    ],
    function (
        $,
        _,
        ko,
        Component,
        addressList,
        addressConverter,
        registry,
        paymentMethodsService,
        completenessLogger
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                saveInAddressBook: true,
                showForm: true,
                isShown: true,
                scopeId: '',
                canValidate: false,
                paymentDeps: []
            },
            isFormInline: addressList().length == 0,

            /**
             * @inheritdoc
             */
            initialize: function () {
                var self = this;

                this._super();

                this._resolveAddress();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var addressData = self._getCheckoutAddressFormData();

                    if (addressData) {
                        checkoutProvider.set(
                            self.scopeId,
                            $.extend(
                                {},
                                checkoutProvider.get(self.scopeId),
                                _.omit(addressData, function (value) {
                                    if (_.isString(value) && value == '') {
                                        return true;
                                    }
                                    return false;
                                })
                            )
                        );
                    }
                    self._afterSetInitialAddressFormData();
                    checkoutProvider.on(self.scopeId, function (addressData) {
                        self._setCheckoutAddressFormData(addressData);
                    });
                    completenessLogger.bindAddressFieldsData(self.scopeId, checkoutProvider);
                });
                this.saveInAddressBook.subscribe(function (flag) {
                    var addressData = this._getCheckoutAddressFormData();

                    if (addressData) {
                        addressData.save_in_address_book = (flag ? 1 : 0);
                        this._setCheckoutAddressFormData(addressData);
                    }
                }, this);
                _.each(this.paymentDeps, function (depPath) {
                    paymentMethodsService.bindAddressFields(depPath);
                });
            },

            /**
             * Retrieve address form data from checkout data storage
             *
             * @returns {Object}
             */
            _getCheckoutAddressFormData: function () {
                return {};
            },

            /**
             * Set address form data to checkout data storage
             *
             * @param {Object} addressData
             */
            _setCheckoutAddressFormData: function (addressData) {
            },

            /**
             * Resolve address
             */
            _resolveAddress: function () {
            },

            /**
             * Called after set initial address form data from checkout data storage
             */
            _afterSetInitialAddressFormData: function () {
            },

            /**
             * @inheritdoc
             */
            initObservable: function () {
                var addressData = this._getCheckoutAddressFormData();

                this._super();

                if (addressData && typeof addressData.save_in_address_book !== undefined) {
                    this.saveInAddressBook = addressData.save_in_address_book ? true : false;
                }
                this.observe(['saveInAddressBook', 'showForm', 'isShown']);

                return this;
            },

            /**
             * @inheritdoc
             */
            validate: function () {
                this.source.set('params.invalid', false);
                if (this.useFormData()) {
                    this.source.trigger(this.scopeId + '.data.validate');

                    if (this.source.get(this.scopeId + '.custom_attributes')) {
                        this.source.trigger(this.scopeId + '.custom_attributes.data.validate');
                    }
                }
            },

            /**
             * Check if use form data
             *
             * @returns {boolean}
             */
            useFormData: function () {
                return this.isShown() && this.showForm() && this.isFormInline;
            },

            /**
             * Copy form data to quote address object
             *
             * @param {Object} quoteAddress
             * @returns {Object}
             */
            copyFormDataToQuoteData: function (quoteAddress) {
                var addressData = addressConverter.formAddressDataToQuoteAddress(
                    this.source.get(this.scopeId)
                );

                for (var field in addressData) {
                    if (addressData.hasOwnProperty(field) &&
                        quoteAddress.hasOwnProperty(field) &&
                        typeof addressData[field] != 'function' &&
                        _.isEqual(quoteAddress[field], addressData[field])
                    ) {
                        quoteAddress[field] = addressData[field];
                    } else if (typeof addressData[field] != 'function' &&
                        !_.isEqual(quoteAddress[field], addressData[field])) {
                        quoteAddress = addressData;
                        break;
                    }
                }

                if (quoteAddress.saveInAddressBook === undefined) {
                    quoteAddress.saveInAddressBook = this.saveInAddressBook() ? 1 : 0;
                }

                return quoteAddress;
            }
        });
    }
);
