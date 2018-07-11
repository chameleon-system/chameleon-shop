    (function(){

        var sellerId = $("#addressBookWidgetDiv").data("sellerid");
        var amazonOrderReferenceId = $("#addressBookWidgetDiv").data("amazonorderreferenceid");
        var errorURL = $("#addressBookWidgetDiv").data("errorurl");

        if(typeof sellerId!='undefined' && typeof errorURL!='undefined' && typeof amazonOrderReferenceId!='undefined'){

            var widgetDefaultDesign = {
                size: {
                    width: '570px',
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
                ['(min-width:980px) and (max-width: 1199px)', {
                    size: {
                        width: '460',
                        height: '300px'
                    }
                }],
                ['(min-width:768px) and (max-width:979px)', {
                    size: {
                        width: '352px',
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

            new OffAmazonPayments.Widgets.AddressBook({
                sellerId: sellerId,
                amazonOrderReferenceId: amazonOrderReferenceId,
                design: widgetDesign,
                onAddressSelect: function(orderReference) {
                    $('#primarypaymentbutton').removeAttr("disabled");
                },
                onError: function(error) {
                    document.location.href = errorURL+'&error=' + encodeURIComponent(error.getErrorMessage()) + '&errorCode=' + encodeURIComponent(error.getErrorCode())
                }
            }).bind("addressBookWidgetDiv");
        }

    })();
