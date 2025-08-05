TPkgShopPaymentTransaction_showForm = function(data, status) {
    console.log('oben');
    var mynode = $(data);
    $('body').append(mynode);
    $('#pkgShopPaymentTransaction-form').dialog(
        {
            width:750,
            height:550
        }
    );
};

TPkgShopPaymentTransaction_closeForm = function() {
    console.log('unten');
    var form = $('#pkgShopPaymentTransaction-form');
    if (form.length) {
        form.remove();
    }
};