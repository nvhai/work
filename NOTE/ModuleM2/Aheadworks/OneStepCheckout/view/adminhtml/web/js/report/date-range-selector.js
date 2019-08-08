/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'mage/validation',
    'timeframe'
], function($) {
    'use strict';

    $.widget('mage.awOscDateRangeSelector', {
        options: {
            earliest: '',
            latest: '',
            weekOffset: 0,
            calendarsHeaderId: 'aw-report-calendars_header',
            calendarsContainerId: 'aw-report-calendars-container',
            calendarsId: 'aw-report-calendars',
            fromDateId: 'aw-report-period_date_from',
            toDateId: 'aw-report-period_date_to',
            calendarForm: '[data-role=calendar-form]',
            customDateRangeSelector: '[data-role=custom-date-range-selector]',
            applyButton: '[data-role=apply-button]',
            cancelButton: '[data-role=cancel-button]',
            rangeToDatesMap: {}
        },
        datePicker: null,

        /**
         * Initialize widget
         */
        _create: function () {
            this._initDatePicker();
            this._bind();
        },

        /**
         * Init date picker
         */
        _initDatePicker: function () {
            this.datePicker = new Timeframe(this.options.calendarsId, {
                startField: this.options.fromDateId,
                endField: this.options.toDateId,
                resetButton: 'reset',
                header: this.options.calendarsHeaderId,
                form: this.options.calendarsContainerId,
                earliest: this.options.earliest,
                latest: this.options.latest,
                weekOffset: this.options.weekOffset,
                calendarSelectionCallback: this.onCalendarRangeSelect.bind(this)
            });
            this.datePicker.parseField('start', true);
            this.datePicker.parseField('end', true);
            this.datePicker.parseField('compareStart', false);
            this.datePicker.parseField('compareEnd', false);
            this.datePicker.clearCompareRange();
            this.datePicker.selectstart = true;
            this.datePicker.populate().refreshRange();
        },

        /**
         * Event binding
         */
        _bind: function () {
            var handlers = {};

            handlers['input #' + this.options.fromDateId] = 'onFromDateChanged';
            handlers['input #' + this.options.toDateId] = 'onToDateChanged';
            handlers['change ' + this.options.customDateRangeSelector] = 'onDateRangeChanged';
            handlers['click ' + this.options.applyButton] = 'onApplyBtnClick';
            handlers['click ' + this.options.cancelButton] = 'onCancelBtnClick';

            this._on(handlers);
        },

        /**
         * On calendar date range select
         */
        onCalendarRangeSelect: function () {
            this._clearValidationErrors();
            this._setCustomRange();
        },

        /**
         * On from date value change handler
         */
        onFromDateChanged: function () {
            this._setCustomRange();
        },

        /**
         * On to date value change handler
         */
        onToDateChanged: function () {
            this._setCustomRange();
        },

        /**
         * On date range change handler
         *
         * @param {Event} event
         */
        onDateRangeChanged: function (event) {
            var element = $(event.currentTarget),
                fromDate = $('#' + this.options.fromDateId),
                toDate = $('#' + this.options.toDateId),
                rangeValue = element.val(),
                fromDateValue = fromDate.val(),
                toDateValue = toDate.val();

            this._clearValidationErrors();
            if (this.options.rangeToDatesMap[rangeValue] !== undefined) {
                fromDateValue = this.options.rangeToDatesMap[rangeValue].from;
                toDateValue = this.options.rangeToDatesMap[rangeValue].to;

                fromDate.val(fromDateValue);
                toDate.val(toDateValue);
            }
            this.datePicker.range.set('start', new Date.parseToObject(fromDateValue));
            this.datePicker.range.set('end', new Date.parseToObject(toDateValue));
            this.datePicker.parseField('start', false);
            this.datePicker.parseField('end', false);
            $(this.options.calendarsHeaderId).html(fromDateValue + ' - ' + toDateValue);
        },

        /**
         * On apply button click
         */
        onApplyBtnClick: function () {
            var form = this.element.find(this.options.calendarForm),
                range = $(this.options.customDateRangeSelector).val(),
                fromDate,
                toDate,
                params = document.location.search.replace('?', '').toQueryParams();

            if (form.validation() && form.validation('isValid')) {
                fromDate = new Date($('#' + this.options.fromDateId).val());
                toDate = new Date($('#' + this.options.toDateId).val());
                params.date_range = range;
                if (range == 'custom') {
                    params.date_range_from = this._dateToString(fromDate);
                    params.date_range_to = this._dateToString(toDate);
                }

                document.location.search = '?' + $.param(params);
            }
        },

        /**
         * On cancel button click
         */
        onCancelBtnClick: function () {
            $('#' + this.options.calendarsHeaderId).removeClass('opened');
            $('#' + this.options.calendarsContainerId).removeClass('is_displayed');
        },

        /**
         * Set custom range value
         */
        _setCustomRange: function () {
            $(this.options.customDateRangeSelector).val('custom');
        },

        /**
         * Convert date to string
         *
         * @param {Date} date
         * @returns {string}
         */
        _dateToString: function(date) {
            return date.getFullYear().toString()
                + '-' + ('0' + (date.getMonth() + 1)).slice(-2)
                + '-' + ('0' + date.getDate()).slice(-2);
        },

        /**
         * Clear validation errors
         */
        _clearValidationErrors: function () {
            var form = this.element.find(this.options.calendarForm);

            if (form.validation()) {
                form.validation('clearError');
            }
        }
    });

    return $.mage.awOscDateRangeSelector;
});
