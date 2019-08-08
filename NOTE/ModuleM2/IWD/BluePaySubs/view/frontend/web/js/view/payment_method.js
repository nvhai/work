/*
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'underscore',
        'uiComponent',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'mage/translate',
        'jquery',
        'jquery/ui',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function (_, Component, creditCardData, cardNumberValidator, $t, $) {

        (function ($) {
            $.fn.selectRange = function (start, end) {
                if (end === undefined) {
                    end = start;
                }
                return this.each(function () {
                    if ('selectionStart' in this) {
                        this.selectionStart = start;
                        this.selectionEnd = end;
                    } else if (this.setSelectionRange) {
                        this.setSelectionRange(start, end);
                    } else if (this.createTextRange) {
                        var range = this.createTextRange();
                        range.collapse(true);
                        range.moveEnd('character', end);
                        range.moveStart('character', start);
                        range.select();
                    }
                });
            };
        })($); // select range

        return Component.extend({
            defaults: {
                formId: 'subs_payment_method',
                creditCardType: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardNumber: '',
                creditCardVerificationNumber: '',
                selectedCardType: null,
                availableTypes: {},
                months: {},
                years: {},
                paymentType: 'CC',
                storedAccounts: {},
                hashStoredAccount: ''
            },

            bindSubmit: function () {
                var self = this;

                $('#' + this.formId).on('submit', function (e) {
                    e.preventDefault();
                    self.submitForm($(this));
                });
            },

            /**
             * Handler for the form 'submit' event
             *
             * @param {Object} form
             */
            submitForm: function (form) {

                var self = this;

                form.submit();
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardVerificationNumber',
                        'selectedCardType',
                        'hashStoredAccount'
                    ]);
                return this;
            },

            initialize: function () {
                var self = this;
                this._super();
                // this.bindSubmit();
                $(document).on('input', '[name="payment[cc_number]"]', function () {
                    self.formatCcNumber($(this), self);
                    // var v = $(this).val().replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                    // self.fillCcNumberField($(this), v);
                });

                //Set credit card number to credit card data object
                this.creditCardNumber.subscribe(function (value) {
                    var result;
                    self.selectedCardType(null);


                    if (value == '' || value == null) {
                        return false;
                    }
                    result = cardNumberValidator(value);

                    if (!result.isPotentiallyValid && !result.isValid) {
                        return false;
                    }
                    if (result.card !== null) {
                        self.selectedCardType(result.card.type);
                        creditCardData.creditCard = result.card;
                    }

                    if (result.isValid) {
                        creditCardData.creditCardNumber = value;
                        self.creditCardType(result.card.type);
                    }
                });

                //Set expiration year to credit card data object
                this.creditCardExpYear.subscribe(function (value) {
                    creditCardData.expirationYear = value;
                });

                //Set expiration month to credit card data object
                this.creditCardExpMonth.subscribe(function (value) {
                    creditCardData.expirationMonth = value;
                });

                //Set cvv code to credit card data object
                this.creditCardVerificationNumber.subscribe(function (value) {
                    creditCardData.cvvCode = value;
                });

                this.hashStoredAccount.subscribe(function (hash) {
                    parseInt(hash) === 0 ? self.hashStoredAccount('') : self.hashStoredAccount(hash);
                });

            },

            getCode: function () {
                return 'payment';
            },
            getData: function () {
                var data;
                data = {
                    'method': this.item.method,
                    'payment': {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_month': creditCardData.expirationMonth,
                        'cc_exp_year': creditCardData.expirationYear,
                        'cc_number': creditCardData.creditCardNumber,
                        'hash': this.hashStoredAccount()
                    }
                };

                return data;
            },
            getCcAvailableTypes: function () {
                return this.availableTypes;
            },

            getCcMonths: function () {
                return this.months;
            },

            getCcYears: function () {
                return this.years;
            },

            getCcAvailableTypesValues: function () {
                return _.map(this.getCcAvailableTypes(), function (value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },

            getCcMonthsValues: function () {
                return _.map(this.getCcMonths(), function (value, key) {
                    if (key < 10) {
                        key = '0' + key;
                    }
                    return {
                        'value': key,
                        'month': value
                    }
                });
            },

            getCcYearsValues: function () {
                return _.map(this.getCcYears(), function (value, key) {
                    return {
                        'value': key.substring(2, 4),
                        'year': value
                    }
                });
            },

            formatCcNumber: function (element, e) {
                var value = element.val();
                if (value) {
                    var clear_value = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                    var formatted_value = clear_value;
                    var cursorPosition = element.prop('selectionStart');
                    var matches = clear_value.match(/\d{4,24}/g);
                    var match = matches && matches[0] || '';
                    var parts = [];
                    for (var i = 0; i < match.length; i += 4) {
                        parts.push(match.substring(i, i + 4))
                    }

                    if (parts.length) {
                        formatted_value = parts.join(' ');
                        var test_value = value.substring(0, cursorPosition);
                        var test_formatted_value = formatted_value.substring(0, cursorPosition);
                        var test_value_length = test_value.match(/ /g) ? test_value.match(/ /g).length : 0;
                        var test_formatted_value_length = test_formatted_value.match(/ /g) ? test_formatted_value.match(/ /g).length : 0;
                        if (test_formatted_value_length > test_value_length) {
                            cursorPosition = cursorPosition + test_formatted_value_length - test_value_length;
                        }
                    }

                    if ((cursorPosition % 5) === 0) {
                        cursorPosition = cursorPosition - 1;
                    }

                    element.val(formatted_value);
                    element.selectRange(cursorPosition);
                }
            },

            selectRange : function (start, end) {
                if (end === undefined) {
                    end = start;
                }
                return $.each(function () {
                    if ('selectionStart' in this) {
                        this.selectionStart = start;
                        this.selectionEnd = end;
                    } else if (this.setSelectionRange) {
                        this.setSelectionRange(start, end);
                    } else if (this.createTextRange) {
                        var range = this.createTextRange();
                        range.collapse(true);
                        range.moveEnd('character', end);
                        range.moveStart('character', start);
                        range.select();
                    }
                });
            },

            getStoredAccounts: function () {
                return this.storedAccounts;
            }
        });
    }
);