<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * always use TPkgShopBasketMapper_BasketItems instead of TPkgShopBasketMapper_BasketItemsEndPoint.
/**/
class TPkgShopBasketMapper_BasketItemsEndPoint extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('oBasket', 'TShopBasket', TShopBasket::GetInstance());
        $oRequirements->NeedsSourceObject('oCurrency', 'TdbPkgShopCurrency', TdbPkgShopCurrency::GetActiveInstance());
        $oRequirements->NeedsSourceObject('generateAbsoluteProductUrls', 'bool', false, true);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oBasket TShopBasket */
        $oBasket = $oVisitor->GetSourceObject('oBasket');

        /** @var $oCurrency TdbPkgShopCurrency */
        $oCurrency = $oVisitor->GetSourceObject('oCurrency');

        $generateAbsoluteProductUrls = $oVisitor->GetSourceObject('generateAbsoluteProductUrls');

        $aBasketItems = array();
        $oBasketContents = $oBasket->GetBasketContents();
        $oBasketContents->GoToStart();
        /** @var $oItem TShopBasketArticle */
        while ($oItem = $oBasketContents->Next()) {
            $aBasketItem = array(
                'sId' => $oItem->id,
                'sBasketItemKey' => $oItem->sBasketItemKey,
                'sImageId' => $oItem->GetImagePreviewObject('basket')->GetImageObject()->id,
                'iAmount' => $oItem->dAmount,
                'sManufacturer' => '',
                'sArticleName' => $oItem->GetName(),
                'sPrice' => TCMSLocal::GetActive()->FormatNumber($oItem->dPrice, 2),
                'sCurrency' => $oCurrency->fieldSymbol,
                'sArticleDetailURL' => $oItem->getLink($generateAbsoluteProductUrls),
                'sNoticeListLink' => $oItem->GetToNoticeListLink(),
                'sRemoveFromBasketLink' => $oItem->GetRemoveFromBasketLink(),
                'bAllowChangeAmount' => true,
                'sPriceTotal' => TCMSLocal::GetActive()->FormatNumber($oItem->dPriceTotal, 2),
            );
            if ($oItem instanceof IPkgShopBasketArticleWithCustomData && true === $oItem->isConfigurableArticle()) {
                $aBasketItem['customData'] = $oItem->getCustomDataForTwigOutput();
                $aBasketItem['customDataTwigTemplate'] = $oItem->getCustomDataTwigTemplate();
            }
            $oVat = $oItem->GetVat();
            if ($oVat) {
                $aBasketItem['sVatFormatted'] = $oVat->GetName();
                $aBasketItem['sVat'] = $oVat->fieldVatPercentFormated;
            }
            $oManufacturer = $oItem->GetFieldShopManufacturer();
            if ($oManufacturer) {
                $aBasketItem['sManufacturer'] = $oManufacturer->GetName();
            }
            if (empty($aBasketItem['sImageId'])) {
                $oPrimaryImage = $oItem->GetPrimaryImage();
                if ($oPrimaryImage) {
                    $oImage = $oPrimaryImage->GetImage(0, 'images', true);
                    if ($oImage) {
                        $aBasketItem['sImageId'] = $oImage->id;
                    }
                }
            }
            $aBasketItem = $this->GetVariantInfo($oItem, $aBasketItem);
            $oStockMessage = $oItem->GetFieldShopStockMessage();
            if (null !== $oStockMessage) {
                $aBasketItem['sShippingInformation'] = $oStockMessage->GetShopStockMessage();
                $aBasketItem['sShopStockMessageClass'] = $oStockMessage->fieldClass;
            }
            $aBasketItems[] = $aBasketItem;
        }

        $oVisitor->SetMappedValue('aArticleList', $aBasketItems);
    }

    /**
     * Get variant data if article is variant.
     *
     * @param TdbShopArticle $oArticle
     * @param array<string, mixed> $aData
     *
     * @return array<string, mixed>
     */
    protected function GetVariantInfo($oArticle, $aData)
    {
        $aVariantTypeList = array();
        if ($oArticle) {
            if ($oArticle->IsVariant()) {
                $oVariantSet = $oArticle->GetFieldShopVariantSet();
                $oVariantTypes = $oVariantSet->GetFieldShopVariantTypeList();
                while ($oVariantType = $oVariantTypes->Next()) {
                    $oValue = $oArticle->GetActiveVariantValue($oVariantType->fieldIdentifier);
                    $aType = array(
                        'sTitle' => $oVariantType->fieldName,
                        'sSystemName' => $oVariantType->fieldIdentifier,
                        'cms_media_id' => $oVariantType->fieldCmsMediaId,
                        'aItems' => array(),
                    );
                    $aItems = array();
                    $aItem = array(
                        'sTitle' => $oValue->fieldName,
                        'sColor' => $oValue->fieldColorCode,
                        'cms_media_id' => $oValue->fieldCmsMediaId,
                        'bIsActive' => false,
                        'sSelectLink' => '',
                    );
                    $aItems[] = $aItem;
                    $aType['aItems'] = $aItems;
                    $aVariantTypeList[] = $aType;
                }
            }
            $aData['aVariantTypes'] = $aVariantTypeList;
        }

        return $aData;
    }
}
