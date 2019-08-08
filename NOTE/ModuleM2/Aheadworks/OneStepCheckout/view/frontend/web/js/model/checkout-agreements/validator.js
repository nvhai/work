/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'mage/validation'
    ],
    function ($) {
        'use strict';

        var checkoutConfig = window.checkoutConfig,
            agreementsConfig = checkoutConfig ? checkoutConfig.checkoutAgreements : {};

        return {
            agreementsForm: '[data-role=checkout-agreements-form]',
            agreementsInput: '[data-role=checkout-agreements-input]',

            /**
             * Validate checkout agreements
             *
             * @returns {boolean}
             */
            validate: function() {
                var input = $(this.agreementsForm + ' ' + this.agreementsInput);

                if (agreementsConfig.isEnabled && input.length > 0) {
                    return $(this.agreementsForm).validate({
                        errorClass: 'mage-error',
                        errorElement: 'div',
                        meta: 'validate',
                        ignore: '[type=hidden]',
                        errorPlacement: function (error, element) {
                            var errorPlacement = element;

                            if (element.is(':checkbox') || element.is(':radio')) {
                                errorPlacement = element.siblings('label').last();
                            }
                            errorPlacement.after(error);
                        }
                    }).element(this.agreementsInput);
                }

                return true;
            }
        }
    }
);
