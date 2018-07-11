(function(){
    var sellerId = $("#walletWidgetDiv").data("sellerid");
    var amazonOrderReferenceId = $("#walletWidgetDiv").data("amazonorderreferenceid");
    var errorURL = $("#walletWidgetDiv").data("errorurl");

    if(typeof sellerId!='undefined' && typeof errorURL!='undefined' && typeof amazonOrderReferenceId!='undefined'){

        var widgetDefaultDesign = {
            size: {
                width: '600px',
                height: '260px'
            }
        };

        var screenSizeWidgetSizeMap = [
            ['(min-width:1200px)', {
                size: {
                    width: '570px',
                    height: '260px'
                }
            }],
            ['(min-width:768px) and (max-width:979px)', {
                size: {
                    width: '440px',
                    height: '300px'
                }
            }],
            ['(max-width:767px)', {
                designMode: 'smartphoneCollapsible',
                padding: '8'
            }]
        ];
        var widgetDesign = {};
        $.map(screenSizeWidgetSizeMap, function(el){

            if(window.matchMedia(el[0]).matches){
                widgetDesign = el[1]
            }

        });

        if($.isEmptyObject(widgetDesign)){
            widgetDesign = widgetDefaultDesign;
        }

        new OffAmazonPayments.Widgets.Wallet({
            sellerId: sellerId,
            amazonOrderReferenceId: amazonOrderReferenceId,
            // amazonOrderReferenceId obtained from Button widget
            design: widgetDesign,

            onPaymentSelect: function(orderReference) {
                $('#primarypaymentbutton').removeAttr("disabled");
            },
            onError: function(error) {
                document.location.href = errorURL+'&error=' + encodeURIComponent(error.getErrorMessage()) + '&errorCode=' + encodeURIComponent(error.getErrorCode())
            }
        }).bind("walletWidgetDiv");
    }
})();