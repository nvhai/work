/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Magento_Ui/js/lib/view/utils/async',
        'underscore',
        'Aheadworks_OneStepCheckout/js/model/render-postprocessor/float-label/converter/cc',
        'awOscFloatLabel'
    ],
    function ($, _, Converter, floatLabel) {
        'use strict';

        return _.extend({}, Converter, {

            /**
             * @inheritdoc
             */
            convertPaymentMethodInputs: function (methodItem) {
                var self = this,
                    methodCode = this._getPaymentMethodCode(methodItem),
                    expMonthSelector = '#' + methodCode + '_expiration',
                    expYearSelector = '#' + methodCode + '_expiration_yr',
                    fields;

                $.async('fieldset#payment_form_' + methodCode, function (fieldSet) {
                    fields = $(fieldSet).find('div.field')
                        .filter(':not(.no-label)')
                        .filter(':not(.type)');

                    fields.each(function () {
                        var field = $(this),
                            isExpDateContainer = field.find(expMonthSelector).length > 0
                                && field.find(expYearSelector).length > 0;

                        if (isExpDateContainer) {
                            self._processExpirationDateInput(field);
                        } else {
                            floatLabel({}, field);
                        }
                    });
                });
            }
        });
    }
);
