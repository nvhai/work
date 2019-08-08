/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'underscore',
    'uiCollection'
], function (_, Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'Aheadworks_OneStepCheckout/ui/grid/totals',
            columnsProvider: 'ns = ${ $.ns }, componentType = columns',
            imports: {
                addColumns: '${ $.columnsProvider }:elems',
                totals: '${ $.provider }:data.totals'
            }
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super()
                .track({
                    totals: []
                });

            return this;
        },

        /**
         * Adds columns whose visibility can be controlled to the component
         *
         * @param {Array} columns
         * @returns {Collection}
         */
        addColumns: function (columns) {
            columns = _.filter(columns, function (column) {
                return column.visibleInTopTotals && _.indexOf(column.topTotalsIndexes, this.index) != -1;
            }, this);

            this.insertChild(columns);

            return this;
        },

        /**
         * Get current top totals label
         *
         * @param {Object} col
         * @returns string
         */
        getTotalLabel: function(col) {
            if ('topTotalsLabel' in col) {
                return col.topTotalsLabel;
            } else {
                return col.label;
            }
        }
    });
});
