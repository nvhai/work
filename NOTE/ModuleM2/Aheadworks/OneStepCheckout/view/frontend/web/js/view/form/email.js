/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/action/check-email-availability',
    'Magento_Customer/js/action/login',
    'Magento_Checkout/js/model/quote',
    'Aheadworks_OneStepCheckout/js/model/checkout-data',
    'Aheadworks_OneStepCheckout/js/model/newsletter/subscriber',
    'Aheadworks_OneStepCheckout/js/action/check-if-subscribed-by-email',
    'Magento_Checkout/js/model/full-screen-loader',
    'Aheadworks_OneStepCheckout/js/model/checkout-data-completeness-logger',
    'mage/validation'
], function (
    $,
    Component,
    ko,
    customer,
    checkEmailAvailabilityAction,
    loginAction,
    quote,
    checkoutData,
    newsletterSubscriber,
    checkIfSubscribedByEmailAction,
    fullScreenLoader,
    completenessLogger
) {
    'use strict';

    var validatedEmail = checkoutData.getValidatedEmailValue(),
        newsletterSubscribeConfig = window.checkoutConfig.newsletterSubscribe,
        verifiedIsSubscribed = checkoutData.getVerifiedIsSubscribedFlag();

    if (validatedEmail && !customer.isLoggedIn()) {
        quote.guestEmail = validatedEmail;
        if (newsletterSubscribeConfig.isGuestSubscriptionsAllowed) {
            newsletterSubscriber.subscriberEmail = validatedEmail;
            if (verifiedIsSubscribed !== undefined) {
                newsletterSubscriber.isSubscribed(verifiedIsSubscribed);
                newsletterSubscriber.subscribedStatusVerified(true);
            }
        }
    }

    return Component.extend({
        defaults: {
            template: 'Aheadworks_OneStepCheckout/form/email',
            email: checkoutData.getInputFieldEmailValue(),
            emailFocused: false,
            isLoading: false,
            isPasswordVisible: false,
            listens: {
                email: 'emailHasChanged',
                emailFocused: 'validateEmail'
            }
        },
        checkDelay: 2000,
        checkAvailabilityRequest: null,
        checkIfSubscribedRequest: null,
        isCustomerLoggedIn: customer.isLoggedIn,
        forgotPasswordUrl: window.checkoutConfig.forgotPasswordUrl,
        emailCheckTimeout: 0,

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            completenessLogger.bindField('email', this.email);
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super()
                .observe([
                    'email',
                    'emailFocused',
                    'isLoading',
                    'isPasswordVisible'
                ]);

            return this;
        },

        /**
         * Process email value change
         */
        emailHasChanged: function () {
            var self = this;

            clearTimeout(this.emailCheckTimeout);

            if (self.validateEmail()) {
                quote.guestEmail = self.email();
                newsletterSubscriber.subscriberEmail = self.email();
                checkoutData.setValidatedEmailValue(self.email());
            }
            this.emailCheckTimeout = setTimeout(function () {
                if (self.validateEmail()) {
                    self.checkEmailAvailability();
                    if (newsletterSubscribeConfig.isGuestSubscriptionsAllowed) {
                        self.checkIfSubscribedByEmail();
                    }
                } else {
                    self.isPasswordVisible(false);
                    newsletterSubscriber.subscribedStatusVerified(false);
                }
            }, self.checkDelay);

            checkoutData.setInputFieldEmailValue(self.email());
        },

        /**
         * Check email availability
         */
        checkEmailAvailability: function () {
            var self = this,
                isEmailCheckComplete = $.Deferred();

            this._validateRequest(this.checkAvailabilityRequest);
            this.isLoading(true);
            this.checkAvailabilityRequest = checkEmailAvailabilityAction(isEmailCheckComplete, this.email());

            $.when(isEmailCheckComplete).done(function () {
                self.isPasswordVisible(false);
            }).fail(function () {
                self.isPasswordVisible(true);
            }).always(function () {
                self.isLoading(false);
            });
        },

        /**
         * Check if subscribed by email
         */
        checkIfSubscribedByEmail: function () {
            var isEmailCheckComplete = $.Deferred();

            this._validateRequest(this.checkIfSubscribedRequest);
            this.checkIfSubscribedRequest = checkIfSubscribedByEmailAction(isEmailCheckComplete, this.email());

            $.when(isEmailCheckComplete).done(function () {
                newsletterSubscriber.isSubscribed(true);
                checkoutData.setVerifiedIsSubscribedFlag(true);
            }).fail(function () {
                newsletterSubscriber.isSubscribed(false);
                checkoutData.setVerifiedIsSubscribedFlag(false);
            }).always(function () {
                newsletterSubscriber.subscribedStatusVerified(true);
            });
        },

        /**
         * If request has been sent abort it
         *
         * @param {XMLHttpRequest} request
         */
        _validateRequest: function (request) {
            if (request != null && $.inArray(request.readyState, [1, 2, 3])) {
                request.abort();
                request = null;
            }
        },

        /**
         * Local email validation
         *
         * @param {Boolean} focused
         * @returns {Boolean}
         */
        validateEmail: function (focused) {
            var loginFormSelector = 'form[data-role=email-with-possible-login]',
                usernameSelector = loginFormSelector + ' input[name=username]',
                loginForm = $(loginFormSelector),
                validator;

            loginForm.validation();
            if (focused === false && !!this.email()) {
                return !!$(usernameSelector).valid();
            }
            validator = loginForm.validate();

            return validator.check(usernameSelector);
        },

        /**
         * Perform login action
         *
         * @param {Object} loginForm
         */
        login: function (loginForm) {
            var loginData = {},
                formDataArray = $(loginForm).serializeArray();

            $.each(formDataArray, function () {
                loginData[this.name] = this.value;
            });

            if (this.isPasswordVisible()
                && $(loginForm).validation()
                && $(loginForm).validation('isValid')
            ) {
                fullScreenLoader.startLoader();
                loginAction(loginData).always(function() {
                    fullScreenLoader.stopLoader();
                });
            }
        }
    });
});
