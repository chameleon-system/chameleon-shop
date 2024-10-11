<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_ArticleAddToBasket extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oObject', 'TdbShopArticle');
        $oRequirements->NeedsSourceObject('bRedirectToBasket', 'bool', false);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oArticle TdbShopArticle */
        $oArticle = $oVisitor->GetSourceObject('oObject');
        if ($oArticle && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oArticle->table, $oArticle->id);
        }

        $isBuyable = $oArticle->IsBuyable();
        $oVisitor->SetMappedValue('bIsBuyable', $isBuyable);

        $oVisitor->SetMappedValue('bHasActiveVariants', $oArticle->HasVariants(true));
        $allowChangeAmount = $isBuyable;
        $oVisitor->SetMappedValue('allowChangeAmount', $allowChangeAmount);

        $bRedirectToBasket = $oVisitor->GetSourceObject('bRedirectToBasket');

        $oVisitor->SetMappedValue('sAddToBasketLink', $oArticle->GetToBasketLink(false, $bRedirectToBasket, false, false, MTShopBasketCore::MSG_CONSUMER_NAME));
        $aBasketParameters = $oArticle->GetToBasketLinkBasketParameters($bRedirectToBasket, false, false, MTShopBasketCore::MSG_CONSUMER_NAME);
        unset($aBasketParameters[MTShopBasketCore::URL_REQUEST_PARAMETER][MTShopBasketCore::URL_ITEM_AMOUNT_NAME]);
        $sAddToBasketHiddenFields = TTools::GetArrayAsFormInput($aBasketParameters);
        $oVisitor->SetMappedValue('sAddToBasketHiddenFields', $sAddToBasketHiddenFields);
        $oVisitor->SetMappedValue('sConsumerAddToBasketName', $aBasketParameters[MTShopBasketCore::URL_REQUEST_PARAMETER][MTShopBasketCore::URL_MESSAGE_CONSUMER_NAME]);
    }
}
