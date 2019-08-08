/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.awOscDraggableFieldArray', {
        options: {
            rowsContainer: '[data-role=row-container]',
            orderInput: '[data-role=sort-order]'
        },

        /**
         * Initialize widget
         */
        _create: function() {
            var self = this,
                rowsContainer = this.element.find(this.options.rowsContainer);

            rowsContainer.sortable({
                distance: 8,
                tolerance: 'pointer',
                axis: 'y',
                update: function () {
                    rowsContainer.find(self.options.orderInput).each(function (index, element) {
                        $(element).val(index);
                    });
                }
            });
        }
    });

    return $.mage.awOscDraggableFieldArray;
});
