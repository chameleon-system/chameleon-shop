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
use ChameleonSystem\ShopBundle\ProductVariant\ProductVariantNameGeneratorInterface;

/**
 * overwritten to handle variant management.
/**/
class TCMSShopTableEditor_ShopArticle extends TCMSTableEditor
{
    /**
     * if the current article is a variant AND a variant set has been defined,
     * then mark all fields as hidden that are NOT activated through the set.
     *
     * @param TIterator $oFields
     */
    public function ProcessFieldsBeforeDisplay(&$oFields)
    {
        /** @var TdbShopArticle $product */
        $product = $this->oTable;
        if ($product->IsVariant()) {
            $oParent = &$product->GetFieldVariantParent();
            if (null != $oParent) {
                $oVariantSet = &$oParent->GetFieldShopVariantSet();
                if (!is_null($oVariantSet)) {
                    $aPermittedFields = $oVariantSet->GetMLTIdList('cms_field_conf', 'cms_field_conf_mlt');
                    $oPermittedFields = new TIterator();
                    $oFields->GoToStart();
                    while ($oField = $oFields->Next()) {
                        /** @var $oField TCMSField */
                        if (in_array($oField->oDefinition->id, $aPermittedFields)) {
                            $oPermittedFields->AddItem($oField);
                        }
                    }
                    $oFields = $oPermittedFields;
                }
            }
        }
    }

    /**
     * if the current article is a parent article, update all fixed fields of its variants
     * to contain the same info as the parent. Note: we do NOT copy property or mlt fields.
     *
     * @param TIterator  $oFields    holds an iterator of all field classes from DB table with the posted values or default if no post data is present
     * @param TCMSRecord $oPostTable holds the record object of all posted data
     */
    protected function PostSaveHook(&$oFields, &$oPostTable)
    {
        parent::PostSaveHook($oFields, $oPostTable);
        /** @var TdbShopArticle $product */
        $product = $this->oTable;
        if ($product->HasVariants()) {
            $oVariantSet = $product->GetFieldShopVariantSet();
            /** @var $oVariantSet TdbShopVariantSet */
            $aDoNotCopyFieldNames = array();
            if (!is_null($oVariantSet)) {
                $oDoNotCopyFields = $oVariantSet->GetFieldCmsFieldConfList();
                while ($oNotCopyField = $oDoNotCopyFields->Next()) {
                    $aDoNotCopyFieldNames[] = $oNotCopyField->fieldName;
                }
            }
            $aDoNotCopyFieldNames[] = 'id';
            $aDoNotCopyFieldNames[] = 'variant_parent_id';
            $aRawData = $oPostTable->sqlData;
            foreach ($aDoNotCopyFieldNames as $sFieldName) {
                if (array_key_exists($sFieldName, $aRawData)) {
                    unset($aRawData[$sFieldName]);
                }
            }
            $aRawData['variant_parent_is_active'] = $this->oTable->sqlData['active'];

            $oVariants = &$product->GetFieldShopArticleVariantsList(array(), false);
            $oVariants->GoToStart();
            while ($oVariant = $oVariants->Next()) {
                $aTmpRawData = $aRawData;
                $aTmpRawData['id'] = $oVariant->id;
                $oTableManager = new TCMSTableEditorManager();
                $oTableManager->Init($this->oTableConf->id, $oVariant->id);
                $oTableManager->AllowEditByAll($this->bAllowEditByAll);
                $oTableManager->ForceHiddenFieldWriteOnSave(true);
                $oTableManager->Save($aTmpRawData, $this->bSaveDataIsSqlData);
                unset($oTableManager);
            }
        } elseif ($product->IsVariant()) {
            $oVariantSet = $product->GetFieldShopVariantSet();
            $aDoNotCopyFieldNames = array();
            if (null !== $oVariantSet) {
                $oDoNotCopyFields = $oVariantSet->GetFieldCmsFieldConfList();
                while ($oNotCopyField = $oDoNotCopyFields->Next()) {
                    if ('readonly' !== $oNotCopyField->fieldModifier) {
                        $aDoNotCopyFieldNames[] = $oNotCopyField->fieldName;
                    }
                }
            }
            if (!in_array('name_variant_info', $aDoNotCopyFieldNames)) {
                $nameList = $this->getProductVariantNameGenerator()->generateNamesForAllLanguages($product);
                $originalLanguageId = $this->oTableConf->GetLanguage();
                foreach ($nameList as $languageId => $name) {
                    $this->oTableConf->SetLanguage($languageId);
                    $this->SaveField('name_variant_info', $name);
                }
                $this->oTableConf->SetLanguage($originalLanguageId);
            }
        }
        if (isset($oPostTable->sqlData['active']) && $oPostTable->sqlData['active']) {
            if (false === $product->fieldVariantParentIsActive) {
                $this->SaveField('variant_parent_is_active', $oPostTable->sqlData['active']);
            }
            if ($product->IsVariant()) {
                $this->activateParent();
            }
        }
    }

