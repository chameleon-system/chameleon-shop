<?php

/** @var $oUserData TdbDataExtranetUser */
/** @var $oShippingAddress TdbDataExtranetUserAddress */
/** @var $oBillingAddress TdbDataExtranetUserAddress */
/** @var $bShipToBillingAddress bool */

$oShippingAddressList = $oUserData->GetFieldDataExtranetUserAddressList();

$aShippingParams = array('sAddressName' => TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING, 'selectedAddressId' => $oShippingAddress->id);
echo $oShippingAddressList->Render('select-address', 'Customer', $aShippingParams);
// render shipping address
echo $oShippingAddress->Render('form', 'Customer', $aShippingParams, false);

include dirname(__FILE__).'/ChangeShipToBillingAddress.inc.php';

if ('1' != $bShipToBillingAddress) {
    // render billing address
    $aBillingParams = array('sAddressName' => TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING, 'selectedAddressId' => $oBillingAddress->id);
    $oBillingAddressList = $oUserData->GetFieldDataExtranetUserAddressList();
    echo $oBillingAddressList->Render('select-address', 'Customer', $aBillingParams);
    echo $oBillingAddress->Render('form', 'Customer', $aBillingParams, false);
}
