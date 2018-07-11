<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_ArticleShippingTime extends AbstractPkgShopMapper_Article
{
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
        if ($oArticle) {
            $oStockMessage = $oArticle->GetFieldShopStockMessage();
            if ($oStockMessage) {
                if ($bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oStockMessage->table, $oStockMessage->id);
                }
                $oVisitor->SetMappedValue('sStockMessage', $oStockMessage->GetShopStockMessage());
                $oVisitor->SetMappedValue('sStockMessageCSSClass', $oStockMessage->fieldClass);
            }
        }
    }
}
