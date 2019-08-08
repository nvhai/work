/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Magento_Ui/js/lib/view/utils/async',
        'underscore',
        'Aheadworks_OneStepCheckout/js/model/render-postprocessor/float-label/converter/cc',
        'Aheadworks_OneStepCheckout/js/view/payment-method/renderer/braintree/hosted-fields/float-label',
        'mage/template',
        'mage/translate'
    ],
    function ($, _, Converter, floatLabel, mageTemplate, $t) {
        'use strict';

        return _.extend({}, Converter, {
            expMonthSelector: '[id$=_expirationMonth]',
            expYearSelector: '[id$=_expirationYear]',

            /**
             * @inheritdoc
             */
            convertPaymentMethodInputs: function (methodItem) {
                var self = this,
                    form = methodItem.find('form');

                if (form.length > 0) {
                    form.find('div.field').each(function () {
                        var field = $(this),
                            isExpDateContainer = field.find(self.expMonthSelector).length > 0
                                && field.find(self.expYearSelector).length > 0;

                        if (!field.hasClass('type') && !field.hasClass('choice')) {
                            if (isExpDateContainer) {
                                self._processExpirationDateInput(field);
                            } else {
                                floatLabel({}, field);
                            }
                        }
                    });
                }
            },

            /**
             * @inheritdoc
             */
            _processExpirationDateInput: function (container) {
                var expMonthContainer = container.find(this.expMonthSelector),
                    expYearContainer = container.find(this.expYearSelector);

                $(container).find('label').remove();
                this._addLabel(expMonthContainer, 'Expiration Month', 'Exp. Month');
                this._addLabel(expYearContainer, 'Expiration Year', 'Exp. Year');

                floatLabel(
                    {fieldSelector: [this.expMonthSelector, this.expYearSelector].join(', ')},
                    container
                );
            },

            /**
             * @inheritdoc
             */
            _addLabel: function (element, label, shortLabel) {
                var templateString = shortLabel
                        ? this.labelTmplStr + this.shortLabelTmplStr
                        : this.labelTmplStr,
                    labelTemplate = mageTemplate(templateString);

                element.prepend(labelTemplate({
                    data: {
                        for: element.attr('id'),
                        label: $t(label),
                        shortLabel: shortLabel ? $t(shortLabel) : ''
                    }
                }));
            }
        });
    }
);
