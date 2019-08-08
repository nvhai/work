/*
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'iwd_bluepay',
                component: 'IWD_BluePay/js/view/payment/method-renderer/blue_pay'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);