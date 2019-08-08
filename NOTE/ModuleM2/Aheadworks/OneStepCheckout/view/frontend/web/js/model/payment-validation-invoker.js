/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'underscore',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function ($, _, additionalValidators) {
        'use strict';

        var componentMethodsMap = {
                'authorizenet_directpost': ['validateHandler'],
                'braintree': ['validateCardType', 'validate']
            },
            invokeSequence = {
                'braintree': ['validateCardType', 'validate']
            },
            defaultComponentMethod = 'validate';

        return {

            /**
             * Invoke validation methods of payment renderer component
             *
             * @param {uiComponent} component
             * @param {string} methodCode
             * @returns {Deferred}
             */
            invokeValidate: function (component, methodCode) {
                var self = this,
                    validationSubject = {},
                    componentMethods = [],
                    sequence,
                    invokeIndex = 0,
                    invokeNext = function () {
                        var methodName = sequence[invokeIndex];

                        invokeIndex++;

                        return methodName == 'invokeSpecific'
                            ? validationSubject[methodName](component, methodCode)
                            : validationSubject[methodName](component);
                    },
                    invokeResolveHandler,
                    invokeRejectHandler,
                    deferred = $.Deferred();

                if (typeof componentMethodsMap[methodCode] != 'undefined') {
                    _.each(componentMethodsMap[methodCode], function (methodName) {
                        componentMethods.push(methodName);
                    });
                } else {
                    componentMethods.push(defaultComponentMethod);
                }
                _.each(componentMethods, function (methodName) {
                    validationSubject[methodName] = function (comp) {
                        return comp[methodName]()
                            ? $.Deferred().resolve()
                            : $.Deferred().reject();
                    };
                });
                validationSubject.invokeSpecific = function (comp, method) {
                    return self._invokeSpecificValidation(comp, method);
                };

                sequence = typeof invokeSequence[methodCode] != 'undefined'
                    ? invokeSequence[methodCode]
                    : componentMethods;
                invokeResolveHandler = function () {
                    if (invokeIndex < sequence.length) {
                        invokeNext().done(invokeResolveHandler).fail(invokeRejectHandler);
                    } else if (additionalValidators.validate()) {
                        deferred.resolve();
                    } else {
                        deferred.reject();
                    }
                };
                invokeRejectHandler = function () {
                    deferred.reject();
                };
                invokeResolveHandler();

                return deferred;
            },

            /**
             * Invoke payment method specific validation that require custom logic
             *
             * @param {uiComponent} component
             * @param {string} methodCode
             * @returns {Deferred}
             */
            _invokeSpecificValidation: function (component, methodCode) {
                // No implementation
                return $.Deferred().resolve();
            }
        };
    }
);
