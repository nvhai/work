/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'uiComponent',
        'underscore',
        'Aheadworks_OneStepCheckout/js/action/update-quote-item',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, _, updateQuoteItemAction, totals) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_OneStepCheckout/sidebar/item-details/qty'
            },
            qtyOriginal: null,
            isEditing: false,

            /**
             * On increment quantity event handler
             *
             * @param {Object} item
             */
            onIncrementQtyClick: function (item) {
                var qtyOriginal = item.qty;

                item.qty++;
                this._performUpdateQtyAction(item, qtyOriginal);
            },

            /**
             * On decrement quantity click event handler
             *
             * @param {Object} item
             */
            onDecrementQtyClick: function (item) {
                var qty = item.qty,
                    qtyOriginal = qty;

                qty--;
                if (qty > 0) {
                    item.qty = qty;
                    this._performUpdateQtyAction(item, qtyOriginal);
                }
            },

            /**
             * On quantity input focusin event handler
             *
             * @param {Object} item
             */
            onQtyFocusIn: function (item) {
                this.qtyOriginal = item.qty;
                this.isEditing = true;
            },

            /**
             * On quantity input focusout event handler
             *
             * @param {Object} item
             */
            onQtyFocusOut: function (item) {
                var qty = item.qty,
                    self = this;

                if (this._isManuallyUpdateAllowed(qty)) {
                    self._performUpdateQtyAction(item, this.qtyOriginal);
                } else if (!this._isValidQty(qty)) {
                    self._restore(item, this.originalQty);
                }
                this.isEditing = false;
            },

            /**
             * Check if manually quantity update is allowed
             *
             * @param {string} qty
             * @returns {boolean}
             */
            _isManuallyUpdateAllowed: function (qty) {
                var isChanged = (this.qtyOriginal != qty);

                return this.isEditing && isChanged && this._isValidQty(qty);
            },

            /**
             * Check if quantity is valid
             *
             * @param {string} qty
             * @returns {boolean}
             */
            _isValidQty: function (qty) {
                return (qty.length > 0)
                    && (qty - 0 == qty)
                    && (qty - 0 > 0);
            },

            /**
             * Perform update quantity action
             *
             * @param {Object} item
             * @param {Number} originalQty
             */
            _performUpdateQtyAction: function (item, originalQty) {
                var self = this;

                updateQuoteItemAction(item).fail(function () {
                    self._restore(item, originalQty);
                });
            },

            /**
             * Restore original quantity value
             *
             * @param {Object} item
             * @param {Number} originalQty
             */
            _restore: function (item, originalQty) {
                var items = totals.getItems(),
                    itemsDataUpdate = [];

                _.each(items(), function (itemData) {
                    var dataToUpdate = _.clone(itemData);

                    if (dataToUpdate.item_id == item.item_id) {
                        dataToUpdate.qty = originalQty;
                    }
                    itemsDataUpdate.push(dataToUpdate);
                });
                items(itemsDataUpdate);
            }
        });
    }
);
