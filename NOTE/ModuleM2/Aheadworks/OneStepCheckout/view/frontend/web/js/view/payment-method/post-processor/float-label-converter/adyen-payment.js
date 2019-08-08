/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'underscore',
        'uiComponent',
        'Aheadworks_OneStepCheckout/js/model/render-postprocessor/float-label/converters-pool',
        'Aheadworks_OneStepCheckout/js/model/render-postprocessor/float-label/converter/adyen-payment/cc',
        'Aheadworks_OneStepCheckout/js/model/render-postprocessor/float-label/converter/adyen-payment/oneclick'
    ],
    function (_, Component, convertersPool, ccConverter, oneClickConverter) {
        'use strict';

        /**
         * Get adyen oneclick payment method codes
         *
         * @returns {Array}
         */
        function getOneClickMethodCodes() {
            return _.map(window.checkoutConfig.payment.adyenOneclick.billingAgreements, function (billingAgreement) {
                return billingAgreement.reference_id;
            });
        }

        convertersPool.register('adyen_cc', ccConverter);
        _.each(getOneClickMethodCodes(), function (methodCode) {
            convertersPool.register(methodCode, oneClickConverter);
        });

        return Component.extend({});
    }
);
