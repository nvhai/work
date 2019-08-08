/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'uiComponent',
        'Aheadworks_OneStepCheckout/js/model/checkout-data',
        'Aheadworks_OneStepCheckout/js/model/order-note/order-note'
    ],
    function (Component, checkoutData, orderNote) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_OneStepCheckout/sidebar/order-note',
                inputValue: ''
            },
            isVisible: window.checkoutConfig.isOrderNoteEnabled,

            /**
             * @inheritdoc
             */
            initialize: function () {
                var orderNote = checkoutData.getOrderNote();

                this._super();

                if (orderNote) {
                    this.inputValue(orderNote);
                }
                this.inputValue.subscribe(function (newValue) {
                    checkoutData.setOrderNote(newValue);
                }, this);
            },

            /**
             * @inheritdoc
             */
            initObservable: function () {
                this._super();
                this.inputValue = orderNote;

                return this;
            }
        });
    }
);
