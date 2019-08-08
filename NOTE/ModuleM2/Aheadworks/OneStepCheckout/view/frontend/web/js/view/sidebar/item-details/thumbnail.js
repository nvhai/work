/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'uiComponent',
        'Aheadworks_OneStepCheckout/js/model/image-data'
    ],
    function (Component, imageData) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/summary/item/details/thumbnail'
            },

            /**
             * Get src attribute
             *
             * @param {Object} item
             * @returns {string|null}
             */
            getSrc: function(item) {
                return this._getItemImageDataProperty(
                    item.item_id,
                    'src',
                    imageData.getPlaceHolderUrl()
                );
            },

            /**
             * Get width attribute
             *
             * @param {Object} item
             * @returns {string|null}
             */
            getWidth: function(item) {
                return imageData.getImagesWidth();
            },

            /**
             * Get height attribute
             *
             * @param {Object} item
             * @returns {string|null}
             */
            getHeight: function(item) {
                return imageData.getImagesHeight();
            },

            /**
             * Get alt attribute
             *
             * @param {Object} item
             * @returns {string|null}
             */
            getAlt: function(item) {
                return this._getItemImageDataProperty(item.item_id, 'alt', null);
            },

            /**
             * Get item image data property value
             *
             * @param {Number} itemId
             * @param {string} propName
             * @param {*} defaultValue
             * @returns {*}
             */
            _getItemImageDataProperty: function (itemId, propName, defaultValue) {
                var image = imageData.getItemImageDataByItemId(itemId);

                return image ? image[propName] : defaultValue;
            }
        });
    }
);
