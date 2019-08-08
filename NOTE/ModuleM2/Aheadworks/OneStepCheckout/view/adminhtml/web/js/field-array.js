/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'underscore',
    'mage/template'
], function ($, _, mageTemplate) {
    'use strict';

    $.widget('mage.awOscFieldArray', {
        dataIndex: 0,
        options: {
            template: '[data-role=row-template]',
            rowsContainer: '[data-role=rows-container]',
            row: '[data-role=row]',
            addButton: '[data-role=add-button]',
            addAfterButton: '[data-role=add-after-button]',
            deleteButton: '[data-role=delete-button]',
            defaultRowData: {},
            rows: [],
            maxRows: -1
        },

        /**
         * Initialize widget
         */
        _create: function() {
            var self = this;

            this.template = mageTemplate(this.options.template);
            _.each(this.options.rows, function (row) {
                self._addRow(row);
            });
            this._bind();
        },

        /**
         * @inheritdoc
         */
        _init: function () {
            this._initAddButton();
        },

        /**
         * Event binding
         */
        _bind: function () {
            var handlers = {};

            handlers['click ' + this.options.addButton] = 'onAddBtnClick';
            handlers['click ' + this.options.addAfterButton] = 'onAddAfterBtnClick';
            handlers['click ' + this.options.deleteButton] = 'onDeleteBtnClick';

            this._on(handlers);
        },

        /**
         * Init add button
         */
        _initAddButton: function () {
            var btn;

            if (this._ifMaxRowsAdded()) {
                btn = this.element.find(this.options.addButton);
                btn.attr('disabled', 'disabled');
            }
        },

        /**
         * On add button click handler
         */
        onAddBtnClick: function (event) {
            var btn;

            if (!this._ifMaxRowsAdded()) {
                this._addRow(
                    _.extend({}, this.options.defaultRowData, {'_id': this._generateId()})
                );
                if (this._ifMaxRowsAdded()) {
                    btn = $(event.currentTarget);
                    btn.attr('disabled', 'disabled');
                }
            }
        },

        /**
         * On add after button click handler
         */
        onAddAfterBtnClick: function () {
            // todo
        },

        /**
         * On delete button click handler
         *
         * @param {Object} event
         */
        onDeleteBtnClick: function (event) {
            var rowId = $(event.currentTarget).data('row-id'),
                addBtn;

            this.element.find(this.options.row)
                .filter('#' + rowId)
                .remove();
            if (!this._ifMaxRowsAdded()) {
                addBtn = this.element.find(this.options.addButton);
                addBtn.removeAttr('disabled');
            }
        },

        /**
         * Add row
         *
         * @param {Object} data
         */
        _addRow: function (data) {
            var row = $(this.template(data));

            row.appendTo(this.element.find(this.options.rowsContainer));
            row.trigger('contentUpdated');
        },

        /**
         * Generate string identifier
         *
         * @returns {string}
         */
        _generateId: function () {
            var date = new Date();

            return '_' + date.getTime() + '_' + date.getMilliseconds();
        },

        /**
         * Checks if max number of rows added
         *
         * @returns {boolean}
         */
        _ifMaxRowsAdded: function () {
            var rows;

            if (this.options.maxRows > -1) {
                rows = this.element.find(this.options.row);
                return rows.length >= this.options.maxRows;
            }

            return false;
        }
    });

    return $.mage.awOscFieldArray;
});
