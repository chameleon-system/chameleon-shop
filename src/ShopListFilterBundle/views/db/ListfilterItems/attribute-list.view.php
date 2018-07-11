<?php

$oViewRenderer = new ViewRenderer();
$oViewRenderer->AddMapper(new TPkgShopListfilterMapper_FilterStandard());
$oViewRenderer->AddSourceObject('oFilterItem', $oListItem);
$oViewRenderer->AddSourceObject('oActiveFilter', TdbPkgShopListfilter::GetActiveInstance());
echo $oViewRenderer->Render('/pkgShopListFilter/shopFilterMultiSelect.html.twig');
