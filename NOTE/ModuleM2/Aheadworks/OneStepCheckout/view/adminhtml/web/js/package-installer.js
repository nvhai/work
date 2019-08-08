/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    $.widget('mage.awOscPackageInstaller', {
        options: {
            submitUrl: '#',
            packageName: '',
            packageVersion: '*',
            loaderContext: 'body',
            installedLabel: $t('Installed'),
            progressLabel: $t('Installing...'),
            isInstalled: false,
            linkedControlsSelector: '',
            successEventName: 'awOscPackageInstalled',
            prepareRequestDataEventName: 'awOscPrepareInstallRequestData'
        },
        isInstalled: false,

        /**
         * Initialize widget
         */
        _create: function() {
            this._bind();
        },

        /**
         * @inheritdoc
         */
        _init: function () {
            this.isInstalled = this.options.isInstalled;
        },

        /**
         * Event binding
         */
        _bind: function () {
            this._on({'click': 'onClick'});
        },

        /**
         * On text input value change event handler
         *
         * @param {Object} event
         */
        onClick: function (event) {
            var self = this;

            event.preventDefault();
            if (!this.isInstalled) {
                $.ajax({
                    url: this.options.submitUrl,
                    data: this._prepareRequestData(),
                    type: 'post',
                    dataType: 'json',

                    beforeSend: $.proxy(this._startProgress, this),

                    /**
                     * Called when request succeeds
                     *
                     * @param {Object} response
                     */
                    success: function(response) {
                        if (response.success) {
                            self._setInstalled();
                            $(self.options.linkedControlsSelector).trigger(
                                self.options.successEventName,
                                [response]
                            );
                        } else if (response.error) {
                            self._showMessage(response.error, true);
                        }
                    },

                    complete: $.proxy(this._stopProgress, this)
                });
            }
        },

        /**
         * Prepare request data
         *
         * @returns Object
         */
        _prepareRequestData: function () {
            var data = {
                'package_name': this.options.packageName,
                'package_version': this.options.packageVersion,
                'form_key': FORM_KEY
            };

            $(this.options.linkedControlsSelector).trigger(
                this.options.prepareRequestDataEventName,
                [data]
            );

            return data;
        },

        /**
         * Start progress
         */
        _startProgress: function () {
            var initialLabel = this.element.text();

            $(this.options.loaderContext).trigger('processStart');
            this.element.prop('disabled', true)
                .text(this.options.progressLabel)
                .data('initial-label', initialLabel);
        },

        /**
         * Stop progress
         */
        _stopProgress: function () {
            $(this.options.loaderContext).trigger('processStop');
            if (!this.isInstalled) {
                this.element.text(this.element.data('initial-label'))
                    .removeProp('disabled');
            }
        },

        /**
         * Set installed state
         */
        _setInstalled: function () {
            this.isInstalled = true;
            this.element.text(this.options.installedLabel);
        },

        /**
         * Show message
         *
         * @param {String} message
         * @param {Boolean} isError
         */
        _showMessage: function (message, isError) {
            $('body').notification('clear')
                .notification('add', {
                    error: isError,
                    message: $.mage.__(message),

                    /**
                     * @param {String} message
                     */
                    insertMethod: function (message) {
                        var $wrapper = $('<div/>').html(message);

                        $('.page-main-actions').after($wrapper);
                    }
                });
        }
    });

    return $.mage.awOscPackageInstaller;
});
