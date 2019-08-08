/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'underscore',
        'ko',
        'mageUtils',
        'uiComponent',
        'uiLayout',
        'Magento_Customer/js/model/address-list'
    ],
    function ($, _, ko, utils, Component, layout, addressList) {
        'use strict';

        var defaultRendererTemplate = {
            parent: '${ $.$data.parentName }',
            name: '${ $.$data.name }',
            component: 'Aheadworks_OneStepCheckout/js/view/shipping-address/address-renderer/default'
        };

        return Component.extend({
            defaults: {
                template: 'Aheadworks_OneStepCheckout/shipping-address/list',
                isShown: addressList().length > 0,
                rendererTemplates: []
            },
            renders: [],

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super()
                    .initAddressRenders();

                addressList.subscribe(
                    function (changes) {
                        _.each(changes, function (change) {
                            if (change.status === 'added') {
                                this._createRenderer(change.value, change.index);
                            }
                        }, this);
                    },
                    this,
                    'arrayChange'
                );

                return this;
            },

            /**
             * Init address renders
             *
             * @returns {Component}
             */
            initAddressRenders: function () {
                _.each(addressList(), function (address, index) {
                    this._createRenderer(address, index);
                }, this);

                return this;
            },

            /**
             * Create address renderer
             *
             * @param address
             * @param index
             */
            _createRenderer: function (address, index) {
                var rendererTemplate,
                    templateData,
                    renderer;

                if (index in this.renders) {
                    this.renders[index].address(address);
                } else {
                    if (address.getType() != undefined
                        && this.rendererTemplates[address.getType()] != undefined
                    ) {
                        rendererTemplate = utils.extend(
                            {},
                            defaultRendererTemplate,
                            this.rendererTemplates[address.getType()]
                        );
                    } else {
                        rendererTemplate = defaultRendererTemplate;
                    }
                    templateData = {
                        parentName: this.name,
                        name: index
                    };

                    renderer = utils.template(rendererTemplate, templateData);
                    utils.extend(renderer, {address: ko.observable(address)});
                    layout([renderer]);
                    this.renders[index] = renderer;
                }
            }
        });
    }
);
