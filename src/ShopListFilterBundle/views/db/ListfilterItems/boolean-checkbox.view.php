<?php

/** @var $oListItem TdbPkgShopListfilterItem */
$oViewRenderer = new ViewRenderer();
$oViewRenderer->AddMapper(new TPkgShopListfilterMapper_FilterBoolean());
$oViewRenderer->AddSourceObject('oFilterItem', $oListItem);
$oViewRenderer->AddSourceObject('oActiveFilter', TdbPkgShopListfilter::GetActiveInstance());
echo $oViewRenderer->Render('/pkgShopListFilter/boolean.html.twig');
