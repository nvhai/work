/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

/**
 * Checkout data provider for estimation purposes
 */
define(
    [
        'Aheadworks_OneStepCheckout/js/model/checkout-data',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/model/new-customer-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/action/select-billing-address',
        'Aheadworks_OneStepCheckout/js/model/same-as-shipping-flag',
        'Aheadworks_OneStepCheckout/js/model/estimation-data',
        'Magento_Customer/js/model/address-list',
        'Aheadworks_OneStepCheckout/js/model/shipping-address/new-address-form-state'
    ],
    function (
        checkoutData,
        addressConverter,
        quote,
        checkoutDataResolver,
        newAddress,
        selectShippingAddressAction,
        selectBillingAddressAction,
        sameAsShippingFlag,
        estimationData,
        addressList,
        newAddressFormState
    ) {
        'use strict';

        return {
            /**
             * Resolve estimation shipping address
             */
            resolveShippingAddress: function () {
                var shippingAddress = null;

                if (addressList().length == 0) {
                    shippingAddress = this.resolveShippingAddressFormData();
                } else if (!newAddressFormState.isShown()) {
                    shippingAddress = quote.shippingAddress();
                }

                estimationData.setShippingAddress(shippingAddress);
                return shippingAddress;
            },

            /**
             * Resolve estimation billing address
             */
            resolveBillingAddress: function () {
                var billingAddress = addressList().length == 0
                    ? this.resolveBillingAddressFormData()
                    : (sameAsShippingFlag.sameAsShipping() && !quote.isQuoteVirtual()
                        ? quote.shippingAddress()
                        : quote.billingAddress()
                    );

                estimationData.setBillingAddress(billingAddress);
                return billingAddress;
            },

            /**
             * Apply shipping address form data to quote
             */
            resolveShippingAddressFormData: function () {
                var shippingAddress = quote.shippingAddress(),
                    addressData = addressConverter.formAddressDataToQuoteAddress(
                        checkoutData.getShippingAddressFromData()
                    );

                return this._copyFormDataToEstimationData(addressData, shippingAddress);
            },

            /**
             * Apply billing address form data to quote
             */
            resolveBillingAddressFormData: function () {
                var isSameAsShipping = sameAsShippingFlag.sameAsShipping(),
                    billingAddress = isSameAsShipping ? quote.shippingAddress() : quote.billingAddress(),
                    addressData = addressConverter.formAddressDataToQuoteAddress(
                        isSameAsShipping
                            ? checkoutData.getShippingAddressFromData()
                            : checkoutData.getBillingAddressFromData()
                    );

                return this._copyFormDataToEstimationData(addressData, billingAddress);
            },

            /**
             * Copy form data to estimation data
             *
             * @param {Object} formData
             * @param {Object} estimationData
             * @returns {Object}
             */
            _copyFormDataToEstimationData: function (formData, estimationData) {
                if (estimationData) {
                    for (var field in formData) {
                        if (formData.hasOwnProperty(field) &&
                            estimationData.hasOwnProperty(field) &&
                            typeof formData[field] != 'function' &&
                            _.isEqual(estimationData[field], formData[field])
                        ) {
                            estimationData[field] = formData[field];
                        } else if (typeof formData[field] != 'function' &&
                            !_.isEqual(estimationData[field], formData[field])) {
                            estimationData = formData;
                            break;
                        }
                    }
                }
                return estimationData;
            }
        };
    }
);
