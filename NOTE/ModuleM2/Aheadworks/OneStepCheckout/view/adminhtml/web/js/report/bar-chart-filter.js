/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.awOscBarChartFilter', {
        options: {
            filterCheckBox: '[data-role=chart-filter-checkbox]',
            filterAttribute: 'data-filter-by'
        },

        /**
         * Initialize widget
         */
        _create: function () {
            this._bind();
        },

        /**
         * Event binding
         */
        _bind: function () {
            var handlers = {};

            handlers['change ' + this.options.filterCheckBox] = 'onFilterCheckboxChange';
            this._on(handlers);
        },

        /**
         * On filter checkbox click event handler
         *
         * @param {Event} event
         */
        onFilterCheckboxChange: function (event) {
            var element = $(event.currentTarget),
                filterBy = element.attr(this.options.filterAttribute),
                items = this.element
                    .find('[' + this.options.filterAttribute + '=' + filterBy + ']')
                    .not(this.options.filterCheckBox);

            if (element[0].checked) {
                items.hide();
            } else {
                items.show();
            }
        }
    });

    return $.mage.awOscBarChartFilter;
});
