/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'uiComponent'
    ],
    function (Component) {
        'use strict';

        var trustSealsConfig = window.checkoutConfig.trustSeals;

        return Component.extend({
            defaults: {
                template: 'Aheadworks_OneStepCheckout/trust-seals'
            },

            /**
             * Check if block enabled
             *
             * @returns {boolean}
             */
            isEnabled: function () {
                return trustSealsConfig.isEnabled
            },

            /**
             * Get secure payments label
             *
             * @returns {string}
             */
            getLabel: function () {
                return trustSealsConfig.label;
            },

            /**
             * Get secure payment text
             *
             * @returns {string}
             */
            getText: function () {
                return trustSealsConfig.text;
            },

            /**
             * Get badges
             *
             * @returns {Array}
             */
            getBadges: function () {
                return trustSealsConfig.badges;
            }
        });
    }
);
