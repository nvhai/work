/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'ko',
        'uiComponent',
        'Aheadworks_OneStepCheckout/js/model/newsletter/subscriber'
    ],
    function (ko, Component, subscriber) {
        'use strict';

        var newsletterSubscribeConfig = window.checkoutConfig.newsletterSubscribe;

        return Component.extend({
            defaults: {
                template: 'Aheadworks_OneStepCheckout/newsletter-subscriber'
            },
            isSubscribe: subscriber.subscribe,
            isEnabled: ko.computed(function () {
                return newsletterSubscribeConfig.isEnabled
                    ? subscriber.isAvailableForSubscribe()
                    : false;
            })
        });
    }
);
