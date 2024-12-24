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
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use ChameleonSystem\ShopBundle\Interfaces\VariantTypeDataModelFactoryInterface;
use ChameleonSystem\ShopBundle\Interfaces\VariantTypeValueDataModelFactoryInterface;

class TPkgShopMapper_ArticleGetOneVariantType extends AbstractPkgShopMapper_Article
{
    /**
     * {@inheritdoc}
     * @throws ErrorException
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $aReturnData = [];
        $variantTypeDataModels = [];
        /** @var \TdbShopArticle $productRecord */
        $productRecord = $oVisitor->GetSourceObject('oObject');
        if ($productRecord && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($productRecord->table, $productRecord->id);
        }

        $variantSetRecord = $productRecord->GetFieldShopVariantSet();
        if ($variantSetRecord) {
            $variantTypeDataModelFactory = $this->getVariantTypeDataModelFactory();
            $variantTypeValueDataModelFactory = $this->getVariantTypeValueDataModelFactory();
            
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($variantSetRecord->table, $variantSetRecord->id);
            }

            $selectedTypeValues = $this->getSelectedTypeValues($productRecord);

            $loadInactiveItems = false;
            $shopService = $this->getShopService();
            $shopRecord = $shopService->getActiveShop();
            
            if (property_exists($shopRecord, 'fieldLoadInactiveVariants') && $shopRecord->fieldLoadInactiveVariants) {
                $loadInactiveItems = true;
            }

            $variantTypeRecordList = $variantSetRecord->GetFieldShopVariantTypeList();
            if ($variantTypeRecordList) {
                $currentSelectedValues = [];
                $previousVariantTypeId = '';
                $variantTypeDataModel = null;
                while ($variantTypeRecord = $variantTypeRecordList->Next()) {
                    if ($bCachingEnabled) {
                        $oCacheTriggerManager->addTrigger($variantTypeRecord->table, $variantTypeRecord->id);
                    }

                    if ($loadInactiveItems) {
                        $availableVariantValuesRecordList = $productRecord->GetVariantValuesAvailableForTypeIncludingInActive($variantTypeRecord, $currentSelectedValues);
                    } else {
                        $availableVariantValuesRecordList = $productRecord->GetVariantValuesAvailableForType($variantTypeRecord, $currentSelectedValues);
                    }

                    if (null === $availableVariantValuesRecordList) {
                        continue;
                    }

                    $sActiveValueForVariantType = '';
                    if (isset($selectedTypeValues[$variantTypeRecord->id])) {
                        $sActiveValueForVariantType = $selectedTypeValues[$variantTypeRecord->id];
                    }
                    if ('' !== $variantTypeRecord->fieldCmsMediaId && $bCachingEnabled) {
                        $oCacheTriggerManager->addTrigger('cms_media', $variantTypeRecord->fieldCmsMediaId);
                    }
                    
                    $variantTypeDataModel = $variantTypeDataModelFactory->createFromVariantTypeRecord($variantTypeRecord, (empty($previousVariantTypeId) || isset($currentSelectedValues[$previousVariantTypeId])));

                    $variantTypes = $variantTypeDataModel->getAllPropertiesAsArray();
                        
                    $variantValues = [];
                    $variantTypeValueDataModels = [];
                    $firstVariantId = '';
                    while ($variantValueRecord = $availableVariantValuesRecordList->Next()) {
                        if ($bCachingEnabled) {
                            $oCacheTriggerManager->addTrigger($variantValueRecord->table, $variantValueRecord->id);
                        }

                        $sCmsMediaId = $variantValueRecord->fieldCmsMediaId;
                        if (is_numeric($sCmsMediaId) && $sCmsMediaId < 1000) { // dummy image
                            $sCmsMediaId = '';
                        }

                        if ('' !== $sCmsMediaId && $bCachingEnabled) {
                            $oCacheTriggerManager->addTrigger('cms_media', $sCmsMediaId);
                        }

                        $variantTypeValueDataModel = $variantTypeValueDataModelFactory->createFromVariantTypeValueRecord(
                            $variantTypeRecord,
                            $variantValueRecord,
                            $loadInactiveItems,
                            $currentSelectedValues,
                            $sActiveValueForVariantType === $variantValueRecord->id,
                            $productRecord
                        );
                        
                        $variantTypeValueDataModels[] = $variantTypeValueDataModel;
                        
                        $aItem = $variantTypeValueDataModel->getAllPropertiesAsArray();

                        $variantValues[] = $aItem;
                        if (empty($firstVariantId)) {
                            $firstVariantId = $variantValueRecord->id;
                        }
                    }
                    $variantTypes['aItems'] = $variantValues;
                    $variantTypeDataModel->setVariantTypeValues($variantTypeValueDataModels);

                    if (isset($selectedTypeValues[$variantTypeRecord->id])) {
                        $currentSelectedValues[$variantTypeRecord->id] = $selectedTypeValues[$variantTypeRecord->id];
                    }
                    $aReturnData[$variantTypeRecord->id] = $variantTypes;
                    $variantTypeDataModels[$variantTypeRecord->id] = $variantTypeDataModel;
                    $previousVariantTypeId = $variantTypeRecord->id;
                }
            }
        }

        /**
         * @deprecated aVariantTypes is deprecated since 7.1.16
         * Use the variantTypeDataModel instead.
         */
        $oVisitor->SetMappedValue('aVariantTypes', $aReturnData);
        $oVisitor->SetMappedValue('variantTypeDataModels', $variantTypeDataModels);
    }

    /**
     * Can be either from the current article (variant) or the user's selection (URL).
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

    private function getVariantTypeDataModelFactory(): VariantTypeDataModelFactoryInterface
    {
        return ServiceLocator::get('chameleon_system_shop.factory.variant_type_data_model_factory');
    }
    
    private function getVariantTypeValueDataModelFactory(): VariantTypeValueDataModelFactoryInterface
    {
        return ServiceLocator::get('chameleon_system_shop.factory.variant_type_value_data_model_factory');
    }

    private function getShopService(): ShopServiceInterface
    {
        return ServiceLocator::get('chameleon_system_shop.shop_service');
    }
}
