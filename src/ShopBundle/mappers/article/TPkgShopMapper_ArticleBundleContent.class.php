<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_ArticleBundleContent extends AbstractPkgShopMapper_Article
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        parent::GetRequirements($oRequirements);
        $oRequirements->NeedsSourceObject('oLocal', 'TCMSLocal', TCMSLocal::GetActive());
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

        /** @var $oLocal TCMSLocal */
        $oLocal = $oVisitor->GetSourceObject('oLocal');
        if ($oLocal && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oLocal->table, $oLocal->id);
        }

        // aBundleArticleList - array with (sAmount, sTitle, sLink, sPrice, sCurrency)
        if (false === $oArticle->fieldIsBundle) {
            return;
        }

        $aBundleArticleList = [];

        $oBundleItems = $oArticle->GetFieldShopBundleArticleList();
        while ($oBundleItem = $oBundleItems->Next()) {
            $oCacheTriggerManager->addTrigger($oBundleItem->table, $oBundleItem->id);
            $oBundleArticle = $oBundleItem->GetFieldBundleArticle();
            if ($oBundleArticle) {
                if ($bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oBundleArticle->table, $oBundleArticle->id);
                }
                $aItem = [
                    'sAmount' => $oLocal->FormatNumber($oBundleItem->fieldAmount, 0),
                    'sTitle' => $oBundleArticle->GetName(),
                    'sLink' => $oBundleArticle->getLink(),
                    'sPrice' => $oLocal->FormatNumber($oBundleArticle->dPrice, 2),
                ];
                $aBundleArticleList[] = $aItem;
            }
        }

        $oVisitor->SetMappedValue('aBundleArticleList', $aBundleArticleList);
    }
}
