/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'ko',
        'Magento_Ui/js/form/form',
        'Aheadworks_OneStepCheckout/js/model/checkout-data',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/quote',
        'Aheadworks_OneStepCheckout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Aheadworks_OneStepCheckout/js/model/payment-methods-service',
        'Aheadworks_OneStepCheckout/js/model/totals-service',
        'Aheadworks_OneStepCheckout/js/model/checkout-section/cache-key-generator',
        'Aheadworks_OneStepCheckout/js/model/checkout-section/cache',
        'Aheadworks_OneStepCheckout/js/model/checkout-data-completeness-logger'
    ],
    function (
        $,
        ko,
        Component,
        checkoutData,
        selectShippingMethodAction,
        shippingService,
        quote,
        setShippingInformationAction,
        paymentService,
        paymentMethodConverter,
        paymentMethodsService,
        totalsService,
        cacheKeyGenerator,
        cacheStorage,
        completenessLogger
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_OneStepCheckout/shipping-method'
            },
            rates: shippingService.getShippingRates(),
            isShown: ko.computed(function () {
                return !quote.isQuoteVirtual();
            }),
            isLoading: shippingService.isLoading,
            isSelected: ko.computed(function () {
                    return quote.shippingMethod() ?
                    quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code
                        : null;
                }
            ),
            errorValidationMessage: ko.observable(''),

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super();

                quote.shippingMethod.subscribe(function () {
                    this.errorValidationMessage('');
                }, this);
                completenessLogger.bindField('shippingMethod', quote.shippingMethod);

                return this;
            },

            /**
             * Select shipping method
             *
             * @param {Object} shippingMethod
             * @return {Boolean}
             */
            selectShippingMethod: function (shippingMethod) {
                selectShippingMethodAction(shippingMethod);
                checkoutData.setSelectedShippingRate(
                    shippingMethod.carrier_code + '_' + shippingMethod.method_code
                );
                paymentMethodsService.isLoading(true);
                totalsService.isLoading(true);
                setShippingInformationAction().done(
                    function (response) {
                        var methods = paymentMethodConverter(response.payment_methods),
                            cacheKey = cacheKeyGenerator.generateCacheKey({
                                shippingAddress: quote.shippingAddress(),
                                billingAddress: quote.billingAddress(),
                                totals: quote.totals()
                            });

                        quote.setTotals(response.totals);
                        paymentService.setPaymentMethods(methods);
                        cacheStorage.set(
                            cacheKey,
                            {'payment_methods': methods, 'totals': response.totals}
                        );
                    }
                ).always(
                    function () {
                        paymentMethodsService.isLoading(false);
                        totalsService.isLoading(false);
                    }
                );

                return true;
            },

            /**
             * @inheritdoc
             */
            validate: function () {
                if (!quote.shippingMethod() && !quote.isQuoteVirtual()) {
                    this.errorValidationMessage('Please specify a shipping method.');
                    this.source.set('params.invalid', true);
                }
            }
        });
    }
);
