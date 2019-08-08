/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [],
    function () {
        'use strict';

        var timeout = 5000;

        return {

            /**
             * Process error messages
             *
             * @param {jQuery} element
             * @param {Object} messageContainer
             */
            processError: function (element, messageContainer) {
                var messages = messageContainer.getErrorMessages();

                if (messages().length > 0) {
                    element.trigger('awOscVMError', [messages()]);
                    this._reset(element);
                }
            },

            /**
             * Process success messages
             *
             * @param {jQuery} element
             * @param {Object} messageContainer
             */
            processSuccess: function (element, messageContainer) {
                var messages = messageContainer.getSuccessMessages();

                if (messages().length > 0) {
                    element.trigger('awOscVMSuccess', [messages()]);
                    this._reset(element);
                }
            },

            /**
             * Reset messages immediately
             *
             * @param {jQuery} element
             */
            resetImmediate: function (element) {
                element.trigger('awOscVMReset', [true]);
            },

            /**
             * Reset messages
             *
             * @param {jQuery} element
             */
            _reset: function (element) {
                setTimeout(function () {
                    element.trigger('awOscVMReset', [false]);
                }, timeout);
            }
        }
    }
);
