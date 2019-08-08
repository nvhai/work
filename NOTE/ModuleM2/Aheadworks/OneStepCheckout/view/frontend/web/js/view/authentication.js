/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'Magento_Ui/js/form/form',
        'Magento_Customer/js/action/login',
        'Magento_Customer/js/model/customer',
        'mage/validation',
        'Magento_Checkout/js/model/authentication-messages',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function(
        $,
        Component,
        loginAction,
        customer,
        validation,
        messageContainer,
        fullScreenLoader
    ) {
        'use strict';
        var checkoutConfig = window.checkoutConfig;

        return Component.extend({
            defaults: {
                template: 'Aheadworks_OneStepCheckout/authentication'
            },
            isCustomerLoggedIn: customer.isLoggedIn,
            forgotPasswordUrl: checkoutConfig.forgotPasswordUrl,
            autocomplete: checkoutConfig.autocomplete,

            /**
             * Perform login action
             *
             * @param {Object} loginForm
             */
            login: function(loginForm) {
                var loginData = {},
                    formDataArray = $(loginForm).serializeArray();

                $.each(formDataArray, function () {
                    loginData[this.name] = this.value;
                });

                if ($(loginForm).validation() && $(loginForm).validation('isValid')) {
                    fullScreenLoader.startLoader();
                    loginAction(
                        loginData,
                        checkoutConfig.checkoutUrl,
                        undefined,
                        messageContainer
                    ).always(function() {
                        fullScreenLoader.stopLoader();
                    });
                }
            }
        });
    }
);
