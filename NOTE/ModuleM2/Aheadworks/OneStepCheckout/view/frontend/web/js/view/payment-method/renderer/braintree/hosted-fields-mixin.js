/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'mage/utils/wrapper',
        'Aheadworks_OneStepCheckout/js/model/render-postprocessor/float-label/converter/braintree-hosted-fields',
        'Magento_Braintree/js/view/payment/adapter',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/translate'
    ],
    function ($, wrapper, flLabelConverter, adapter, fullScreenLoader, $t) {
        'use strict';

        return function (renderer) {
            return renderer.extend({
                defaults: {
                    /**
                     * @inheritdoc
                     */
                    clientConfig: {

                        /**
                         * @inheritdoc
                         */
                        onError: function (response) {
                            fullScreenLoader.stopLoader();
                            adapter.showError($t('Payment ' + this.getTitle() + ' can\'t be initialized'));
                            throw response.message;
                        }
                    }
                },

                /**
                 * @inheritdoc
                 */
                getHostedFields: function () {
                    var fields = this._super();

                    if (fields.onFieldEvent !== undefined) {
                        fields.onFieldEvent = wrapper.wrap(fields.onFieldEvent, function (originalHandler, event) {
                            var expDateContainersSelector = [
                                    flLabelConverter.expMonthSelector,
                                    flLabelConverter.expYearSelector
                                ].join(', '),
                                container = $(event.target.container),
                                flFieldElement = container.closest('div.field'),
                                field = flFieldElement.find(expDateContainersSelector).length > 0
                                    ? container.closest(expDateContainersSelector)
                                    : flFieldElement;

                            flFieldElement.awOscBraintreeHostedFloatLabel(
                                'triggerFieldEvent',
                                field,
                                event.isFocused,
                                event.isEmpty
                            );

                            return originalHandler(event);
                        });
                    }

                    delete fields.expirationMonth.placeholder;
                    delete fields.expirationYear.placeholder;

                    return fields;
                },

                /**
                 * @inheritdoc
                 */
                placeOrderClick: function () {
                    fullScreenLoader.startLoader();
                    this._super();
                }
            });
        }
    }
);
