/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Magento_Ui/js/lib/view/utils/async',
        'underscore',
        'mageUtils',
        'Aheadworks_OneStepCheckout/js/model/render-postprocessor/float-label/converters-pool'
    ],
    function ($, _, utils, floatLabelConvertersPool) {
        'use strict';

        return {
            paymentMethodItemsSelectors: '[data-role=payment-methods-load] div.payment-method',
            actionsToolbarSelector: 'div.actions-toolbar',

            /**
             * Init post processing
             */
            initProcessing: function () {
                var self = this;

                $.async(this.paymentMethodItemsSelectors, function (methodItem) {
                    self._processPaymentMethodContent($(methodItem));
                });
            },

            /**
             * Process payment method item content
             *
             * @param {jQuery} element
             */
            _processPaymentMethodContent: function (element) {
                var methodCode = this._getPaymentMethodCode(element);

                this._hideActionToolbar(element);
                this._correctHtmlAttributes(element);
                floatLabelConvertersPool.getConverter(methodCode).convertPaymentMethodInputs(element);
            },

            /**
             * Hide action toolbar
             *
             * @param {jQuery} methodItem
             */
            _hideActionToolbar: function (methodItem) {
                methodItem.find(this.actionsToolbarSelector).hide();
            },

            /**
             * Retrieve payment method code from method item element
             *
             * @param {jQuery} element
             * @returns {string}
             */
            _getPaymentMethodCode: function (element) {
                var input = element.find('input[name="payment[method]"]');

                return input.val();
            },

            /**
             * Check and correct html attributes
             *
             * @param {jQuery} element
             */
            _correctHtmlAttributes: function (element) {
                this._correctCheckboxFieldsAttributes(element);
            },

            /**
             * Correct checkboxes html attributes.
             * Searches for missed id or for attribute and set it
             *
             * @param {jQuery} element
             */
            _correctCheckboxFieldsAttributes: function (element) {
                element.find('.field.choice').each(function () {
                    var field = $(this),
                        input = field.find('input[type=checkbox]'),
                        label = field.find('label'),
                        forAttr,
                        idAttr;

                    if (input.length > 0 && label.length > 0) {
                        forAttr = label.attr('for');
                        idAttr = input.attr('id');

                        if (_.isUndefined(forAttr)) {
                            if (_.isUndefined(idAttr)) {
                                idAttr = utils.uniqueid();
                            }
                            label.attr('for', idAttr);
                        } else if (_.isUndefined(idAttr) || forAttr != idAttr) {
                            input.attr('id', forAttr);
                        }
                    }
                });
            }
        };
    }
);
