/*
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
define(
    [
        'ko',
        'jquery',
        'IWD_BluePay/js/view/payment/method-renderer/blue_pay',
        'iwdOpcHelper'
    ],
    function (ko, $, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'IWD_BluePay/onepage/payment/methods-renderers/blue_pay',
                isCurrentlySecure: window.checkoutConfig.iwdOpcSettings.isCurrentlySecure
            },
            decorateSelect: function (uid, option) {
                var select = $('#' + uid);
                if (select.length) {
                    if(option && select[0].selectize) {
                        select[0].selectize.addOption({value: option.value, text: option.text });
                    }
                    else {
                        select.decorateSelect();
                    }
                }
            }
        });
    }
);