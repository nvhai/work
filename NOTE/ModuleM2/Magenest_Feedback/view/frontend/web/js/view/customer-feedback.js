define([
    'uiComponent',
    'ko',
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/backend/tabs',
    'Magento_Ui/js/modal/modal'
], function(uiComponent, ko, $, alert) {
    'use strict';

    function ProductReview(data){
        var self = this;

        self.order_id = data.order_id;
        self.product_id = data.product_id;
        self.name = data.name;
        self.customer_id = data.customer_id;
        self.image = data.image;
        self.message = ko.observable(data.message);
        self.comment_status = ko.observable(data.comment_status);
        
        self.submitReview = function () {
            var data = {
                'order_id': self.order_id,
                'product_id': self.product_id,
                'customer_id': self.customer_id,
                'message': self.message(),
                'status': self.comment_status()
            };
            $.ajax({
                url: '/feedback/customer/submitreview',
                data: data,
                type: 'post',
                dataType: 'json',
                context: this,
                success: function (response) {
                    if (response.status === true){
                        alert({
                            content: $.mage.__('Thanks for Submitting.')
                        });
                        self.comment_status(true);
                    }
                },
            });
        }
    };

    return uiComponent.extend({
        defaults: {
            template: 'Magenest_Feedback/customer-feedback'
        },


        initialize: function (config) {
            var products = [];
            $.each(config.listProducts, function (index, product) {
                products.push(new ProductReview(product));
            });
            this.products = products;
            this.product = ko.observable();

            this._super();
        },

        initTabs: function () {
            $('#horizontal_tabs').tabs();
        },

        initModal: function(){
            var self = this;
            $('#feedback-form').modal({
                buttons: [{
                    text: 'Send Review',
                    click: function() {
                        self.product().submitReview();
                        this.closeModal();
                    }
                }]
            });
        },
        
        openReviewForm: function (product, data) {
            this.product(product);
            $('#feedback-form').modal('openModal');
        }
        
    });
});