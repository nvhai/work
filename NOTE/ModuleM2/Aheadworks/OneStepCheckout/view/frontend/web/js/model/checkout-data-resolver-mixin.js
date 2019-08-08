/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'underscore',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/action/select-payment-method',
    'Aheadworks_OneStepCheckout/js/model/same-as-shipping-flag'
], function (
    _,
    checkoutData,
    addressList,
    addressConverter,
    quote,
    selectShippingAddressAction,
    selectBillingAddressAction,
    selectShippingMethodAction,
    paymentService,
    selectPaymentMethodAction,
    sameAsShippingFlag
) {
    'use strict';

    return function (checkoutDataResolver) {
        return _.extend(checkoutDataResolver, {

            /**
             * @inheritdoc
             */
            applyShippingAddress: function (isEstimatedAddress) {
                var address,
                    shippingAddress,
                    isConvertAddress,
                    addressData,
                    isShippingAddressInitialized;

                if (addressList().length == 0) {
                    address = addressConverter.formAddressDataToQuoteAddress(
                        checkoutData.getShippingAddressFromData()
                    );
                    selectShippingAddressAction(address);
                }
                shippingAddress = quote.shippingAddress();
                isConvertAddress = isEstimatedAddress || false;

                if (!shippingAddress) {
                    isShippingAddressInitialized = addressList.some(function (addressFromList) {
                        if (checkoutData.getSelectedShippingAddress() == addressFromList.getKey()) {
                            addressData = isConvertAddress ?
                                addressConverter.addressToEstimationAddress(addressFromList)
                                : addressFromList;
                            selectShippingAddressAction(addressData);

                            return true;
                        }

                        return false;
                    });

                    if (!isShippingAddressInitialized) {
                        isShippingAddressInitialized = addressList.some(function (address) {
                            if (address.isDefaultShipping()) {
                                addressData = isConvertAddress ?
                                    addressConverter.addressToEstimationAddress(address)
                                    : address;
                                selectShippingAddressAction(addressData);

                                return true;
                            }

                            return false;
                        });
                    }

                    if (!isShippingAddressInitialized && addressList().length == 1) {
                        addressData = isConvertAddress ?
                            addressConverter.addressToEstimationAddress(addressList()[0])
                            : addressList()[0];
                        selectShippingAddressAction(addressData);
                    }
                }
                if (sameAsShippingFlag.sameAsShipping()
                    && shippingAddress
                    && shippingAddress.canUseForBilling()
                    && (shippingAddress.isDefaultShipping() || !quote.isQuoteVirtual())
                ) {
                    selectBillingAddressAction(quote.shippingAddress());
                }
            },

            /**
             * @inheritdoc
             */
            resolveShippingRates: function (ratesData) {
                var selectedShippingRate = checkoutData.getSelectedShippingRate(),
                    availableRate = false;

                if (ratesData.length == 1) {
                    //set shipping rate if we have only one available shipping rate
                    selectShippingMethodAction(ratesData[0]);

                    return;
                }

                if (quote.shippingMethod()) {
                    availableRate = _.find(ratesData, function (rate) {
                        return rate.carrier_code == quote.shippingMethod().carrier_code &&
                            rate.method_code == quote.shippingMethod().method_code;
                    });
                }

                if (!availableRate && selectedShippingRate) {
                    availableRate = _.find(ratesData, function (rate) {
                        return rate.carrier_code + '_' + rate.method_code === selectedShippingRate;
                    });
                }

                if (!availableRate && window.checkoutConfig.selectedShippingMethod) {
                    availableRate = window.checkoutConfig.selectedShippingMethod;
                }

                if (!availableRate && window.checkoutConfig.defaultShippingMethod) {
                    availableRate = _.find(ratesData, function (rate) {
                        return rate.carrier_code == window.checkoutConfig.defaultShippingMethod.carrier_code &&
                            rate.method_code == window.checkoutConfig.defaultShippingMethod.method_code;
                    });
                }

                if (!availableRate) {
                    selectShippingMethodAction(null);
                } else {
                    selectShippingMethodAction(availableRate);
                }
            },

            /**
             * @inheritdoc
             */
            resolvePaymentMethod: function () {
                var availablePaymentMethods = paymentService.getAvailablePaymentMethods(),
                    selectedPaymentMethod = checkoutData.getSelectedPaymentMethod();

                if (!selectedPaymentMethod && window.checkoutConfig.defaultPaymentMethod) {
                    selectedPaymentMethod = window.checkoutConfig.defaultPaymentMethod;
                }

                if (selectedPaymentMethod) {
                    availablePaymentMethods.some(function (payment) {
                        if (payment.method == selectedPaymentMethod) {
                            selectPaymentMethodAction(payment);
                        }
                    });
                }
            }
        });
    }
});
