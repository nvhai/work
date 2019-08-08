/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'underscore',
        'uiComponent',
        'uiLayout',
        'mageUtils',
        'Magento_Checkout/js/model/quote'
    ],
    function (_, Component, layout, utils, quote) {
        'use strict';

        var defaultRendererComponent = 'Aheadworks_OneStepCheckout/js/view/actions-toolbar/renderer/default';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_OneStepCheckout/actions-toolbar',
                methodCode: null,
                rendererList: {},
                currentRendererComponent: null
            },

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super()
                    ._createActionRenderer();

                quote.paymentMethod.subscribe(function (value) {
                    if (value) {
                        this.methodCode(value.method);
                    } else {
                        this.methodCode(null);
                    }
                    this.reInitActionRenderer();
                }, this);

                return this;
            },

            /**
             * @inheritdoc
             */
            initObservable: function () {
                this._super()
                    .observe({
                        'methodCode': quote.paymentMethod()
                            ? quote.paymentMethod().method
                            : null
                    });

                return this;
            },

            /**
             * Reinitialize action renderer
             */
            reInitActionRenderer: function () {
                if (this.currentRendererComponent != this._getRendererComponent()) {
                    this._removeActionRender();
                    this._createActionRenderer();
                } else {
                    this._reAssignActionRenderer();
                }
            },

            /**
             * Get current renderer component
             *
             * @returns {string}
             */
            _getRendererComponent: function () {
                var methodCode = this.methodCode();

                if (methodCode) {
                    return typeof this.rendererList[methodCode] != 'undefined'
                        ? this.rendererList[methodCode]
                        : defaultRendererComponent;
                } else {
                    return defaultRendererComponent
                }
            },

            /**
             * Create action renderer
             */
            _createActionRenderer: function () {
                var methodCode = this.methodCode(),
                    component = this._getRendererComponent(),
                    rendererTemplate = {
                        parent: '${ $.$data.parentName }',
                        name: '${ $.$data.name }',
                        displayArea: '${ $.$data.displayArea }',
                        component: component,
                        methodCode: methodCode
                    },
                    templateData = {
                        parentName: this.name,
                        name: methodCode + '_actions',
                        displayArea: 'payment-actions'
                    },
                    rendererComponent = utils.template(rendererTemplate, templateData);

                layout([rendererComponent]);
                this.currentRendererComponent = component;
            },

            /**
             * Remove action renderer
             */
            _removeActionRender: function () {
                var items = this.getRegion('payment-actions');

                _.find(items(), function (value) {
                    value.disposeSubscriptions();
                    value.destroy();
                });
            },

            /**
             * Reassign action renderer component to new payment method component
             */
            _reAssignActionRenderer: function () {
                var self = this,
                    items = this.getRegion('payment-actions');

                _.find(items(), function (value) {
                    value.methodCode = self.methodCode();
                    value.initMethodsRenderComponent();
                });
            }
        });
    }
);
