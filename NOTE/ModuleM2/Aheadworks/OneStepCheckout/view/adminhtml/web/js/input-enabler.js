/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.awOscInputEnabler', {
        options: {
            isEnabledInitial: false
        },
        isEnabled: false,

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
            if (this.options.isEnabledInitial) {
                this.enable();
            } else {
                this.disable();
            }
        },

        /**
         * Event binding
         */
        _bind: function () {
            this._on({'awOscDownloaded': 'enable'});
        },

        /**
         * Enable element
         */
        enable: function () {
            this.element.removeProp('disabled');
            this.isEnabled = true;
        },

        /**
         * Disable element
         */
        disable: function () {
            this.element.prop('disabled', true);
            this.isEnabled = false;
        }
    });

    return $.mage.awOscInputEnabler;
});
