/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'ko',
        'underscore',
        'uiComponent',
        'uiLayout',
        'mageUtils',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/method-list',
        'Magento_Checkout/js/model/payment/renderer-list',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Aheadworks_OneStepCheckout/js/model/render-postprocessor',
        'Aheadworks_OneStepCheckout/js/model/payment-methods-service',
        'Aheadworks_OneStepCheckout/js/model/checkout-data-completeness-logger'
    ],
    function (
        $,
        ko,
        _,
        Component,
        layout,
        utils,
        quote,
        methodList,
        rendererList,
        paymentService,
        methodConverter,
        checkoutDataResolver,
        postProcessor,
        paymentMethodsService,
        completenessLogger
    ) {
        'use strict';

        paymentService.setPaymentMethods(methodConverter(window.checkoutConfig.paymentMethods));

        return Component.extend({
            defaults: {
                template: 'Aheadworks_OneStepCheckout/payment-method/list',
                isPaymentMethodsAvailable: false,
                isLoading: paymentMethodsService.isLoading
            },
            errorValidationMessage: ko.observable(''),

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super().initMethodsRenders();

                checkoutDataResolver.resolvePaymentMethod();
                methodList.subscribe(
                    function (changes) {
                        var renders = this.getRegion('payment-methods-items'),
                            methods = [],
                            methodsToDelete = [],
                            methodsToAdd = {},
                            sortOrderCounter = 0;

                        checkoutDataResolver.resolvePaymentMethod();

                        _.each(changes, function (change) {
                            var methodCode = change.value.method;

                            if (change.status === 'added') {
                                methodsToAdd[methodCode] = change.value;
                                methods.push(methodCode);
                            }
                        }, this);
                        _.each(changes, function (change) {
                            var methodCode = change.value.method;

                            if (change.status === 'deleted') {
                                methodsToDelete.push(methodCode);
                                if (_.indexOf(methods, methodCode) == -1) {
                                    methods.push(methodCode);
                                }
                            }
                        }, this);

                        _.each(methods, function (methodCode) {
                            var methodRenderer;

                            if (_.indexOf(methodsToDelete, methodCode) != -1
                                && methodsToAdd[methodCode] === undefined
                            ) {
                                this._removeRenderer(methodCode);
                            } else if (methodsToAdd[methodCode] !== undefined
                                && _.indexOf(methodsToDelete, methodCode) == -1
                            ) {
                                this._createRenderer(methodsToAdd[methodCode], sortOrderCounter++);
                            } else {
                                methodRenderer = _.find(renders(), function (renderer) {
                                    return renderer.item.method.indexOf(methodCode) === 0;
                                });
                                if (typeof methodRenderer != 'undefined') {
                                    methodRenderer.updateConfig(
                                        methodRenderer.sortOrder,
                                        sortOrderCounter++,
                                        'sortOrder'
                                    );
                                }
                            }
                        }, this);
                    }, this, 'arrayChange');
                quote.paymentMethod.subscribe(function () {
                    this.errorValidationMessage('');
                }, this);
                completenessLogger.bindField('paymentMethod', quote.paymentMethod);

                return this;
            },

            /**
             * @inheritdoc
             */
            initObservable: function () {
                this._super();

                this.isPaymentMethodsAvailable = ko.computed(function () {
                    return methodList().length > 0
                });

                return this;
            },

            /**
             * Create renders for child payment methods
             *
             * @returns {Component}
             */
            initMethodsRenders: function () {
                var sortOrderCounter = 0;

                _.each(methodList(), function (methodData) {
                    this._createRenderer(methodData, sortOrderCounter);
                    sortOrderCounter++;
                }, this);

                return this;
            },

            /**
             * Create payment method renderer
             *
             * @param {Object} methodData
             * @param {number} sortOrder
             */
            _createRenderer: function (methodData, sortOrder) {
                var renderer = this._getRendererForMethod(methodData.method);

                if (typeof renderer != 'undefined') {
                    layout([
                        this._createRendererComponent(
                            {
                                config: renderer.config,
                                component: renderer.component,
                                name: renderer.type,
                                method: methodData.method,
                                item: methodData,
                                displayArea: 'payment-methods-items',
                                sortOrder: sortOrder
                            }
                        )]
                    );
                }
            },

            /**
             * Remove payment method renderer
             *
             * @param {string} methodCode
             */
            _removeRenderer: function (methodCode) {
                var items = this.getRegion('payment-methods-items');

                _.find(items(), function (value) {
                    if (value.item.method.indexOf(methodCode) === 0) {
                        value.disposeSubscriptions();
                        value.destroy();
                    }
                });
            },

            /**
             * Get renderer definition for payment method
             *
             * @param {String} methodCode
             * @returns {Object}
             */
            _getRendererForMethod: function (methodCode) {
                return _.find(rendererList(), function (renderer) {
                    if (renderer.hasOwnProperty('typeComparatorCallback') &&
                        typeof renderer.typeComparatorCallback == 'function'
                    ) {
                        return renderer.typeComparatorCallback(renderer.type, methodCode);
                    } else {
                        return renderer.type === methodCode;
                    }
                });
            },

            /**
             * Create renderer component definition
             *
             * @param {Object} payment
             * @returns {Object}
             */
            _createRendererComponent: function (payment) {
                var rendererTemplate,
                    rendererComponent,
                    templateData;

                templateData = {
                    parentName: this.name,
                    name: payment.name
                };
                rendererTemplate = {
                    parent: '${ $.$data.parentName }',
                    name: '${ $.$data.name }',
                    displayArea: payment.displayArea,
                    component: payment.component,
                    sortOrder: payment.sortOrder
                };
                rendererComponent = utils.template(rendererTemplate, templateData);
                utils.extend(rendererComponent, {
                    item: payment.item,
                    config: payment.config
                });

                return rendererComponent;
            },

            /**
             * On render list event handler
             */
            onRender: function () {
                postProcessor.initProcessing();
            },

            /**
             * Validate payment method data
             *
             * @returns {boolean}
             */
            validate: function () {
                if (methodList().length > 0 && !quote.paymentMethod()) {
                    this.errorValidationMessage('Please specify a payment method.');
                    return false;
                }

                return true;
            }
        });
    }
);
