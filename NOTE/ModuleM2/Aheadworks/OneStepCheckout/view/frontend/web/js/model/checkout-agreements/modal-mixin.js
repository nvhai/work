/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, _, modal, $t) {
    'use strict';

    return function (agreementModal) {
        return _.extend(agreementModal, {

            /**
             * Create popup window
             *
             * @param {Element} element
             */
            createModal: function(element) {
                this.modalWindow = element;
                var options = {
                    'type': 'popup',
                    'modalClass': 'agreements-modal',
                    'responsive': true,
                    'innerScroll': true,
                    'trigger': '.show-modal',
                    'title': $t('Terms and Conditions'),
                    'buttons': []
                };
                modal(options, $(this.modalWindow));
            }
        });
    }
});
