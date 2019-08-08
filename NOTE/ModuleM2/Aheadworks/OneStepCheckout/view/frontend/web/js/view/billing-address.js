/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'ko',
        'Aheadworks_OneStepCheckout/js/view/address-abstract',
        'Aheadworks_OneStepCheckout/js/model/same-as-shipping-flag',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Aheadworks_OneStepCheckout/js/model/checkout-data',
        'Magento_Checkout/js/action/create-billing-address',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/action/set-billing-address',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/customer-data',
        'Magento_Customer/js/model/address-list',
        'Magento_Ui/js/model/messageList',
        'mage/translate',
        'Aheadworks_OneStepCheckout/js/model/checkout-data-completeness-logger'
    ],
    function (
        $,
        ko,
        Component,
        sameAsShippingFlag,
        quote,
        checkoutDataResolver,
        checkoutData,
        createBillingAddressAction,
        selectBillingAddressAction,
        setBillingAddressAction,
        customer,
        customerData,
        addressList,
        globalMessageList,
        $t,
        completenessLogger
    ) {
        'use strict';

        var lastSelectedBillingAddress = null,
            countryData = customerData.get('directory-data');

        return Component.extend({
            defaults: {
                scopeId: 'billingAddress',
                template: 'Aheadworks_OneStepCheckout/billing-address',
                availableForMethods: [],
                showAddressDetails: false,
                showAddressList: addressList().length > 0,
                showToolbar: false,
                selectedAddress: null,
                addressOptions: [],
                isAddressSpecified: false,
                editAddress: false,
                isCurrentAddressEditable: false,
                customerHasAddresses: false,
                errorValidationMessage: ''
            },
            canUseShippingAddress: ko.computed(function () {
                return !quote.isQuoteVirtual()
                    && quote.shippingAddress()
                    && quote.shippingAddress().canUseForBilling();
            }),
            isAddressSameAsShipping: sameAsShippingFlag.sameAsShipping,
            currentBillingAddress: ko.computed(function () {
                return !quote.isQuoteVirtual() && sameAsShippingFlag.sameAsShipping()
                    ? quote.shippingAddress()
                    : quote.billingAddress();
            }),
            newAddressOption: {
                getAddressInline: function () {
                    return $t('New Address');
                },
                customerAddressId: null
            },

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super();

                if (quote.paymentMethod()) {
                    this.isShown(this._isAvailableForMethod(quote.paymentMethod().method));
                }
                quote.paymentMethod.subscribe(function (method) {
                    var isShown = method
                        ? this._isAvailableForMethod(method.method)
                        : true;

                    this.isShown(isShown);
                }, this);
                this.isAddressSpecified.subscribe(function (flag) {
                    if (flag) {
                        this.errorValidationMessage('');
                    }
                }, this);
                this.customerHasAddresses = this.addressOptions.length > 1;
                completenessLogger.bindSelectedAddressData('billingAddress', quote.billingAddress);

                return this;
            },

            /**
             * @inheritdoc
             */
            _getCheckoutAddressFormData: function () {
                return checkoutData.getBillingAddressFromData();
            },

            /**
             * @inheritdoc
             */
            _setCheckoutAddressFormData: function (addressData) {
                this._super();
                checkoutData.setBillingAddressFromData(addressData);
            },

            /**
             * @inheritdoc
             */
            _resolveAddress: function () {
                checkoutDataResolver.resolveBillingAddress();
            },

            /**
             * @inheritdoc
             */
            initObservable: function () {
                var addressOptions;

                this._super();
                this.observe([
                    'showAddressDetails',
                    'showAddressList',
                    'selectedAddress',
                    'isAddressSpecified',
                    'editAddress',
                    'isCurrentAddressEditable',
                    'errorValidationMessage'
                ]);

                this.isAddressSpecified = ko.computed(function () {
                    if (!quote.isQuoteVirtual() && sameAsShippingFlag.sameAsShipping()) {
                        return quote.shippingAddress() != null;
                    } else {
                        return checkoutData.getSelectedBillingAddress() != null
                            || checkoutData.getNewCustomerBillingAddress() != null;
                    }
                }, this);

                this.showAddressDetails = ko.computed(function () {
                    return this.isAddressSpecified();
                }, this);
                this.showAddressList = ko.computed(function () {
                    return !this.isAddressSpecified() || this.editAddress();
                }, this);
                this.showForm = ko.computed(function () {
                    if (this.isFormInline) {
                        return !quote.isQuoteVirtual() && !sameAsShippingFlag.sameAsShipping()
                            || quote.isQuoteVirtual();
                    } else {
                        return this.showAddressList() && this.selectedAddress() == this.newAddressOption;
                    }
                }, this);
                this.showToolbar = ko.computed(function () {
                    return this.showAddressList() || this.showForm();
                }, this);
                this.isCurrentAddressEditable = ko.computed(function () {
                    return this.currentBillingAddress()
                        && !(!quote.isQuoteVirtual() && sameAsShippingFlag.sameAsShipping());
                }, this);

                addressOptions = addressList().filter(function (address) {
                    return address.getType() == 'customer-address';
                });
                addressOptions.push(this.newAddressOption);
                this.addressOptions = addressOptions;

                return this;
            },

            /**
             * Get country name
             *
             * @param {string} countryId
             * @return string
             */
            getCountryName: function (countryId) {
                return countryData()[countryId] != undefined ? countryData()[countryId].name : '';
            },

            /**
             * On use shipping address checkbox click event handler
             *
             * @returns {boolean}
             */
            onUseShippingAddress: function () {
                var addressData,
                    newBillingAddress;

                if (sameAsShippingFlag.sameAsShipping()) {
                    selectBillingAddressAction(quote.shippingAddress());
                    this.updateAddresses();
                    checkoutData.setSelectedBillingAddress(quote.shippingAddress().getKey());
                    checkoutData.setNewCustomerBillingAddress(null);
                } else {
                    lastSelectedBillingAddress = quote.billingAddress();
                    if (this.isFormInline) {
                        addressData = this.source.get('billingAddress');
                        newBillingAddress = createBillingAddressAction(addressData);
                        selectBillingAddressAction(newBillingAddress);
                        checkoutData.setSelectedBillingAddress(newBillingAddress.getKey());
                        checkoutData.setNewCustomerBillingAddress(addressData);
                    } else {
                        checkoutData.setSelectedBillingAddress(null);
                        checkoutData.setNewCustomerBillingAddress(null);
                    }
                }

                return true;
            },

            /**
             * Restore billing address
             */
            restoreBillingAddress: function () {
                if (lastSelectedBillingAddress != null) {
                    selectBillingAddressAction(lastSelectedBillingAddress);
                    checkoutData.setSelectedBillingAddress(lastSelectedBillingAddress.getKey());
                }
            },

            /**
             * Update shipping and billing addresses
             */
            updateAddresses: function () {
            },

            /**
             * Check if billing address is available for payment method
             *
             * @param {string} methodCode
             * @returns {boolean}
             */
            _isAvailableForMethod: function (methodCode) {
                return this.availableForMethods.indexOf(methodCode) != -1;
            },

            /**
             * Get address option text
             *
             * @param {Object} address
             * @return {string}
             */
            addressOptionsText: function (address) {
                return address.getAddressInline();
            },

            /**
             * On edit address click event handler
             */
            onEditAddressClick: function() {
                lastSelectedBillingAddress = quote.billingAddress();
                this.editAddress(true);
            },

            /**
             * On update address click event handler
             */
            onUpdateAddressClick: function () {
                if (this.selectedAddress() && this.selectedAddress() != this.newAddressOption) {
                    selectBillingAddressAction(this.selectedAddress());
                    checkoutData.setSelectedBillingAddress(this.selectedAddress().getKey());
                    this._restoreSameAsShippingFlag();
                    this.editAddress(false);
                    this.updateAddresses();
                } else {
                    this.source.set('params.invalid', false);
                    this.source.trigger(this.scopeId + '.data.validate');
                    if (this.source.get(this.scopeId + '.custom_attributes')) {
                        this.source.trigger(this.scopeId + '.custom_attributes.data.validate');
                    }

                    if (!this.source.get('params.invalid')) {
                        var addressData = this.source.get(this.scopeId),
                            newBillingAddress;

                        if (customer.isLoggedIn() && !this.customerHasAddresses) {
                            this.saveInAddressBook(1);
                        }
                        addressData['save_in_address_book'] = this.saveInAddressBook() ? 1 : 0;
                        newBillingAddress = createBillingAddressAction(addressData);

                        selectBillingAddressAction(newBillingAddress);
                        checkoutData.setSelectedBillingAddress(newBillingAddress.getKey());
                        checkoutData.setNewCustomerBillingAddress(addressData);

                        this.editAddress(false);
                        this.updateAddresses();
                    }
                }
            },

            /**
             * On cancel button click event handler
             */
            onCancelClick: function () {
                this._restoreSameAsShippingFlag();
                if (sameAsShippingFlag.sameAsShipping()) {
                    checkoutData.setSelectedBillingAddress(quote.shippingAddress().getKey());
                    checkoutData.setNewCustomerBillingAddress(null);
                }
                this.editAddress(false);
            },

            /**
             * Restore same as shipping flag
             */
            _restoreSameAsShippingFlag: function () {
                if (quote.shippingAddress()
                    && quote.shippingAddress().getKey() == quote.billingAddress().getKey()
                    && !quote.isQuoteVirtual()
                ) {
                    sameAsShippingFlag.sameAsShipping(true);
                }
            },

            /**
             * @inheritdoc
             */
            validate: function () {
                this._super();
                if (this.isShown() && !this.isFormInline && !this.isAddressSpecified()) {
                    this.source.set('params.invalid', true);
                    this.errorValidationMessage('Please specify a billing address.');
                }
            }
        });
    }
);
