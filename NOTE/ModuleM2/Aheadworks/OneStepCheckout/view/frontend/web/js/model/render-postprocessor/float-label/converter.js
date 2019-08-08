/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [],
    function () {
        'use strict';

        return {
            labelTmplStr: '<label for="<%- data.for %>" class="label"><span><%- data.label %></span></label>',
            shortLabelTmplStr: '<label for="<%- data.for %>" class="label short">'
                + '<span><%- data.shortLabel %></span></label>',

            /**
             * Convert payment method form inputs to inputs with float label
             *
             * @param {Object} methodItem
             */
            convertPaymentMethodInputs: function (methodItem) {
            }
        };
    }
);
