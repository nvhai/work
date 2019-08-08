/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

/**
 * Can be instantiated for:
 * 1. Form field element;
 * 2. Container of form field elements.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.awOscFloatLabel', {
        options: {
            fieldSelector: 'div.field',
            excludeFieldSelector: '[data-exclude-fl-init]',
            inputsSelectors: ['input', 'select'],
            flClass: 'fl-label',
            flLabelStateClass: 'fl-label-state',
            flPlaceholderStateClass: 'fl-placeholder-state'
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
            var self = this,
                fields = this._getFields();

            fields.addClass(this.options.flClass);
            fields.each(function () {
                self._getInputs(this).each(function () {
                    self._applyState($(this));
                });
            });
        },

        /**
         * Event binding
         */
        _bind: function () {
            var handlers = {},
                isMobile = navigator.userAgent.match(/Android|webOS|iPhone|iPad|iPod|BlackBerry|Windows Phone/i);

            handlers = this._addEventHandler(handlers, 'change', 'eventHandler');
            handlers = this._addEventHandler(handlers, 'focus', 'eventHandler');
            handlers = this._addEventHandler(handlers, 'blur', 'eventHandler');
            handlers = this._addEventHandler(handlers, 'awOscForceRefresh', 'eventHandler');

            if (isMobile) {
                handlers = this._addEventHandler(handlers, 'mouseup', 'eventHandler');
                handlers = this._addEventHandler(handlers, 'mousedown', 'eventHandler');
            }

            this._on(handlers);
        },

        /**
         * Add event handler
         *
         * @param {Object.<string, string>} handlers
         * @param {string} eventName
         * @param {string} callback
         * @returns {Object.<string, string>}
         */
        _addEventHandler: function (handlers, eventName, callback) {
            var self = this;

            $.each(self.options.inputsSelectors, function () {
                var event = self._isField()
                    ? eventName + ' ' + this
                    : eventName + ' ' + self._getChildFieldsSelector() + ' ' + this;

                handlers[event] = callback;
            });

            return handlers;
        },

        /**
         * Field input event handler
         *
         * @param {Object} event
         */
        eventHandler: function (event) {
            this._applyState($(event.currentTarget));
        },

        /**
         * Get field element
         *
         * @returns {Object}
         */
        _getFields: function () {
            return this._isField()
                ? this.element
                : this.element.find(this._getChildFieldsSelector());
        },

        /**
         * Get input elements
         *
         * @param {Object} field
         * @returns {Object}
         */
        _getInputs: function (field) {
            return $(field).find(this.options.inputsSelectors.join(', '));
        },

        /**
         * Get child fields selector
         *
         * @returns {string}
         */
        _getChildFieldsSelector: function () {
            return this.options.fieldSelector + ':not(' + this.options.excludeFieldSelector + ')';
        },

        /**
         * Check if element is field
         *
         * @returns {boolean}
         */
        _isField: function () {
            return this.element.is(this.options.fieldSelector);
        },

        /**
         * Apply float label state
         *
         * @param {Object} element
         */
        _applyState: function (element) {
            var isLabel,
                field = element.closest(this.options.fieldSelector),
                optionText;

            if (element.is('select')) {
                optionText = element.find('option:selected').text();
                isLabel = typeof optionText != 'undefined' && optionText.trim() != '';
            } else {
                isLabel = element.is(':focus') || element.val() != '';
            }

            if (isLabel) {
                this._setInLabelState(field);
            } else {
                this._setInPlaceholderState(field);
            }
        },

        /**
         * Set field in placeholder state
         *
         * @param {Object} field
         */
        _setInPlaceholderState: function (field) {
            field.removeClass(this.options.flLabelStateClass);
            field.addClass(this.options.flPlaceholderStateClass);
        },

        /**
         * Set field in label state
         *
         * @param {Object} field
         */
        _setInLabelState: function (field) {
            field.removeClass(this.options.flPlaceholderStateClass);
            field.addClass(this.options.flLabelStateClass);
        }
    });

    return $.mage.awOscFloatLabel;
});
