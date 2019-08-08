/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    $.widget('mage.awOscAttributeMetaEdit', {
        options: {
            textInputs: 'input[type=text]',
            checkboxes: 'input[type=checkbox]',
            metaFieldAttribute: 'data-meta-field-name',
            fieldLinkage: {
                label: [
                    {visible: 'disabled'},
                    {required: 'data-required'}
                ]
            }
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
            var self = this,
                handlers = {};

            handlers['change ' + this.options.textInputs] = 'onTextInputChange';
            handlers['change ' + this.options.checkboxes] = 'onCheckboxChange';
            _.each(this.options.fieldLinkage, function (config, metaField) {
                _.each(config, function (configItem) {
                    _.each(configItem, function (prop, masterMetaField) {
                        var eventDeclare = 'change [' + self.options.metaFieldAttribute + '=' + masterMetaField + ']';

                        handlers[eventDeclare] = function (event) {
                            self.handleLinkage(
                                $(event.currentTarget),
                                self.element.find('[' + self.options.metaFieldAttribute + '=' + metaField + ']'),
                                prop
                            );
                        };
                    });
                });
            });

            this._on(handlers);
        },

        /**
         * On text input value change event handler
         *
         * @param {Object} event
         */
        onTextInputChange: function (event) {
            var element = $(event.currentTarget);

            this._updateValue(element.attr('id'), element.val());
        },

        /**
         * On checkbox checked state change event handler
         *
         * @param {Object} event
         */
        onCheckboxChange: function (event) {
            var element = $(event.currentTarget);

            this._updateValue(
                element.attr('id'),
                element[0].checked ? '1' : '0'
            );
        },

        /**
         * Handle linkage between inputs
         *
         * @param {Object} master
         * @param {Object} slave
         * @param {string} prop
         */
        handleLinkage: function (master, slave, prop) {
            var value;

            if (master.is(this.options.checkboxes)) {
                value = master[0].checked;
                if (prop == 'disabled') {
                    if (!value) {
                        slave.prop(prop, 'disabled');
                    } else {
                        slave.removeProp(prop);
                    }
                } else if (prop == 'data-required') {
                    slave.attr(prop, value ? '1': '0');
                }
            }
        },

        /**
         * Update value of hidden related field
         *
         * @param {string} id
         * @param {string} value
         */
        _updateValue: function (id, value) {
            var hidden = this.element.find('[data-value-input-id=' + id + ']');

            hidden.val(value);
        }
    });

    return $.mage.awOscAttributeMetaEdit;
});
