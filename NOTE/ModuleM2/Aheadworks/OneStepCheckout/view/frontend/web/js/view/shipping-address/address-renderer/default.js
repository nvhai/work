/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/customer-data',
    'Aheadworks_OneStepCheckout/js/model/shipping-address/new-address-form-state',
    'Aheadworks_OneStepCheckout/js/model/checkout-data-completeness-logger'
], function (
    $,
    ko,
    Component,
    selectShippingAddressAction,
    quote,
    checkoutData,
    customerData,
    newAddressFormState,
    completenessLogger
) {
    'use strict';

    var countryData = customerData.get('directory-data');

    return Component.extend({
        defaults: {
            template: 'Aheadworks_OneStepCheckout/shipping-address/address-renderer/default'
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            completenessLogger.bindSelectedAddressData('shippingAddress', quote.shippingAddress);
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super();

            this.isSelected = ko.computed(function() {
                var isSelected = false,
                    shippingAddress = quote.shippingAddress();

                if (shippingAddress) {
                    isSelected = shippingAddress.getKey() == this.address().getKey();
                }

                return isSelected;
            }, this);

            return this;
        },

        /**
         * Get country name
         *
         * @param {string} countryId
         * @returns {string}
         */
        getCountryName: function(countryId) {
            return (countryData()[countryId] != undefined) ? countryData()[countryId].name : '';
        },

        /**
         * On select address click event handler
         */
        onSelectAddressClick: function() {
            selectShippingAddressAction(this.address());
            checkoutData.setSelectedShippingAddress(this.address().getKey());
        },

        /**
         * On edit address click event handler
         */
        onEditAddressClick: function() {
            newAddressFormState.isShown(true);
        }
    });
});
