/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'uiComponent',
        'Aheadworks_OneStepCheckout/js/model/google-autocomplete'
    ],
    function (Component, autocomplete) {
        'use strict';

        autocomplete.init('[data-role=autocomplete]');

        return Component.extend({
            defaults: {
                regionMap: []
            },

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super();
                autocomplete.setRegionMap(this.regionMap);
            }
        });
    }
);
