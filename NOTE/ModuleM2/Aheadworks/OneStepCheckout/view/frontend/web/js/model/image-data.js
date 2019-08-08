/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [],
    function () {
        'use strict';

        var imageConfigData = window.checkoutConfig.itemImageData,
            itemsImageData = imageConfigData.itemsData;

        return {

            /**
             * Get item image data by item Id
             *
             * @param {Number} itemId
             * @returns {Object|null}
             */
            getItemImageDataByItemId: function (itemId) {
                return itemsImageData[itemId] !== undefined
                    ? itemsImageData[itemId]
                    : null;
            },

            /**
             * Set cart items image data
             *
             * @param {Object} data
             */
            setItemsImageData: function (data) {
                itemsImageData = data;
            },

            /**
             * Get images width
             *
             * @returns {Number}
             */
            getImagesWidth: function () {
                return imageConfigData.attributes.width;
            },

            /**
             * Get images height
             *
             * @returns {Number}
             */
            getImagesHeight: function () {
                return imageConfigData.attributes.height;
            },

            /**
             * Get placeholder url
             *
             * @returns {string}
             */
            getPlaceHolderUrl: function () {
                return imageConfigData.placeholderUrl;
            }
        };
    }
);
