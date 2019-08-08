/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.awOscValidationMock', {
        options: {
            errorClassName: 'mage-error',
            successClassName: 'mage-success',
            messageElement: 'div'
        },

        /**
         * Initialize widget
         */
        _create: function () {
            this._bind();
        },

        /**
         * @inheritdoc
         */
        _init: function () {
            if (!this.element.attr('id')) {
                this.element.attr('id', this._generateId());
            }
        },

        /**
         * Event binding
         */
        _bind: function () {
            this._on({
                'awOscVMError': 'onError',
                'awOscVMSuccess': 'onSuccess',
                'awOscVMReset': 'onReset'
            });
        },

        /**
         * Generate unique Id
         *
         * @returns {string}
         */
        _generateId: function () {
            return '_' + Math.random().toString(36).substr(2, 9);
        },

        /**
         * On error event handler
         *
         * @param {Event} event
         * @param {Array} messages
         */
        onError: function (event, messages) {
            var self = this;

            this._removeMessageLabels();
            $.each(messages, function (index) {
                self._addMessageLabel(this, self.options.errorClassName, index);
            });
            this._highlightError();
        },

        /**
         * On success event handler
         *
         * @param {Event} event
         * @param {Array} messages
         */
        onSuccess: function (event, messages) {
            var self = this;

            this._removeMessageLabels();
            $.each(messages, function (index) {
                self._addMessageLabel(this, self.options.successClassName, index);
            });
            this._highlightSuccess();
        },

        /**
         * On reset event handler
         *
         * @param {Event} event
         * @param {boolean} force
         */
        onReset: function (event, force) {
            this._unHighlight();
            if (force) {
                this._removeMessageLabels();
            } else {
                this._hideAndRemoveMessageLabels();
            }
        },

        /**
         * Add message label
         *
         * @param {string} message
         * @param {string} className
         * @param {int} index
         */
        _addMessageLabel: function (message, className, index) {
            var elementId = this.element.attr('id'),
                label = $('<' + this.options.messageElement + '/>')
                    .attr({
                        id: elementId + '_message-label-' + index,
                        for: elementId,
                        generated: true
                    })
                    .addClass(className)
                    .html(message || '');

            this.element.after(label);
        },

        /**
         * Get message labels selector
         *
         * @returns {string}
         */
        _getMessageLabelsSelector: function () {
            return '[id|=' + this.element.attr('id') + '_message-label]';
        },

        /**
         * Remove message labels
         */
        _removeMessageLabels: function () {
            var labels = this.element.siblings(this._getMessageLabelsSelector());

            labels.remove();
        },

        /**
         * Hide and remove message labels
         */
        _hideAndRemoveMessageLabels: function () {
            var labels = this.element.siblings(this._getMessageLabelsSelector());

            labels.hide('blind', {}, 500, function () {
                $(this).remove();
            });
        },

        /**
         * Highlight error
         */
        _highlightError: function () {
            this.element.removeClass(this.options.successClassName)
                .addClass(this.options.errorClassName);
        },

        /**
         * Highlight success
         */
        _highlightSuccess: function () {
            this.element.removeClass(this.options.errorClassName)
                .addClass(this.options.successClassName);
        },

        /**
         * Unhighlight
         */
        _unHighlight: function () {
            this.element.removeClass(this.options.errorClassName)
                .removeClass(this.options.errorClassName);
        }
    });

    return $.mage.awOscValidationMock;
});
