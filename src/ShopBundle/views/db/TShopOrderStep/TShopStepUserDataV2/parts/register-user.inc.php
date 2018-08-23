<?php
/** @var $oUserData TdbDataExtranetUser */
/** @var $oShippingAddress TdbDataExtranetUserAddress */
/** @var $oBillingAddress TdbDataExtranetUserAddress */
/** @var $bShipToBillingAddress tinyint */
?>
<?php
// render shipping address
echo $oShippingAddress->Render('form', 'Core', array('sAddressName' => TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING), false);
// render user data
echo $oUserData->Render('form-basket-register', 'Core');

include dirname(__FILE__).'/ChangeShipToBillingAddress.inc.php';
if ('1' != $bShipToBillingAddress) {
    // render billing address
    echo $oBillingAddress->Render('form', 'Core', array('sAddressName' => TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING), false);
}
