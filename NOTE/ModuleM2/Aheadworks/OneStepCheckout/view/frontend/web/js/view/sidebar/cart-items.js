/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'ko',
        'underscore',
        'uiComponent',
        'Magento_Checkout/js/model/totals'
    ],
    function (ko, _, Component, totals) {
        'use strict';

        /**
         * Initial sorted items
         */
        var initialItems = (totals.getItems())();

        /**
         * Sort cart items according to initial positions
         *
         * @param {Array} items
         */
        function sortItems(items) {
            var sortedItems = [],
                diff = [];

            _.each(items, function (item) {
                var founded = _.find(initialItems, function (initItem) {
                    return item.item_id == initItem.item_id;
                });

                if (founded === undefined) {
                    diff.push(item);
                }
            });
            _.each(initialItems, function (initialItem) {
                var candidate = _.find(items, function (newItem) {
                    return initialItem.item_id == newItem.item_id;
                });

                if (candidate !== undefined) {
                    sortedItems.push(candidate);
                } else if (diff.length > 0) {
                    candidate = diff.pop();
                    sortedItems.push(candidate);
                }
            });
            initialItems = sortedItems;

            return sortedItems;
        }

        return Component.extend({
            defaults: {
                template: 'Aheadworks_OneStepCheckout/sidebar/cart-items',
                items: sortItems(initialItems),
                itemsQty: parseFloat(totals.totals().items_qty)
            },

            /**
             * @inheritdoc
             */
            initialize: function () {
                var self = this;

                this._super();
                totals.getItems().subscribe(function (newItems) {
                    self.items(sortItems(newItems));
                });
                totals.totals.subscribe(function (newTotals) {
                    self.itemsQty(parseFloat(newTotals.items_qty));
                });
            },

            /**
             * @inheritdoc
             */
            initObservable: function () {
                this._super();
                this.observe(['items','itemsQty']);

                return this;
            }
        });
    }
);
