/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'underscore',
    'Aheadworks_OneStepCheckout/js/view/actions-toolbar/renderer/default',
    'Magento_Checkout/js/model/quote'
], function ($, _, Component, quote) {
    'use strict';

    var placeOrderBtnSelectorTemplate = '#adyen_oneclick_<%- value %> button.action.primary';

    return Component.extend({

        /**
         * @inheritdoc
         */
        placeOrder: function (data, event) {
            var self = this,
                paymentMethod,
                btnSelectorTemplate;

            if (event) {
                event.preventDefault();
            }
            this._beforeAction().done(function () {
                paymentMethod = quote.paymentMethod();
                if (self._getMethodRenderComponent() && _.has(paymentMethod, 'additional_data')) {
                    btnSelectorTemplate = _.template(placeOrderBtnSelectorTemplate);
                    $(btnSelectorTemplate({
                        value: paymentMethod.additional_data.recurring_detail_reference}
                    )).trigger('click');
                }
            });
        }
    });
});
