<?php

/** @var $oMessageType TdbCmsMessageManagerMessageType */
/** @var $oMessageObject TdbCmsMessageManagerMessage */
/** @var $sMessageString string */
$oViewRenderer = new ViewRenderer();

$oViewRenderer->addMapperFromIdentifier('chameleon_system_shop_currency.mapper.shop_currency_mapper');
$oViewRenderer->AddMapper(new \ChameleonSystem\AmazonPaymentBundle\mappers\AmazonButtonWidgetMapper());
$oViewRenderer->addMapperFromIdentifier('chameleon_system_core.mapper.message_manager');
$oViewRenderer->AddSourceObject('oMessageType', $oMessageType);
$oViewRenderer->AddSourceObject('sMessage', $sMessageString);

$oViewRenderer->AddMapper(new TPkgShopMapper_ArticleAddedToBasket());
$oArticle = null;
$iAmount = 0;
$aMessageParameters = $oMessageObject->GetMessageParameters();
if (is_array($aMessageParameters)) {
    $oArticle = (isset($aMessageParameters['sArticleAddedId'])) ? TdbShopArticle::GetNewInstance($aMessageParameters['sArticleAddedId']) : ('');
    $iAmount = (isset($aMessageParameters['amount'])) ? ($aMessageParameters['amount']) : (0);
}
$oViewRenderer->AddSourceObject('oObject', $oArticle);
$oViewRenderer->AddSourceObject('iAmount', $iAmount);

$oArticleList = $oArticle->GetFieldShopArticle2List();
if (0 == $oArticleList->Length() && true == $oArticle->IsVariant()) {
    $oArticleList = $oArticle->GetFieldVariantParent()->GetFieldShopArticle2List();
}
$oArticleList->SetPagingInfo(0, 3);
$oViewRenderer->AddSourceObject('oList', $oArticleList);
$oViewRenderer->generateSourceObjectForObjectList(
    'aArticleList', // target name
    'oList', // from
    'oObject', // as
    '/common/teaser/standard-shopArticle.html.twig', // with this view
    array(
        'chameleon_system_shop_currency.mapper.shop_currency_mapper',
        'TPkgShopMapper_ArticleTeaserBase',
        'TPkgShopMapper_ArticleRatingAverage',
        'TPkgShopMapper_ArticleShippingTime',
    ) // using the following mappers
);

$oViewRenderer->addMapperFromIdentifier('chameleon_system_core.mapper.message_manager_overlay');

echo $oViewRenderer->Render('/pkgShop/shopBasket/shopBasketAddedToBasket.html.twig');
