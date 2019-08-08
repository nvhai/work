/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'underscore',
    'mage/translate'
], function ($, _, $t) {
    'use strict';

    $.widget('mage.awOscDownloader', {
        options: {
            submitUrl: '#',
            downloadUrl: '#',
            fileName: '',
            loaderContext: 'body',
            downloadedLabel: $t('Update'),
            progressLabelInitial: $t('Downloading...'),
            progressLabelDownloaded: $t('Updating...'),
            isDownloaded: false,
            linkedControlsSelector: '',
            successEventName: 'awOscDownloaded',
            noteSelector: '[data-role=download-geo-ip-updated-at]',
            noteTemplate: 'Last time updated: <%= updatedAt %>'
        },
        isDownloaded: false,

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
            this.isDownloaded = this.options.isDownloaded;
        },

        /**
         * Event binding
         */
        _bind: function () {
            this._on({
                'click': 'onClick',
                'awOscPackageInstalled': 'onPackageInstalled',
                'awOscPrepareInstallRequestData': 'onPrepareInstallRequestData'
            });
        },

        /**
         * On text input value change event handler
         *
         * @param {Object} event
         */
        onClick: function (event) {
            var self = this;

            event.preventDefault();
            $.ajax({
                url: this.options.submitUrl,
                data: {
                    'download_url': this.options.downloadUrl,
                    'file_name': this.options.fileName,
                    'form_key': FORM_KEY
                },
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
                        self._setDownloaded();
                        self._updateNote({updatedAt: response.updated_at});
                        if (response.success_message) {
                            self._showMessage(response.success_message, false);
                        }
                    } else if (response.error) {
                        self._showMessage(response.error, true);
                    }
                },

                complete: $.proxy(this._stopProgress, this)
            });
        },

        /**
         * On package installed event handler
         *
         * @param {Object} event
         * @param {Object} data
         */
        onPackageInstalled: function (event, data) {
            if (data.database_downloaded) {
                this._setDownloaded();
                this._updateNote({updatedAt: data.database_updated_at});
            } else {
                this.element.removeProp('disabled');
            }
        },

        /**
         * On prepare install request data event handler
         *
         * @param {Object} event
         * @param {Object} data
         */
        onPrepareInstallRequestData: function (event, data) {
            data.database_download_url = this.options.downloadUrl;
            data.database_file_name = this.options.fileName;
        },

        /**
         * Start progress
         */
        _startProgress: function () {
            var initialLabel = this.element.text(),
                progressLabel = this.isDownloaded
                    ? this.options.progressLabelDownloaded
                    : this.options.progressLabelInitial;

            $(this.options.loaderContext).trigger('processStart');
            this.element.prop('disabled', true)
                .text(progressLabel)
                .data('initial-label', initialLabel);
        },

        /**
         * Stop progress
         */
        _stopProgress: function () {
            var label = this.isDownloaded
                ? this.options.downloadedLabel
                : this.element.data('initial-label');

            $(this.options.loaderContext).trigger('processStop');
            this.element.text(label)
                .removeProp('disabled');
        },

        /**
         * Set downloaded state
         */
        _setDownloaded: function () {
            this.isDownloaded = true;
            this.element.text(this.options.downloadedLabel)
                .removeProp('disabled');
            $(this.options.linkedControlsSelector).trigger(this.options.successEventName);
        },

        /**
         * Update note
         *
         * @param {Object} data
         */
        _updateNote: function (data) {
            var template = _.template(this.options.noteTemplate);

            $(this.options.noteSelector).text(template(data));
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

    return $.mage.awOscDownloader;
});
