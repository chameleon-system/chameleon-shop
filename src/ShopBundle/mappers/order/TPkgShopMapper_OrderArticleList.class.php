<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_OrderArticleList extends AbstractViewMapper
{
    /** @var bool */
    protected $bAbsoluteArticleUrls = false;

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oObject', 'TdbShopOrder', null, true);
        $oRequirements->NeedsSourceObject('bAbsoluteArticleUrls', null, false);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oOrder TdbShopOrder */
        $oOrder = $oVisitor->GetSourceObject('oObject');
        $this->bAbsoluteArticleUrls = $oVisitor->GetSourceObject('bAbsoluteArticleUrls');
        if (null !== $oOrder) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oOrder->table, $oOrder->id);
            }
            $oVisitor->SetMappedValue('aArticleList', $this->getArticleList($oOrder, $oCacheTriggerManager, $bCachingEnabled));
        }
    }

    /**
     * get the value map for the whole article list (order items) of the order.
     *
     * @param bool $bCachingEnabled
     *
     * @return array
     */
    protected function getArticleList(TdbShopOrder $oOrder, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled)
    {
        $aArticleList = [];
        $oOrderItemList = $oOrder->GetFieldShopOrderItemList();
        while ($oOrderItem = $oOrderItemList->Next()) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oOrderItem->table, $oOrderItem->id);
            }
            $aArticleList[] = $this->getArticle($oOrderItem, $oCacheTriggerManager, $bCachingEnabled);
        }

        return $aArticleList;
    }

    /**
     * the the value map for one article (order item).
     *
     * @param bool $bCachingEnabled
     *
     * @return array
     */
    protected function getArticle(TdbShopOrderItem $oOrderItem, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled)
    {
        $aArticle = [];
        $oArticle = $oOrderItem->GetFieldShopArticle();

        $aArticle['iAmount'] = $oOrderItem->fieldOrderAmountFormated;
        $aArticle['sManufacturer'] = $oOrderItem->fieldShopManufacturerName;
        $aArticle['sArticleName'] = $oOrderItem->fieldName;
        $aArticle['sArticleVariantName'] = $oOrderItem->fieldNameVariantInfo;
        $aArticle['sShippingInformation'] = '';
        $aArticle['sPrice'] = $oOrderItem->fieldOrderPriceFormated;
        $aArticle['sPriceTotal'] = $oOrderItem->fieldOrderPriceTotalFormated;
        $aArticle['sVat'] = $oOrderItem->fieldVatPercentFormated;
        $aArticle['sVatFormatted'] = number_format($oOrderItem->fieldVatPercent, 0, '.', '').'%';
        if ($oArticle) {
            $aArticle['sImageId'] = $this->getArticleImageId($oArticle, $oCacheTriggerManager, $bCachingEnabled);
            $aArticle = $this->GetVariantInfo($oArticle, $aArticle, $oCacheTriggerManager, $bCachingEnabled);
            $aArticle['sArticleDetailURL'] = $oArticle->getLink($this->bAbsoluteArticleUrls);
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oArticle->table, $oArticle->id);
            }
        }
        if ($oOrderItem instanceof IPkgShopOrderItemWithCustomData) {
            $aArticle['customData'] = $oOrderItem->getCustomDataForTwigOutput();
            $aArticle['customDataTwigTemplate'] = $oOrderItem->getCustomDataTwigTemplate();
        }

        return $aArticle;
    }

    /**
     * get the image id of connected article for given order item in the dimensions defined by the given image size identifier.
     *
     * @param bool $bCachingEnabled
     * @param string $sImageSizeName
     *
     * @return string empty string or image id
     */
    protected function getArticleImageId(TdbShopArticle $oArticle, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled, $sImageSizeName = 'basket')
    {
        $sImageId = '';
        if (null !== $oArticle) {
            $oImage = $oArticle->GetImagePreviewObject($sImageSizeName);
            if (null !== $oImage) {
                $sImageId = $oImage->fieldCmsMediaId;
            }
            if (empty($sImageId) || (is_numeric($sImageId) && $sImageId < 100)) {
                $oPrimaryImage = $oArticle->GetPrimaryImage();
                if (null !== $oPrimaryImage) {
                    $oImage = $oPrimaryImage->GetImage(0, 'images', true);
                    if (null !== $oImage) {
                        $sImageId = $oImage->id;
                        if ($bCachingEnabled) {
                            $oCacheTriggerManager->addTrigger('cms_media', $sImageId);
                        }
                    }
                }
            } else {
                if ($bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger('cms_media', $sImageId);
                }
            }
        }

        return $sImageId;
    }

    /**
     * Get variant data if article is variant.
     *
     * @param TdbShopArticle $oArticle
     * @param array $aData
     * @param bool $bCachingEnabled
     *
     * @return array
     */
    protected function GetVariantInfo($oArticle, $aData, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled)
    {
        $aVariantTypeList = [];
        if ($oArticle) {
            if ($oArticle->IsVariant()) {
                $oVariantSet = $oArticle->GetFieldShopVariantSet();
                if ($oVariantSet && $bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oVariantSet->table, $oVariantSet->id);
                }
                $oVariantTypes = $oVariantSet->GetFieldShopVariantTypeList();
                while ($oVariantType = $oVariantTypes->Next()) {
                    if ($oVariantType && $bCachingEnabled) {
                        $oCacheTriggerManager->addTrigger($oVariantType->table, $oVariantType->id);
                    }
                    $oValue = $oArticle->GetActiveVariantValue($oVariantType->fieldIdentifier);
                    if ($oValue && $bCachingEnabled) {
                        $oCacheTriggerManager->addTrigger($oValue->table, $oValue->id);
                    }
                    $aType = [
                        'sTitle' => $oVariantType->fieldName,
                        'sSystemName' => $oVariantType->fieldIdentifier,
                        'cms_media_id' => $oVariantType->fieldCmsMediaId,
                        'aItems' => [],
                    ];
                    $aItems = [];
                    $aItem = [
                        'sTitle' => $oValue->fieldName,
                        'sColor' => $oValue->fieldColorCode,
                        'cms_media_id' => $oValue->fieldCmsMediaId,
                        'bIsActive' => false,
                        'sSelectLink' => '',
                    ];
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
