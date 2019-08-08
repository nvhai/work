/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'underscore',
    'mage/utils/wrapper'
], function ($, _, wrapper) {
    'use strict';

    var agreementsConfig = window.checkoutConfig.checkoutAgreements;

    return function (agreementsAssigner) {

        return wrapper.wrap(agreementsAssigner, function (originalAssigner, paymentData) {
            var agreementForm = '[data-role=checkout-agreements-form]',
                agreementIds = [];

            if (agreementsConfig.isEnabled) {
                _.each($(agreementForm).serializeArray(), function (item) {
                    agreementIds.push(item.value);
                });
                if (paymentData['extension_attributes'] === undefined) {
                    paymentData['extension_attributes'] = {};
                }
                paymentData['extension_attributes']['agreement_ids'] = agreementIds;
            }
        });
    };
});
