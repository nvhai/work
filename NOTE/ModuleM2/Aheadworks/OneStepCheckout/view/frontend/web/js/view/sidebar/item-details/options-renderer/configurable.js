/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'underscore',
        'Aheadworks_OneStepCheckout/js/view/sidebar/item-details/options-renderer/renderer-abstract',
        'uiLayout',
        'uiRegistry',
        'mageUtils'
    ],
    function (
        _,
        Component,
        layout,
        registry,
        utils
    ) {
        'use strict';

        var attributeFieldTemplate = {
            parent: '${ $.$data.parentName }',
            name: '${ $.$data.name }',
            provider: '${ $.$data.provider }',
            dataScope: '${ $.$data.dataScope }',
            label: '${ $.$data.label }',
            sortOrder: '${ $.$data.sortOrder }',
            value: '${ $.$data.value }',
            component: 'Aheadworks_OneStepCheckout/js/view/sidebar/item-details/'
                + 'options-renderer/configurable/field/select'
        };

        return Component.extend({
            defaults: {
                template: 'Aheadworks_OneStepCheckout/sidebar/item-details/options/configurable',
                selectedProducts: {}
            },

            /**
             * @inheritdoc
             */
            initialize: function () {
                var self = this;

                this._super().initChildren();

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    checkoutProvider.on(self.scopeId, function (optionsData) {
                        self._recollectProducts(optionsData);
                    });
                });

                return this;
            },

            /**
             * Init child components
             *
             * @returns {Component}
             */
            initChildren: function () {
                var defaultValues = this.options.defaultValues;

                _.each(this.options.attributes, function (attributeData, attributeId) {
                    this._createFieldComponent(attributeData, defaultValues[attributeId]);
                }, this);

                return this;
            },

            /**
             * @inheritdoc
             */
            initObservable: function () {
                this._super();

                this.observe(['selectedProducts']);

                return this;
            },

            /**
             * @inheritdoc
             */
            disposeSubscriptions: function () {
                var self = this;

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    checkoutProvider.off(self.scopeId);
                });
            },

            /**
             * Create field component
             *
             * @param {Object} attributeData
             * @param {string} defaultValue
             */
            _createFieldComponent: function (attributeData, defaultValue) {
                var templateData = {
                        parentName: this.name,
                        name: attributeData.code,
                        provider: 'checkoutProvider',
                        dataScope: this.scopeId + '.' + attributeData.code,
                        label: attributeData.label,
                        sortOrder: 0,
                        value: defaultValue
                    },
                    rendererComponent = utils.template(attributeFieldTemplate, templateData);

                utils.extend(
                    rendererComponent,
                    {
                        config: {
                            customScope: this.scopeId,
                            template: 'ui/form/field',
                            options: attributeData.options
                        },
                        deps: ['checkoutProvider'],
                        itemId: this.itemId
                    }
                );
                layout([rendererComponent]);
            },

            /**
             * Recollect products
             *
             * @param {Object} optionsData
             */
            _recollectProducts: function (optionsData) {
                var selectedProducts = this.selectedProducts();

                _.each(optionsData, function (value, name) {
                    var attribute = _.find(this.options.attributes, function (attrData) {
                            return name == attrData.code;
                        }),
                        option = _.find(attribute.options, function (optData) {
                            return value == optData.value;
                        });

                    if (option !== undefined) {
                        selectedProducts[attribute.code] = option.products;
                    }
                }, this);
                this.selectedProducts(selectedProducts);
            }
        });
    }
);
