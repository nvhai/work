/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.awOscLinkedSelect', {
        options: {
            select: 'select[data-role=linked-select]',
            showOptionValueAttr: 'data-show',
            hideOptionValueAttr: 'data-hide',
            enableOptionValueAttr: 'data-enable',
            disableOptionValueAttr: 'data-disable',
            container: '[data-role=container]'
        },

        /**
         * Initialize widget
         */
        _create: function() {
            this._bind();
        },

        /**
         * Event binding
         */
        _bind: function () {
            var event = this._isContainer()
                ? 'change ' + this.options.select
                : 'change',
                handlers = {};

            handlers[event] = 'onChange';
            this._on(handlers);
        },

        /**
         * @inheritdoc
         */
        _init: function () {
            var element = this._isContainer()
                ? this.element.find(this.options.select)
                : this.element;

            this._adjustVisibility(element.val());
        },

        /**
         * On change event handler
         *
         * @param {Object} event
         */
        onChange: function (event) {
            this._adjustVisibility($(event.currentTarget).val());
        },

        /**
         * Adjust visibility of linked blocks
         *
         * @param {string} value
         */
        _adjustVisibility: function (value) {
            var container = this._isContainer()
                    ? this.element
                    : $(document),
                elementsToShow = container.find('[' + this.options.showOptionValueAttr + '*=' + value + ']'),
                elementsToHide = container.find('[' + this.options.hideOptionValueAttr + '*=' + value + ']'),
                elementsToEnable = container.find('[' + this.options.enableOptionValueAttr + '*=' + value + ']'),
                elementsToDisable = container.find('[' + this.options.disableOptionValueAttr + '*=' + value + ']');

            elementsToShow.show();
            elementsToHide.hide();
            elementsToEnable.prop('disabled', false);
            elementsToDisable.prop('disabled', true);
        },

        /**
         * Check if element is container
         *
         * @returns {boolean}
         */
        _isContainer: function () {
            return this.element.is(':not(' + this.options.select + ')');
        }
    });

    return $.mage.awOscLinkedSelect;
});
