6. Now overwrite https://github.com/magento/magento2/blob/2.1.9/app/code/Magento/Checkout/view/frontend/web/js/view/payment.js file to stop Payment Page Step before our Billing Address Step

Create File Webkul/BillingStep/view/frontend/requirejs-config.js



var config = {
    map: {
        '*': {
            "Magento_Checkout/js/view/payment": "Webkul_BillingStep/js/view/payment"
        }
    }
};




7. Finally copy https://github.com/magento/magento2/tree/2.1.9/app/code/Magento/Checkout/view/frontend/web/js/view/payment directory and place in our Billing Step Module
Webkul/BillingStep/view/frontend/web/js/view