/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [],
    function () {
        'use strict';

        /**
         * Estimation data
         *
         * @type {Object}
         */
        var estimationData = {
            shippingAddress: null,
            billingAddress: null
        };

        return {
            /**
             * Get estimation shipping address
             *
             * @returns {null|Object}
             */
            getShippingAddress: function () {
                return estimationData.shippingAddress;
            },

            /**
             * Set estimation shipping address
             *
             * @param {Object} shippingAddress
             */
            setShippingAddress: function (shippingAddress) {
                estimationData.shippingAddress = shippingAddress;
            },

            /**
             * Get estimation billing address
             *
             * @returns {null|Object}
             */
            getBillingAddress: function () {
                return estimationData.billingAddress;
            },

            /**
             * Set estimation billing address
             *
             * @param {Object} billingAddress
             */
            setBillingAddress: function (billingAddress) {
                estimationData.billingAddress = billingAddress;
            }
        };
    }
);
