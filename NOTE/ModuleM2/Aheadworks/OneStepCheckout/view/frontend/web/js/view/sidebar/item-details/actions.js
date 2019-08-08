/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'uiComponent',
        'Aheadworks_OneStepCheckout/js/action/remove-quote-item'
    ],
    function (Component, removeQuoteItemAction) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_OneStepCheckout/sidebar/item-details/actions'
            },

            /**
             * On remove item click
             *
             * @param {Object} item
             */
            onRemoveClick: function (item) {
                removeQuoteItemAction(item.item_id);
            }
        });
    }
);
