<?php

/** @var $oListItem TPkgShopListfilterItemPrice */
$options = $oListItem->GetOptions();
if (count($options) > 0) {
    $oViewRenderer = new ViewRenderer();
    $oViewRenderer->addMapperFromIdentifier('chameleon_system_shop_currency.mapper.shop_currency_mapper');
    $oViewRenderer->AddMapper(new TPkgShopListfilterMapper_FilterNumericSlider());
    $oViewRenderer->AddSourceObject('oFilterItem', $oListItem);
    $oViewRenderer->AddSourceObject('oActiveFilter', TdbPkgShopListfilter::GetActiveInstance());
    echo $oViewRenderer->Render('/pkgShopListFilter/shopFilterItemPrice.html.twig');
}
