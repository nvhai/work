/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'underscore',
        'Aheadworks_OneStepCheckout/js/model/render-postprocessor/float-label/converter/cc'
    ],
    function (_, defaultConverter) {
        'use strict';

        var converters = {};

        return {

            /**
             * Get converter
             *
             * @param {String} methodCode
             * @returns {Object}
             */
            getConverter: function (methodCode) {
                return _.has(converters, methodCode)
                    ? converters[methodCode]
                    : defaultConverter;
            },

            /**
             * Register converter
             *
             * @param {string} methodCode
             * @param {Object} converter
             */
            register: function (methodCode, converter) {
                converters[methodCode] = converter;
            }
        };
    }
);
