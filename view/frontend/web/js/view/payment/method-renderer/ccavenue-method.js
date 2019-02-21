define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Customer/js/model/customer',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (Component, customer, url, fullScreenLoader) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magedev_Ccavenue/payment/ccavenue'
            },
            redirectAfterPlaceOrder: false,
            getTitle:function(){
                return window.checkoutConfig.payment.ccavenue.title;
            },

            getData: function () {
                return {
                    'method': this.getCode()
                };
            },
            afterPlaceOrder: function () {
                fullScreenLoader.startLoader();
                window.location.replace(url.build('ccavenue/request/place/'));
            }
        });
    }
);