/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'underscore',
    'ko'
], function (_, ko) {
    'use strict';

    var quoteData = window.checkoutConfig.quoteData,
        isQuoteVirtual = ko.observable(!!Number(quoteData.is_virtual));

    return function (quote) {
        return _.extend(quote, {
            isQuoteVirtual: isQuoteVirtual,

            /**
             * Set quote data
             *
             * @param {Object} data
             */
            setQuoteData: function (data) {
                this.isQuoteVirtual(data.is_virtual);
            }
        });
    }
});
