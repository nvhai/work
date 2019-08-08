/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'Aheadworks_OneStepCheckout/js/model/delivery-date/delivery-date',
    'Magento_Checkout/js/model/quote'
], function (deliveryDate, quote) {
    'use strict';

    var deliveryDateConfig = window.checkoutConfig.deliveryDate;

    return function (paymentData) {
        if (deliveryDateConfig.isEnabled && !quote.isQuoteVirtual()) {
            if (paymentData['extension_attributes'] === undefined) {
                paymentData['extension_attributes'] = {};
            }
            paymentData['extension_attributes']['delivery_date'] = deliveryDate.date();
            paymentData['extension_attributes']['delivery_time_slot'] = deliveryDate.timeSlot();
        }
    };
});
