var amazonOrderReferenceId;

(function($, exports) {

    exports.chameleon_system_amazon_payment = exports.chameleon_system_amazon_payment || {};

    exports.chameleon_system_amazon_payment.init = function() {
        var paymentDiv = $(".payWithAmazonDiv");
        if(jQuery.isFunction(paymentDiv.tooltip)) {
            paymentDiv.find("img").tooltip({"placement":'bottom'});
        }

        paymentDiv.each(function(index, element) {

            var elementId = "payWithAmazonDiv" + index;
            element.id = elementId;

            var sellerId = element.dataset.sellerid;
            var payWithAmazonURL = element.dataset.paywithamazonurl;
            var payWithAmazonURLError = element.dataset.paywithamazonurlerror;

            if( typeof OffAmazonPayments != 'undefined'
                && typeof sellerId != 'undefined'
                && typeof payWithAmazonURL != 'undefined'
                && typeof payWithAmazonURLError != 'undefined'){

                new OffAmazonPayments.Widgets.Button({
                    sellerId: sellerId,
                    onSignIn: function(orderReference) {
                        amazonOrderReferenceId = orderReference.getAmazonOrderReferenceId();
                        window.location = payWithAmazonURL+'&amazonOrderReferenceId='
                        + encodeURIComponent(amazonOrderReferenceId);
                    },
                    onError: function(error) {
                        window.location = payWithAmazonURLError+'&error=' + encodeURIComponent(error.getErrorMessage()) + '&errorCode=' + encodeURIComponent(error.getErrorCode())
                    }
                }).bind(elementId);
            }
        });

    };

})(jQuery, window);

$(document).ready(function () {
    if(window.chameleon_system_amazon_payment) {
        window.chameleon_system_amazon_payment.init();

        var CHAMELEON = window.CHAMELEON || {};
        $(CHAMELEON).on("chameleon_system_shop.product_added", window.chameleon_system_amazon_payment.init);
    }
});