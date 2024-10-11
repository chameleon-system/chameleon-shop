<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopOrderStatusMapper_Status extends AbstractViewMapper
{
    /**
     * A mapper has to specify its requirements by providing th passed MapperRequirements instance with the
     * needed information and returning it.
     *
     * example:
     *
     * $oRequirements->NeedsSourceObject("foo",'stdClass','default-value');
     * $oRequirements->NeedsSourceObject("bar");
     * $oRequirements->NeedsMappedValue("baz");
     *
     * @param IMapperRequirementsRestricted $oRequirements
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oObject', 'TdbShopOrderStatus|TdbShopOrder', null, true);
        $oRequirements->NeedsSourceObject('local', 'TdbCmsLocals', TdbCmsLocals::GetActive(), true);
    }

    /**
     * To map values from models to views the mapper has to implement iVisitable.
     * The ViewRender will pass a prepared MapperVisitor instance to the mapper.
     *
     * The mapper has to fill the values it is responsible for in the visitor.
     *
     * example:
     *
     * $foo = $oVisitor->GetSourceObject("foomodel")->GetFoo();
     * $oVisitor->SetMapperValue("foo", $foo);
     *
     *
     * To be able to access the desired source object in the visitor, the mapper has
     * to declare this requirement in its GetRequirements method (see IViewMapper)
     *
     * @param \IMapperVisitorRestricted     $oVisitor
     * @param bool                          $bCachingEnabled      - if set to true, you need to define your cache trigger that invalidate the view rendered via mapper. if set to false, you should NOT set any trigger
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ): void {
        /** @var TdbCmsLocals $local */
        $local = $oVisitor->GetSourceObject('local');

        /** @var TdbShopOrderStatus|TdbShopOrder $oStatus */
        $oObject = $oVisitor->GetSourceObject('oObject');

        if (null === $oObject) {
            return;
        }
        if ($oObject instanceof TdbShopOrder) {
            $aStatusList = array();

            $oStatusList = $oObject->GetFieldShopOrderStatusList();
            $oStatusList->ChangeOrderBy(array('status_date' => 'DESC'));

            while ($oStatus = $oStatusList->Next()) {
                $aStatusList[] = $this->getDataFromStatus($oStatus, $local, $oCacheTriggerManager, $bCachingEnabled);
            }
            $oVisitor->SetMappedValue('aStatusList', $aStatusList);
        } else {
            $aStatus = $this->getDataFromStatus($oObject, $local, $oCacheTriggerManager, $bCachingEnabled);
            $oVisitor->SetMappedValueFromArray($aStatus);
        }
    }

    /**
     * @param bool $bCachingEnabled
     *
     * @return array<string, mixed>
     */
    protected function getDataFromStatus(
        TdbShopOrderStatus $oStatus,
        TdbCmsLocals $local,
        IMapperCacheTriggerRestricted $oCacheTriggerManager,
        $bCachingEnabled
    ) {
        $aStatus = array(
            'date' => $local->FormatDate(
                    $oStatus->fieldStatusDate,
                    TdbCmsLocals::DATEFORMAT_SHOW_DATE | TdbCmsLocals::DATEFORMAT_SHOW_TIME_HOUR | TdbCmsLocals::DATEFORMAT_SHOW_TIME_MINUTE
                ),
            'codeName' => $oStatus->GetFieldShopOrderStatusCode()->fieldName,
            'info' => $oStatus->GetStatusText(),
            'aPositions' => array(),
        );
        $oStatusPosList = $oStatus->GetFieldShopOrderStatusItemList();
        while ($oPos = $oStatusPosList->Next()) {
            $aArticleData = $this->getArticle(
                $oPos,
                $oCacheTriggerManager,
                $bCachingEnabled
            );

            $aStatus['aPositions'][] = $aArticleData;
        }

        return $aStatus;
    }

    /**
     * the the value map for one article (order item).
     *
     * @param TdbShopOrderStatusItem        $oPosition
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     * @param bool $bCachingEnabled
     *
     * @return array
     */
    protected function getArticle(
        TdbShopOrderStatusItem $oPosition,
        IMapperCacheTriggerRestricted $oCacheTriggerManager,
        $bCachingEnabled
    ) {
        $oOrderItem = $oPosition->GetFieldShopOrderItem();
        $aArticle = array();
        /** @var $oArticle TdbShopArticle */
        $oArticle = $oOrderItem->GetFieldShopArticle();

        $aArticle['iAmount'] = number_format((float) $oOrderItem->fieldOrderAmount, 0, ',', '');
        $aArticle['iUsedAmount'] = number_format($oPosition->fieldAmount, 0, ',', '');
        $aArticle['sManufacturer'] = $oOrderItem->fieldShopManufacturerName;
        $aArticle['sArticleName'] = $oOrderItem->fieldName;
        $aArticle['sArticleVariantName'] = $oOrderItem->fieldNameVariantInfo;
        $aArticle['sPrice'] = $oOrderItem->fieldOrderPriceFormated;
        $aArticle['sPriceTotal'] = $oOrderItem->fieldOrderPriceTotalFormated;
        $aArticle['sUsedPriceTotal'] = $oOrderItem->fieldPrice * $oPosition->fieldAmount;

        if ($oArticle) {
            $aArticle['sImageId'] = $this->getArticleImageId($oArticle, $oCacheTriggerManager, $bCachingEnabled);
            $aArticle = $this->GetVariantInfo($oArticle, $aArticle, $oCacheTriggerManager, $bCachingEnabled);
            $aArticle['sArticleDetailURL'] = $oArticle->getLink(true);
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oArticle->table, $oArticle->id);
            }
        }

        return $aArticle;
    }

    /**
     * get the image id of connected article for given order item in the dimensions defined by the given image size identifier.
     *
     * @param TdbShopOrderItem $oOrderItem
     * @param string           $sImageSizeName
     * @param bool $bCachingEnabled
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
     * @param array<string, mixed> $aData
     * @param bool $bCachingEnabled
     *
     * @return array<string, mixed>
     */
    protected function GetVariantInfo($oArticle, $aData, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled)
    {
        $aVariantTypeList = array();
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
