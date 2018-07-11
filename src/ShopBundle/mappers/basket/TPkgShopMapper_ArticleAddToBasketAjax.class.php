<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_ArticleAddToBasketAjax extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('oObject', 'TdbShopArticle');
        $oRequirements->NeedsSourceObject('bRedirectToBasket', 'bool', false);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oArticle TdbShopArticle */
        $oArticle = $oVisitor->GetSourceObject('oObject');
        if (!is_null($oArticle) && !is_null($oArticle->id)) {
            $oVisitor->SetMappedValue('sBasketFormId', 'tobasket'.$oArticle->sqlData['cmsident']);
            $sAjaxBasketLink = "CHAMELEON.Custom.AddToBasket('".TdbShop::GetInstance()->GetBasketModuleSpotName()."', '".TGlobal::OutHTML($oArticle->id)."', document.tobasket{$oArticle->sqlData['cmsident']}.elements['".MTShopBasketCore::URL_ITEM_AMOUNT."'].value);return false;";
            $oVisitor->SetMappedValue('sAjaxBasketLink', $sAjaxBasketLink);
        }
        if ($oArticle && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oArticle->table, $oArticle->id);
        }
    }
}
