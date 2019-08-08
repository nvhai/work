/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    ['underscore'],
    function (_) {
        'use strict';

        var cache = {};

        /**
         * Data buffering
         *
         * @param {String} key
         * @param {Object} buffer
         * @param {String} operation
         */
        function bufferData (key, buffer, operation) {
            var isRead = operation == 'read',
                isWrite = operation == 'write';

            if (isRead || isWrite) {
                if (!_.has(cache, key)) {
                    cache[key] = {};
                }

                if (isRead) {
                    buffer = copyData(buffer, cache[key]);
                }
                if (isWrite) {
                    cache[key] = copyData(cache[key], buffer);
                }
            }
        }

        /**
         * Copy data
         *
         * @param {Object} destination
         * @param {Object} source
         * @returns {Object}
         */
        function copyData (destination, source) {
            destination = _.extend({}, source);
            if (_.has(source, 'totals')) {
                _.each(source.totals.items, function (item, index) {
                    var destinationItem = destination.totals.items[index];

                    if (!_.isEqual(destinationItem, index)) {
                        destination.totals.items[index] = deepClone(item);
                    }
                });
            }

            return destination;
        }

        /**
         * Perform deep object clone
         *
         * @param {Object} object
         * @returns {Object}
         */
        function deepClone (object) {
            return JSON.parse(JSON.stringify(object));
        }

        return {
            /**
             * Retrieve data
             *
             * @param {string} key
             * @returns {boolean|Object.<string, Array>}
             */
            get: function(key) {
                var buffer = {};

                if (cache[key]) {
                    bufferData(key, buffer, 'read');
                    return buffer;
                }
                return false;
            },

            /**
             * Set data
             *
             * @param {string} key
             * @param {Object.<string, Array>} data
             */
            set: function(key, data) {
                bufferData(key, data, 'write');
            }
        };
    }
);
