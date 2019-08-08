/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'ko',
        'underscore',
        'Aheadworks_OneStepCheckout/js/model/completeness-logger/converter',
        'Aheadworks_OneStepCheckout/js/action/submit-completeness-log',
        'Aheadworks_OneStepCheckout/js/model/same-as-shipping-flag',
        'Magento_Checkout/js/model/quote'
    ],
    function (
        ko,
        _,
        converter,
        submitCompletenessLogAction,
        sameAsShippingFlag,
        quote
    ) {
        'use strict';

        var logData = {},
            isLogOfShippingAddressEnabled = ko.computed(function () {
                    return !quote.isQuoteVirtual();
                }),
            isLogOfBillingAddressEnabled = ko.computed(function () {
                return !sameAsShippingFlag.sameAsShipping() || quote.isQuoteVirtual();
            }),
            isStarted = false;

        var logger = {

            /**
             * Bind field
             *
             * @param {string} fieldName
             * @param {Function} observable
             */
            bindField: function (fieldName, observable) {
                var self = this;

                if (logData[fieldName] === undefined) {
                    logData[fieldName] = ko.computed(function () {
                        return self._isCompleted(observable());
                    });
                    logData[fieldName].subscribe(function () {
                        self.submitLog();
                    });
                    self.submitLog();
                }
            },

            /**
             * Bind address fields data
             *
             * @param {string} addressTypeKey
             * @param {Object} dataProvider
             */
            bindAddressFieldsData: function (addressTypeKey, dataProvider) {
                var self = this,
                    key = addressTypeKey + 'Data',
                    addressData = dataProvider.get(addressTypeKey);

                if (logData[key] === undefined) {
                    logData[key] = this._toAddressLogData(addressData);
                    // todo: consider update logData by completed status change but not address data change
                    dataProvider.on(addressTypeKey, function (newAddressData) {
                        logData[key] = self._toAddressLogData(newAddressData);
                        self.submitLog();
                    });
                    self.submitLog();
                }
            },

            /**
             * Bind selected address data
             *
             * @param {string} addressTypeKey
             * @param {Function} observable
             */
            bindSelectedAddressData: function (addressTypeKey, observable) {
                var self = this,
                    key = addressTypeKey + 'Selected';

                if (logData[key] === undefined) {
                    logData[key] = this._toAddressLogData(observable());
                    // todo: consider update logData by completed status change but not address data change
                    observable.subscribe(function (newAddressData) {
                        logData[key] = self._toAddressLogData(newAddressData);
                        self.submitLog();
                    });
                    self.submitLog();
                }
            },

            /**
             * Convert address data into log data
             *
             * @param {Object} addressData
             * @returns {Object}
             * @private
             */
            _toAddressLogData: function (addressData) {
                var addressLogData = {};

                _.each(addressData, function (value, field) {
                    if (_.isObject(value)) {
                        addressLogData[field] = {};
                        _.each(value, function (item, index) {
                            addressLogData[field][index] = this._isCompleted(item);
                        }, this);
                    } else {
                        addressLogData[field] = this._isCompleted(value);
                    }
                }, this);

                return addressLogData;
            },

            /**
             * Check if value completed
             *
             * @param value
             * @returns {boolean}
             */
            _isCompleted: function (value) {
                return value !== undefined && value != '' && value !== null;
            },

            /**
             * Submit log
             *
             * @returns {logger}
             */
            submitLog: function () {
                var buffer = {};

                if (isStarted) {
                    _.each(logData, function (data, key) {
                        var shippingAddressKeys = ['shippingAddressData', 'shippingAddressSelected'],
                            billingAddressKeys = ['billingAddressData', 'billingAddressSelected'];

                        if (!(_.indexOf(shippingAddressKeys, key) != -1 && !isLogOfShippingAddressEnabled()
                            || _.indexOf(billingAddressKeys, key) != -1 && !isLogOfBillingAddressEnabled())
                        ) {
                            buffer[key] = data;
                        }
                    });
                    submitCompletenessLogAction(
                        converter.convertToFieldCompletenessData(buffer)
                    );
                }

                return this;
            },

            /**
             * Start logging
             */
            start: function () {
                isStarted = true;
                this.submitLog();
            }
        };

        isLogOfShippingAddressEnabled.subscribe(function () {
            logger.submitLog();
        });
        isLogOfBillingAddressEnabled.subscribe(function () {
            logger.submitLog();
        });

        // todo: this is a workaround, should be revised
        //       possible approach is auto trigger when all expected bindings are performed
        window.setTimeout(function () {
            logger.start();
        }, 3000);

        return logger;
    }
);
