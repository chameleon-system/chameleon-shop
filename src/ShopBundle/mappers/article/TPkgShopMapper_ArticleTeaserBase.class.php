<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;

class TPkgShopMapper_ArticleTeaserBase extends AbstractPkgShopMapper_Article
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

        /** @var $oShop TdbShop */
        $oShop = $oVisitor->GetSourceObject('oShop');
        if ($oShop && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oShop->table, $oShop->id);
        }

        $aData = [];
        $aData['sShippingLink'] = $oShop->GetLinkToSystemPageAsPopUp(TGlobal::Translate('chameleon_system_shop.link.shipping_link'), 'shipping');

        $aData['sPrice'] = $oLocal->FormatNumber($oArticle->dPrice, 2);
        if ($oArticle->fieldPriceReference > $oArticle->dPrice) {
            $aData['sRetailPrice'] = $oLocal->FormatNumber($oArticle->fieldPriceReference, 2);
        }
        $aData['bIsNew'] = $oArticle->fieldIsNew;

        $oImage = $oArticle->GetImagePreviewObject('standard-list');
        if ($oImage) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oImage->table, $oImage->id);
                $oCacheTriggerManager->addTrigger('cms_media', $oImage->fieldCmsMediaId);
            }
            $aData['sImageId'] = $oImage->fieldCmsMediaId;
        }

        $oBrand = $oArticle->GetFieldShopManufacturer();
        if ($oBrand) {
            $aData['sTopic'] = $oBrand->GetName();
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oBrand->table, $oBrand->id);
            }
        }
        $aData['sHeadline'] = $oArticle->GetName();
        if (!is_null($oArticle) && !is_null($oArticle->id)) {
            $aData['sLink'] = $oArticle->getLink();
            $aData['sDescription'] = $oArticle->GetTextField('description', 600, false);
            $aData['sShortDescription'] = $oArticle->GetTextField('description_short', 600, false);
            $aData = $this->GetVariantInfo($oArticle, $aData, $oCacheTriggerManager, $bCachingEnabled);
        }
        $oVisitor->SetMappedValueFromArray($aData);
    }

    /**
     * Add color variant data if available for article
     * Add all other available article variants in separate data.
     *
     * @param TdbShopArticle $oArticle
     * @param array $aData
     * @param bool $bCachingEnabled
     *
     * @return array
     */
    protected function GetVariantInfo($oArticle, $aData, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled)
    {
        $aColors = [];
        $aVariantTypeList = [];
        if ($oArticle->HasVariants()) {
            $oVariantSet = $oArticle->GetFieldShopVariantSet();
            if ($oVariantSet && $bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oVariantSet->table, $oVariantSet->id);
            }
            $oVariantTypes = $oVariantSet->GetFieldShopVariantTypeList();

            $bLoadInactiveItems = false;
            $activeShop = $this->getShopService()->getActiveShop();
            if (property_exists($activeShop, 'fieldLoadInactiveVariants') && $activeShop->fieldLoadInactiveVariants) {
                $bLoadInactiveItems = true;
            }

            while ($oVariantType = $oVariantTypes->Next()) {
                if ($bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oVariantType->table, $oVariantType->id);
                }

                if ($bLoadInactiveItems) {
                    $oAvailableValues = $oArticle->GetVariantValuesAvailableForTypeIncludingInActive($oVariantType);
                } else {
                    $oAvailableValues = $oArticle->GetVariantValuesAvailableForType($oVariantType);
                }

                if (!$oAvailableValues) {
                    continue;
                }
                if ('color' === $oVariantType->fieldIdentifier) {
                    while ($oValue = $oAvailableValues->Next()) {
                        if ($bCachingEnabled) {
                            $oCacheTriggerManager->addTrigger($oValue->table, $oValue->id);
                        }
                        $aColors[] = [
                            'sLink' => $oValue->fieldUrlName,
                            'sHex' => '#'.$oValue->fieldColorCode,
                            'cms_media_id' => $oValue->fieldCmsMediaId,
                        ];
                    }
                } else {
                    $aType = [
                        'sTitle' => $oVariantType->fieldName,
                        'sSystemName' => $oVariantType->fieldIdentifier,
                        'cms_media_id' => $oVariantType->fieldCmsMediaId,
                        'aItems' => [],
                        'bAllowSelection' => true,
                    ];
                    $aItems = [];
                    while ($oValue = $oAvailableValues->Next()) {
                        if ($bCachingEnabled) {
                            $oCacheTriggerManager->addTrigger($oValue->table, $oValue->id);
                        }
                        $aItem = [
                            'sTitle' => $oValue->fieldName,
                            'sColor' => $oValue->fieldColorCode,
                            'cms_media_id' => $oValue->fieldCmsMediaId,
                            'bIsActive' => false,  // currently selected article variant (shows activate state)
                            'bArticleIsActive' => '1',
                            'sSelectLink' => '',
                        ];

                        if ($bLoadInactiveItems) {
                            if (isset($oValue->sqlData['articleactive']) && $oValue->sqlData['articleactive'] > 0) {
                                $aItem['bArticleIsActive'] = '1'; // if 0 the variant is not active and the variant values should be rendered as non-available
                            } else {
                                $aItem['bArticleIsActive'] = '0';
                            }
                        }

                        $aItems[] = $aItem;
                    }
                    $aType['aItems'] = $aItems;
                    $aVariantTypeList[] = $aType;
                }
            }
        }
        $aData['aColors'] = $aColors;
        $aData['aVariantTypes'] = $aVariantTypeList;

        return $aData;
    }

    private function getShopService(): ShopServiceInterface
    {
        return ServiceLocator::get('chameleon_system_shop.shop_service');
    }
}
