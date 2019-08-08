/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'mage/utils/wrapper',
    'Aheadworks_OneStepCheckout/js/model/newsletter/assigner',
    'Aheadworks_OneStepCheckout/js/model/order-note/assigner',
    'Aheadworks_OneStepCheckout/js/model/delivery-date/assigner'
], function (
    $,
    wrapper,
    newsletterAssigner,
    orderNoteAssigner,
    deliveryDateAssigner
) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            newsletterAssigner(paymentData);
            orderNoteAssigner(paymentData);
            deliveryDateAssigner(paymentData);

            return originalAction(paymentData, messageContainer);
        });
    };
});