    private function activateParent()
    {
        /** @var TdbShopArticle $product */
        $product = $this->oTable;
        $parent = $product->GetFieldVariantParent();
        $parentTableEditor = TTools::GetTableEditorManager($parent->table, $parent->id);
        $parentTableEditor->SaveField('active', '1');
    }

    /**
     * here you can modify, clean or filter data before saving.
     *
     * @var array $postData
     *
     * @return array
     */
    protected function PrepareDataForSave($postData)
    {
        if (array_key_exists('active', $postData)) {
            if (!array_key_exists('variant_parent_id', $postData) || empty($postData['variant_parent_id'])) {
                $postData['variant_parent_is_active'] = $postData['active'];
            }
        }

        return $postData;
    }

    /**
     * determine if the current loaded record is a variant or a parent.
     *
     * @return bool
     */
    protected function isNewVariant()
    {
        $bIsNewVariant = false;
        if (!is_null($this->sRestriction) && !is_null($this->sRestrictionField) && '_id' == substr($this->sRestrictionField, -3)) {
            if ('variant_parent_id' == $this->sRestrictionField) {
                $bIsNewVariant = true;
            }
        }

        return $bIsNewVariant;
    }

    /**
     * we overwrite the insert method, so that when inserting variants, we realy perform
     * a copy of the parent.
     */
    public function Insert()
    {
        if ($this->isNewVariant()) {
            $this->sId = $this->sRestriction;
            $variant = $this->DatabaseCopy();
            /** @var TdbShopArticle $product */
            $product = $this->oTable;
            if (!$product->fieldActive) {
                $variantTableEditor = TTools::GetTableEditorManager('shop_article', $variant->id);
                $variantTableEditor->SaveField('variant_parent_is_active', '0');
            }
        } else {
            parent::Insert();
        }
    }

    protected function OnBeforeCopy()
    {
        parent::OnBeforeCopy();
        if ($this->isNewVariant()) {
            $this->oTable->sqlData['variant_parent_id'] = $this->sRestriction;
        }
    }

    /**
     * if we are creating a variant, do NOT copy the parents variants.
     *
     * @param TCMSField $oField
     * @param int       $sourceRecordID
     */
    public function CopyPropertyRecords($oField, $sourceRecordID)
    {
        if (!$this->isNewVariant() || 'shop_article_variants' != $oField->name) {
            parent::CopyPropertyRecords($oField, $sourceRecordID);
        }
    }

    /**
     * changes price of parent article to lowest variant.
     */
    public function UpdatePriceToLowestVariant()
    {
        /** @var TdbShopArticle $product */
        $product = $this->oTable;
        $oLowestPriceVariant = $product->GetLowestPricedVariant();
        if ($oLowestPriceVariant) {
            $aData = array('price' => $oLowestPriceVariant->fieldPriceFormated, 'price_reference' => $oLowestPriceVariant->fieldPriceReferenceFormated);
            $this->SaveFields($aData, false);
        }
    }

    /**
     * @return ProductVariantNameGeneratorInterface
     */
    private function getProductVariantNameGenerator()
    {
        return ServiceLocator::get('chameleon_system_shop.product_variant.product_variant_name_generator');
    }
}
