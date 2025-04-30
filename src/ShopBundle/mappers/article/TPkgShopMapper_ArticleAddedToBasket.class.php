<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_ArticleAddedToBasket extends AbstractPkgShopMapper_Article
{
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        parent::GetRequirements($oRequirements);

        $oRequirements->NeedsSourceObject('iAmount');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        $oLocal = TCMSLocal::GetActive();

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

        $aArticle = [];
        $aArticle['sArticleDetailURL'] = $oArticle->getLink();

        // basket-rightbox
        $oImage = $oArticle->GetImagePreviewObject('basket');
        if (null !== $oImage) {
            $aArticle['sImageId'] = $oImage->fieldCmsMediaId;
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oImage->table, $oImage->id);
                $oCacheTriggerManager->addTrigger('cms_media', $oImage->fieldCmsMediaId);
            }
        }
        $oManufacturer = $oArticle->GetFieldShopManufacturer();
        if (null !== $oManufacturer) {
            $aArticle['sManufacturer'] = $oManufacturer->GetName();
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oManufacturer->table, $oManufacturer->id);
            }
        }
        $aArticle['sArticleName'] = $oArticle->GetName();
        $aArticle['iAmount'] = $oVisitor->GetSourceObject('iAmount');

        $oShopStockMessage = $oArticle->GetFieldShopStockMessage();
        if ($oShopStockMessage) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oShopStockMessage->table, $oShopStockMessage->id);
            }
            $aArticle['sShippingTime'] = (null !== $oShopStockMessage) ? ($oShopStockMessage->GetShopStockMessage()) : ('');
        }

        $aArticle['sPrice'] = $oLocal->FormatNumber($oArticle->dPrice, 2);
        if ($oArticle->fieldPriceReference > $oArticle->dPrice) {
            $aArticle['sRetailPrice'] = $oLocal->FormatNumber($oArticle->fieldPriceReference, 2);
        }

        $aArticle['sShippingLink'] = $oShop->GetLinkToSystemPageAsPopUp(ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.link.shipping_link'), 'shipping');

        $oVisitor->SetMappedValue('aArticle', $aArticle);

        $oVisitor->SetMappedValue('sShopUrl', '#');
        $oVisitor->SetMappedValue('sBasketUrl', $oShop->GetBasketLink(false, true));
    }
}
