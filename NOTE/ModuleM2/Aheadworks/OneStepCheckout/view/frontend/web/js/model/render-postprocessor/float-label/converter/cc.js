/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Magento_Ui/js/lib/view/utils/async',
        'underscore',
        'Aheadworks_OneStepCheckout/js/model/render-postprocessor/float-label/converter',
        'awOscFloatLabel',
        'mage/template',
        'mage/translate'
    ],
    function ($, _, Converter, floatLabel, mageTemplate, $t) {
        'use strict';

        var ccMethodCodes = [
            'authorizenet_directpost',
            'braintree',
            'cybersource',
            'eway'
        ];

        return _.extend({}, Converter, {

            /**
             * @inheritdoc
             */
            convertPaymentMethodInputs: function (methodItem) {
                var form = methodItem.find('form'),
                    methodCode = this._getPaymentMethodCode(methodItem),
                    expDateContainer = '#' + methodCode + '_cc_type_exp_div';

                if (form.length > 0) {
                    floatLabel({excludeFieldSelector: expDateContainer}, form);
                    if (this._isCCMethod(methodCode, methodItem)) {
                        $.async(
                            expDateContainer,
                            $.proxy(this._processExpirationDateInput, this)
                        );
                    }
                }
            },

            /**
             * Check if CC payment method
             *
             * @param {string} methodCode
             * @param {Object} methodItem
             * @returns {boolean}
             */
            _isCCMethod: function (methodCode, methodItem) {
                var form = methodItem.find('form');

                return _.indexOf(methodCode, ccMethodCodes) != -1
                    || form.is('#co-transparent-form, #co-transparent-form-braintree');
            },

            /**
             * Retrieve payment method code from method item element
             *
             * @param {Object} element
             * @returns {string}
             */
            _getPaymentMethodCode: function (element) {
                var input = element.find('input[name="payment[method]"]');

                return input.val();
            },

            /**
             * Process expiration date inputs
             *
             * @param {HTMLElement} container
             */
            _processExpirationDateInput: function (container) {
                var expMonth = $(container).find('[id$=_expiration]'),
                    expYear = $(container).find('[id$=_expiration_yr]'),
                    fields = $(container).find('.field');

                $(container).find('label').remove();
                $(container).find('div.no-label').removeClass('no-label');
                if ($(container).hasClass('required')) {
                    fields.addClass('required');
                }
                this._addLabel(expMonth, 'Expiration Month', 'Exp. Month');
                this._addLabel(expYear, 'Expiration Year', 'Exp. Year');

                _.each([expMonth, expYear], function (element) {
                    element.find('option').each(function () {
                        if ($(this).val() == '') {
                            $(this).html('');
                        }
                    })
                });

                fields.each(function () {
                    floatLabel({}, this);
                });
            },

            /**
             * Add label for input element
             *
             * @param {Object} element
             * @param {string} label
             * @param {string} shortLabel
             */
            _addLabel: function (element, label, shortLabel) {
                var labelTemplate = mageTemplate(this.labelTmplStr + this.shortLabelTmplStr),
                    target = element.closest('div.control');

                target.before(labelTemplate({
                    data: {
                        for: element.attr('id'),
                        label: $t(label),
                        shortLabel: $t(shortLabel)
                    }
                }));
            }
        });
    }
);
