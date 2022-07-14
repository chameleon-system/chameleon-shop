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
use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;

class TPkgShopMapper_ArticleGetOneVariantType extends AbstractPkgShopMapper_Article
{
    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $aReturnData = array();
        /** @var $oArticle TdbShopArticle */
        $oArticle = $oVisitor->GetSourceObject('oObject');
        if ($oArticle && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oArticle->table, $oArticle->id);
        }

        $aPageParameters = array();

        $oVariantSet = $oArticle->GetFieldShopVariantSet();
        if ($oVariantSet) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oVariantSet->table, $oVariantSet->id);
            }

            $aSelectedTypeValues = $this->getSelectedTypeValues($oArticle);

            $bLoadInactiveItems = false;
            $oShop = TShop::GetInstance();
            if (property_exists($oShop, 'fieldLoadInactiveVariants') && $oShop->fieldLoadInactiveVariants) {
                $bLoadInactiveItems = true;
            }

            $oVariantTypes = $oVariantSet->GetFieldShopVariantTypeList();
            if ($oVariantTypes) {
                $aTmpSelectValue = array();
                $sPreviousTypeId = '';
                while ($oVariantType = $oVariantTypes->Next()) {
                    if ($bCachingEnabled) {
                        $oCacheTriggerManager->addTrigger($oVariantType->table, $oVariantType->id);
                    }

                    if ($bLoadInactiveItems) {
                        $oAvailableValues = $oArticle->GetVariantValuesAvailableForTypeIncludingInActive($oVariantType, $aTmpSelectValue);
                    } else {
                        $oAvailableValues = $oArticle->GetVariantValuesAvailableForType($oVariantType, $aTmpSelectValue);
                    }

                    if (!$oAvailableValues) {
                        continue;
                    }

                    $sActiveValueForVariantType = '';
                    if (is_array($aSelectedTypeValues) && isset($aSelectedTypeValues[$oVariantType->id])) {
                        $sActiveValueForVariantType = $aSelectedTypeValues[$oVariantType->id];
                    }
                    if ('' != $oVariantType->fieldCmsMediaId && $bCachingEnabled) {
                        $oCacheTriggerManager->addTrigger('cms_media', $oVariantType->fieldCmsMediaId);
                    }
                    $aType = array(
                        'sTitle' => $oVariantType->fieldName,
                        'sSystemName' => $oVariantType->fieldIdentifier,
                        'cms_media_id' => $oVariantType->fieldCmsMediaId,
                        'bAllowSelection' => (empty($sPreviousTypeId) || isset($aTmpSelectValue[$sPreviousTypeId])),
                        'aItems' => array(),
                    );
                    $aItems = array();
                    $sFirstVariantId = '';
                    while ($oValue = $oAvailableValues->Next()) {
                        if ($bCachingEnabled) {
                            $oCacheTriggerManager->addTrigger($oValue->table, $oValue->id);
                        }
                        $aSelectionRestriction = $aTmpSelectValue;
                        $aSelectionRestriction[$oVariantType->id] = $oValue->id;

                        $sCmsMediaId = $oValue->fieldCmsMediaId;
                        if (is_numeric($sCmsMediaId) && $sCmsMediaId < 1000) { // dummy image
                            $sCmsMediaId = '';
                        }

                        if ('' != $sCmsMediaId && $bCachingEnabled) {
                            $oCacheTriggerManager->addTrigger('cms_media', $sCmsMediaId);
                        }
                        $aItem = array(
                            'sTitle' => $oValue->fieldName,
                            'sColor' => $oValue->fieldColorCode,
                            'cms_media_id' => $sCmsMediaId,
                            'bIsActive' => ($sActiveValueForVariantType == $oValue->id),
                            'sSelectLink' => '',
                            'bArticleIsActive' => '1',
                        );

                        if ($bLoadInactiveItems) {
                            if (isset($oValue->sqlData['articleactive']) && $oValue->sqlData['articleactive'] > 0) {
                                $aItem['bArticleIsActive'] = '1';
                            } else {
                                $aItem['bArticleIsActive'] = '0';
                            }
                        }

                        $aSelectionLinkData = $aTmpSelectValue;
                        $aSelectionLinkData[$oVariantType->id] = $oValue->id;
                        $aPageParameters[TdbShopVariantType::URL_PARAMETER] = $aSelectionLinkData;

                        $aItem['sSelectLink'] = TTools::GetArrayAsURL($aPageParameters, '?');
                        $aItems[] = $aItem;
                        if (empty($sFirstVariantId)) {
                            $sFirstVariantId = $oValue->id;
                        }
                    }
                    $aType['aItems'] = $aItems;

                    if (is_array($aSelectedTypeValues) && isset($aSelectedTypeValues[$oVariantType->id])) {
                        $aTmpSelectValue[$oVariantType->id] = $aSelectedTypeValues[$oVariantType->id];
                    }
                    $aReturnData[$oVariantType->id] = $aType;
                    $sPreviousTypeId = $oVariantType->id;
                }
            }
        }
        $oVisitor->SetMappedValue('aVariantTypes', $aReturnData);
    }

    /**
     * Can be either from the current article (variant) or the user's selection (URL).
     *
     * @param TdbShopArticle $article
     * @return array
     */
    private function getSelectedTypeValues(\TdbShopArticle $article): array
    {
        $selectedTypeValues = [];
        if (true === $article->IsVariant()) {
            $typeValueList = $article->GetFieldShopVariantTypeValueList();

            while (false !== ($typeValue = $typeValueList->Next())) {
                $selectedTypeValues[$typeValue->fieldShopVariantTypeId] = $typeValue->id;
            }
        } else {
            /** @var array $selectedTypeValues */
            $selectedTypeValues = $this->getInputFilterUtil()->getFilteredGetInput(\TShopVariantType::URL_PARAMETER, []);
        }

        return $selectedTypeValues;
    }

    private function getInputFilterUtil(): InputFilterUtilInterface
    {
        return ServiceLocator::get('chameleon_system_core.util.input_filter');
    }
}
