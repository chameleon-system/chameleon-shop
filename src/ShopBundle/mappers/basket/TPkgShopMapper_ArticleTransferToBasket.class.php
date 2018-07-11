<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_ArticleTransferToBasket extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('oObject', 'TdbShopArticle');
        $oRequirements->NeedsSourceObject('bRedirectToBasket', 'bool', false);
        $oRequirements->NeedsSourceObject('oShop', 'TdbShop', TdbShop::GetInstance());
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oArticle TdbShopArticle */
        $oArticle = $oVisitor->GetSourceObject('oObject');
        if ($oArticle && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oArticle->table, $oArticle->id);
        }
        /** @var $oShop TdbShop */
        $oShop = $oVisitor->GetSourceObject('oShop');
        if ($oShop && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oShop->table, $oShop->id);
        }
        $oVisitor->SetMappedValue('bIsBuyable', $oArticle->IsBuyable());
        $bRedirectToBasket = $oVisitor->GetSourceObject('bRedirectToBasket');
        $aBasketParameters = $oArticle->GetToBasketLinkParameters($bRedirectToBasket, false, false, MTShopBasketCore::MSG_CONSUMER_NAME);
        unset($aBasketParameters[MTShopBasketCore::URL_REQUEST_PARAMETER][MTShopBasketCore::URL_ITEM_AMOUNT_NAME]);
        $aBasketParameters['module_fnc'][$oShop->GetBasketModuleSpotName()] = 'TransferFromNoticeList';
        $sAddToBasketHiddenFields = TTools::GetArrayAsFormInput($aBasketParameters);
        $oVisitor->SetMappedValue('sAddToBasketHiddenFields', $sAddToBasketHiddenFields);
        $oVisitor->SetMappedValue('sConsumerAddToBasketName', $aBasketParameters[MTShopBasketCore::URL_REQUEST_PARAMETER][MTShopBasketCore::URL_MESSAGE_CONSUMER_NAME]);
    }
}
