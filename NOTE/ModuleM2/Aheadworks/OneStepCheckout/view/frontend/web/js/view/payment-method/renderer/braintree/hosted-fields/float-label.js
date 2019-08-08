/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'awOscFloatLabel'
], function ($) {
    'use strict';

    $.widget('mage.awOscBraintreeHostedFloatLabel', $.mage.awOscFloatLabel, {

        /**
         * Trigger field event
         *
         * @param {Object} field
         * @param {Boolean} isFocused
         * @param {Boolean} isEmpty
         */
        triggerFieldEvent: function (field, isFocused, isEmpty) {
            if (!isEmpty || isFocused) {
                this._setInLabelState(field);
            } else {
                this._setInPlaceholderState(field);
            }
        }
    });

    return $.mage.awOscBraintreeHostedFloatLabel;
});
