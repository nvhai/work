/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'ko',
    'underscore',
    'Magento_Ui/js/form/element/select',
    'Aheadworks_OneStepCheckout/js/action/update-quote-item-options'
], function (ko, _, Component, updateQuoteItemOptionsAction) {
    'use strict';

    return Component.extend({
        defaults: {
            itemId: null,
            links: {
                selectedProducts: '${ $.parentName }:selectedProducts'
            },
            listens: {
                selectedProducts: 'filterBySelectedProducts',
                outerValue: 'updateQuoteItemOptions'
            },
            selectedProducts: [],
            lastSelectedProducts: [],
            outerValue: '',
            _unlinkValue: false
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super().observe(['outerValue']);
            this.value.subscribe(function (newValue) {
                if (!this._unlinkValue) {
                    this.outerValue(newValue);
                }
            }, this);

            return this;
        },

        /**
         * Filter options by selected products
         *
         * @param {Array} products
         */
        filterBySelectedProducts: function (products) {
            var attrProducts = _.filter(products, function (item, attrCode) {
                    return attrCode != this.index;
                }, this),
                attrCode = this._getAttrCodeChanged(products, this.lastSelectedProducts),
                productsMerged = [],
                options;

            this.lastSelectedProducts = products;

            if (attrCode !== undefined && attrCode != this.index) {
                _.each(attrProducts, function (item) {
                    if (productsMerged.length > 0) {
                        productsMerged = _.intersection(productsMerged, item);
                    } else {
                        productsMerged = item;
                    }
                });

                if (productsMerged.length > 0) {
                    options = _.filter(this.initialOptions, function (item) {
                        return _.intersection(productsMerged, item.products).length > 0;
                    });
                    this.setSafeOptions(options);
                }
            }
        },

        /**
         * Set options without losing value
         *
         * @param {Object} data
         * @returns {Component}
         */
        setSafeOptions: function (data) {
            var currentValue = this.value();

            this._unlinkValue = true;
            this.setOptions(data);
            this.value(currentValue);
            this._unlinkValue = false;

            return this;
        },

        /**
         * Perform update quote item options
         */
        updateQuoteItemOptions: function () {
            var options = {};

            options[this.index] = this.outerValue();
            updateQuoteItemOptionsAction(this.itemId, options);
        },

        /**
         * Get changed attribute code
         *
         * @param {Array} products
         * @param {Array} lastProducts
         * @returns {string|undefined}
         */
        _getAttrCodeChanged: function (products, lastProducts) {
            var changed = [];

            _.each(products, function (item, attrCode) {
                if (!(attrCode in lastProducts)) {
                    changed.push(attrCode);
                } else if (_.difference(item, lastProducts[attrCode]).length > 0) {
                    changed.push(attrCode);
                }
            });

            return changed.pop();
        }
    });
});
