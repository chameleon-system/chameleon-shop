(function(){

    var sellerId = $("#readOnlyAddressBookWidgetDiv").data("sellerid");
    var amazonOrderReferenceId = $("#readOnlyAddressBookWidgetDiv").data("amazonorderreferenceid");
    var errorURL = $("#readOnlyAddressBookWidgetDiv").data("errorurl");

    if(typeof sellerId!='undefined' && typeof errorURL!='undefined' && typeof amazonOrderReferenceId!='undefined'){

        new OffAmazonPayments.Widgets.AddressBook({
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
        }).bind("readOnlyAddressBookWidgetDiv");
    }
})();