/*
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'underscore',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'Magento_Vault/js/view/payment/vault-enabler',
        'mage/translate',
        'jquery',
        'jquery/ui'
    ],
    function (_, Component, creditCardData, cardNumberValidator, VaultEnabler, $t, $) {
        function showHidePaymentFields() {
            var code = 'iwd_bluepay',
                paymentType = $("#" + code + "_payment_type");
            if (paymentType && paymentType.val() == 'ACH') {
                $("[id^= '" + code + "_cc']").hide();
                $("[id^= '" + code + "_echeck']").show();
            } else {
                $("[id^= '" + code + "_cc']").show();
                $("[id^= '" + code + "_echeck']").hide();
            }
        }

        return Component.extend({
            defaults: {
                creditCardType: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardNumber: '',
                creditCardSsStartMonth: '',
                creditCardSsStartYear: '',
                creditCardVerificationNumber: '',
                selectedCardType: null,
                echeckAccountType: 'C',
                echeckRoutingNumber: '',
                echeckAccountName: '',
                paymentType: 'CC',
                hashStoredAccount: '',
                saveInfo: true,
                vaultEnabler: null
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardVerificationNumber',
                        'creditCardSsStartMonth',
                        'creditCardSsStartYear',
                        'selectedCardType',
                        'echeckAccountType',
                        'echeckRoutingNumber',
                        'echeckAccountName',
                        'paymentType',
                        'hashStoredAccount',
                        'saveInfo'
                    ]);
                return this;
            },

            initialize: function () {
                var self = this;
                this._super();

                this.vaultEnabler = new VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());

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

                //this.paymentType = window.checkoutConfig.payment.iwd_bluepay.paymentTypes;
                this.paymentType = 'CC';

                this.hashStoredAccount.subscribe(function (hash) {
                    parseInt(hash) === 0 ? self.hashStoredAccount('') : self.hashStoredAccount(hash);
                });

                this.saveInfo.subscribe(function (value) {
                    creditCardData.saveInfo = value;
                });

            },

            getCode: function () {
                return 'iwd_bluepay';
            },
            getData: function () {
                var data, saveInfo = $("#iwd_bluepay_cc_stored_acct_cb");
                creditCardData.saveInfo = saveInfo.length && saveInfo.is(':checked');
                data = {
                    'method': this.item.method,
                    'additional_data': {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_ss_start_month': this.creditCardSsStartMonth(),
                        'cc_ss_start_year': this.creditCardSsStartYear(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_month': creditCardData.expirationMonth,
                        'cc_exp_year': creditCardData.expirationYear,
                        'cc_number': creditCardData.creditCardNumber,
                        'echeck_type': this.getPaymentType(),
                        'echeck_account_type': this.echeckAccountType(),
                        'echeck_account_name': this.echeckAccountName(),
                        'echeck_routing_number': this.echeckRoutingNumber(),
                        'hash': this.hashStoredAccount(),
                        'save_payment_info': creditCardData.saveInfo
                    }
                };

                this.vaultEnabler.visitAdditionalData(data);

                return data;
            },
            getCcAvailableTypes: function () {
                //return window.checkoutConfig.payment.ccform.availableTypes[this.getCode()];
                return window.checkoutConfig.payment.iwd_bluepay.availableTypes;
            },
            getIcons: function (type) {
                return window.checkoutConfig.payment.ccform.icons.hasOwnProperty(type)
                    ? window.checkoutConfig.payment.ccform.icons[type]
                    : false
            },
            getCcMonths: function () {
                //return window.checkoutConfig.payment.ccform.months[this.getCode()];
                return window.checkoutConfig.payment.iwd_bluepay.months;
            },
            getCcYears: function () {
                //return window.checkoutConfig.payment.ccform.years[this.getCode()];
                return window.checkoutConfig.payment.iwd_bluepay.years;
            },
            hasVerification: function () {
                //return window.checkoutConfig.payment.ccform.hasVerification[this.getCode()];
                return window.checkoutConfig.payment.iwd_bluepay.hasVerification;
            },
            hasSsCardType: function () {
                //return window.checkoutConfig.payment.ccform.hasSsCardType[this.getCode()];
                return window.checkoutConfig.payment.iwd_bluepay.hasSsCardType;
            },
            getCvvImageUrl: function () {
                //return window.checkoutConfig.payment.ccform.cvvImageUrl[this.getCode()];
                return window.checkoutConfig.payment.iwd_bluepay.cvvImageUrl;
            },
            getCvvImageHtml: function () {
                return '<img src="' + this.getCvvImageUrl()
                    + '" alt="' + $t('Card Verification Number Visual Reference')
                    + '" title="' + $t('Card Verification Number Visual Reference')
                    + '" />';
            },
            getSsStartYears: function () {
                //return window.checkoutConfig.payment.ccform.ssStartYears[this.getCode()];
                return window.checkoutConfig.payment.iwd_bluepay.ssStartYears;
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
            getSsStartYearsValues: function () {
                return _.map(this.getSsStartYears(), function (value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },
            isShowLegend: function () {
                return false;
            },
            getCcTypeTitleByCode: function (code) {
                var title = '';
                _.each(this.getCcAvailableTypesValues(), function (value) {
                    if (value['value'] == code) {
                        title = value['type'];
                    }
                });
                return title;
            },
            formatDisplayCcNumber: function (number) {
                return 'xxxx-' + number.substr(-4);
            },
            getInfo: function () {
                return [
                    {'name': $t('Credit Card Type'), value: this.getCcTypeTitleByCode(this.creditCardType())},
                    {'name': $t('Credit Card Number'), value: this.formatDisplayCcNumber(this.creditCardNumber())}
                ];
            },
            getPaymentTypes: function () {
                return window.checkoutConfig.payment.iwd_bluepay.paymentTypes;
            },
            getStoredAccounts: function () {
                var storedAccounts = window.checkoutConfig.payment.iwd_bluepay.storedAccounts;
                return storedAccounts && storedAccounts.length ? storedAccounts : null;
            },
            getPaymentType: function () {
                if ($("#iwd_bluepay_payment_type")) {
                    this.paymentType = $("#iwd_bluepay_payment_type").val();
                }
                return this.paymentType;
            },
            showHidePaymentFields: function () {
                showHidePaymentFields();
            },

            /**
             * @returns {Boolean}
             */
            isCustomerLoggedIn: function() {
                try {
                    return window.checkoutConfig.payment.iwd_bluepay.isCustomerLoggedIn;
                } catch (e) {
                    return true;
                }
            },

            /**
             * @returns {Boolean}
             */
            allowAccountsStorage: function () {
                try {
                    return window.checkoutConfig.payment.iwd_bluepay.allowAccountsStorage;
                } catch (e) {
                    return false;
                }
            },

            initPaymentFields: function () {
                var paymentType = window.checkoutConfig.payment.iwd_bluepay.paymentTypes;
                if (paymentType === 'CC' || paymentType === 'ACH') {
                    $("#iwd_bluepay_payment_type").val(paymentType);
                    $("#iwd_bluepay_payment_type_div").hide();
                }
                showHidePaymentFields();
            },

            isShowPaymentType: function () {
                return window.checkoutConfig.payment.iwd_bluepay.isShowPaymentType;
            },
            /**
             * Check hash exist in config
             */
            isStoredAccountSelected: function () {
                return this.hashStoredAccount();
            },

            forceSaveInVault: function () {
                return window.checkoutConfig.payment.iwd_bluepay.forceSaveInVault;
            },

            forceSaveInVaultMessage: function () {
                return window.checkoutConfig.payment.iwd_bluepay.forceSaveInVaultMessage;
            },

            isInternalVaultEnabled: function () {
                return this.isCustomerLoggedIn() && window.checkoutConfig.payment.iwd_bluepay.isInternalVaultEnabled;
            },

            /**
             * @returns {Boolean}
             */
            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            },

            /**
             * @returns {String}
             */
            getVaultCode: function () {
                return window.checkoutConfig.payment[this.getCode()].vaultCode;
            }
        });
    }
);