(function(){

    var sellerId = $("#readOnlyWalletWidgetDiv").data("sellerid");
    var amazonOrderReferenceId = $("#readOnlyWalletWidgetDiv").data("amazonorderreferenceid");
    var errorURL = $("#readOnlyWalletWidgetDiv").data("errorurl");

    if(typeof sellerId!='undefined' && typeof errorURL!='undefined' && typeof amazonOrderReferenceId!='undefined'){

        new OffAmazonPayments.Widgets.Wallet({
            sellerId: sellerId,
            amazonOrderReferenceId: amazonOrderReferenceId,
            // amazonOrderReferenceId obtained from Button widget
            displayMode: "Read",
            design: {
                designMode: 'responsive',
                padding: '8'
            },
            onError: function(error) {
                document.location.href = errorURL+'&error=' + encodeURIComponent(error.getErrorMessage()) + '&errorCode=' + encodeURIComponent(error.getErrorCode())
            }
        }).bind("readOnlyWalletWidgetDiv");
    }
})();