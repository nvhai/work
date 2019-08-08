/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [],
    function () {
        'use strict';

        var options = window.checkoutConfig.editableItemOptions;

        return {

            /**
             * Get editable options config data by item Id
             *
             * @param {Number} itemId
             * @returns {Object|null}
             */
            getConfigOptionsDataByItemId: function (itemId) {
                return options[itemId] !== undefined
                    ? options[itemId]
                    : null;
            },

            /**
             * Set editable options config data
             *
             * @param {Object} optionsData
             */
            setConfigOptionsData: function (optionsData) {
                options = optionsData;
            }
        };
    }
);
