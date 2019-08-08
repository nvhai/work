/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'Magento_Catalog/js/price-utils',
    'Magento_Ui/js/grid/columns/column'
], function (priceUtils, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            imports: {
                priceFormat: '${ $.provider }:data.priceFormat'
            }
        },

        /**
         * @inheritdoc
         */
        getLabel: function (row) {
            var price = this._super(row);

            return priceUtils.formatPrice(price, this.priceFormat);
        }
    });
});
