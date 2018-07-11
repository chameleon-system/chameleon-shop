<?php

/** @var $oShop TdbShop */
/** @var $oUser TdbDataExtranetUser */
/** @var $oExtranetConfig TdbDataExtranet */
/** @var $oShippingGroupList TShopShippingGroupList */
/** @var $oActiveShippingGroup TShopShippingGroup */
/** @var $oPaymentMethods TdbShopPaymentMethodList */
/** @var $oActivePaymentMethod TdbShopPaymentMethod */

/** @var $oStep TdbShopOrderStep */
/** @var $sSpotName string */
/** @var $aCallTimeVars array */

/** @var $oStepNext TdbShopOrderStep */
/** @var $oStepPrevious TdbShopOrderStep */
/** @var $sBackLink string */

$oViewRenderer = new ViewRenderer();
$oViewRenderer->addMapperFromIdentifier('chameleon_system_shop.mapper.shipping_group.shipping_group_list');
$oViewRenderer->addMapperFromIdentifier('chameleon_system_shop.mapper.orderwizard.payment_list');
$oViewRenderer->addMapperFromIdentifier('chameleon_system_shop_currency.mapper.shop_currency_mapper');
$oViewRenderer->AddSourceObject('oShippingGroupList', $oShippingGroupList);
$oViewRenderer->AddSourceObject('oActiveShippingGroup', $oActiveShippingGroup);
$oViewRenderer->AddSourceObject('oPaymentMethodList', $oPaymentMethods);
$oViewRenderer->AddSourceObject('oActivePaymentMethod', $oActivePaymentMethod);

$oViewRenderer->addMapperFromIdentifier('chameleon_system_shop.mapper.orderwizard.order_step');
$oViewRenderer->AddSourceObject('sBackLink', $sBackLink);

echo $oViewRenderer->Render('/pkgShop/shopBasket/shopBasketCheckoutShippingAndPayment.html.twig');
