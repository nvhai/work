/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'Aheadworks_OneStepCheckout/js/model/newsletter/subscriber'
], function (subscriber) {
    'use strict';

    var newsletterSubscribeConfig = window.checkoutConfig.newsletterSubscribe;

    return function (paymentData) {
        if (newsletterSubscribeConfig.isEnabled && subscriber.isAvailableForSubscribe()) {
            if (paymentData['extension_attributes'] === undefined) {
                paymentData['extension_attributes'] = {};
            }
            paymentData['extension_attributes']['is_subscribe_for_newsletter'] = subscriber.subscribe();
            paymentData['extension_attributes']['subscriber_email'] = subscriber.subscriberEmail;
        }
    };
});
