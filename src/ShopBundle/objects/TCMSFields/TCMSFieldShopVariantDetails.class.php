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
use ChameleonSystem\CoreBundle\Util\UrlNormalization\UrlNormalizationUtil;
use ChameleonSystem\SecurityBundle\Service\SecurityHelperAccess;
use ChameleonSystem\SecurityBundle\Voter\CmsPermissionAttributeConstants;
use ChameleonSystem\ShopBundle\ProductVariant\ProductVariantNameGeneratorInterface;

/**
 * {@inheritdoc}
 *
 * manages the variant type and value details for an article
 *
 * @property TdbShopArticle $oTableRow
 * @property TdbCmsFieldConf $oDefinition
 */
class TCMSFieldShopVariantDetails extends TCMSFieldLookupMultiselectCheckboxes
{
    /**
     * @var int
     */
    protected $maxPossibleVariantCombinations = 400;

    /**
     * {@inheritdoc}
     */
    public function GetHTML()
    {
        $aData = [];
        $aData['sFieldName'] = $this->name;
        $aData['sAjaxURL'] = $this->GenerateAjaxURL();
        $oParent = null;
        /** @var TdbShopVariantSet $oVariantSet */
        $oVariantSet = $this->oTableRow->GetFieldShopVariantSet();

        // is this a variant, and is the variantset defined in the parent?
        if (!empty($this->oTableRow->fieldVariantParentId)) {
            /** @var $oParent TdbShopArticle */
            $oParent = $this->oTableRow->GetFieldVariantParent();
            $oVariantSet = $oParent->GetFieldShopVariantSet();
        }

        $aData['oParent'] = $oParent;
        $aData['oVariantSet'] = $oVariantSet;

        if (null === $oParent) {
            $html = $this->renderPrimaryArticleField($oVariantSet);
        } elseif (null === $oVariantSet) {
            $html = $this->RenderVariantDetails('vEditFieldVariantSetNotDefined', $aData);
        } else {
            $html = $this->RenderVariantDetails('vEditField', $aData);
        }

        return $html;
    }

    /**
     * @return string
     */
    protected function renderPrimaryArticleField(?TdbShopVariantSet $variantSet = null)
    {
        $viewRenderer = $this->getViewRenderer();

        $hasVariantSet = (null !== $variantSet);
        $viewRenderer->AddSourceObject('hasVariantSet', $hasVariantSet);
        $viewRenderer->AddSourceObject('fieldName', $this->name);
        $viewRenderer->AddSourceObject('ajaxUrl', $this->GenerateAjaxURL());
        $viewRenderer->AddSourceObject('maxPossibleVariantCombinations', $this->maxPossibleVariantCombinations);

        if (true === $hasVariantSet) {
            $variantTypes = $variantSet->GetFieldShopVariantTypeList();
            $variantTypeParameters = [];
            while ($variantType = $variantTypes->Next()) {
                $variantValueList = $variantType->GetFieldShopVariantTypeValueList();

                $typeVariants = [];
                while ($variantValue = $variantValueList->Next()) {
                    $typeVariants[$variantValue->id] = $variantValue->GetName();
                }

                asort($typeVariants);
                $variantTypeParameters[$variantType->GetName()] = ['variantTypeName' => $variantType->GetName(), 'variantTypeId' => $variantType->id, 'typeVariants' => $typeVariants];
                ksort($variantTypeParameters);
            }

            $viewRenderer->AddSourceObject('variantTypeParameters', $variantTypeParameters);
        }

        return $viewRenderer->Render('@ChameleonSystemShop/snippets-cms/TCMSFieldShopVariantDetails/main-product.html.twig');
    }

    /**
     * render field details.
     *
     * @param string $sViewName
     * @param array $aCallTimeVars
     *
     * @return string
     */
    protected function RenderVariantDetails($sViewName, $aCallTimeVars = [])
    {
        $oView = new TViewParser();
        $aActivatedIds = $this->oTableRow->GetMLTIdList('shop_variant_type_value', 'shop_variant_type_value_mlt');
        $oView->AddVar('aActivatedIds', $aActivatedIds);
        $oView->AddVar('oField', $this);
        $oView->AddVarArray($aCallTimeVars);

        return $oView->RenderObjectView($sViewName, 'TCMSFields/TCMSFieldShopVariantDetails', 'Core');
    }

    /**
     * @param string $sTableName
     * @param string $iTargetRecord
     *
     * @return string
     */
    public function GetEditLink($sTableName, $iTargetRecord)
    {
        static $aTableEditorConfs = [];
        if (!in_array($sTableName, $aTableEditorConfs)) {
            $oTableConf = TdbCmsTblConf::GetNewInstance();
            $oTableConf->LoadFromField('name', $sTableName);
            $aTableEditorConfs[$sTableName] = $oTableConf->id;
        }
        /** @var SecurityHelperAccess $securityHelper */
        $securityHelper = ServiceLocator::get(SecurityHelperAccess::class);

        if (false === $securityHelper->isGranted(CmsPermissionAttributeConstants::TABLE_EDITOR_EDIT, $sTableName)) {
            return '';
        }

        $sLinkParams = [
            'pagedef' => 'tableeditor',
            'tableid' => $aTableEditorConfs[$sTableName],
            'id' => urlencode($iTargetRecord),
        ];
        $sLink = PATH_CMS_CONTROLLER.'?'.TTools::GetArrayAsURLForJavascript($sLinkParams);

        return $sLink;
    }

    /**
     * {@inheritdoc}
     */
    public function GetDatabaseCopySQL()
    {
        return '';
    }

    /**
     * overwrites sql method to ensure that the connecting mlt is filled properly.
     *
     * {@inheritdoc}
     */
    public function GetSQL()
    {
        /**
         * @var array $aNewValues
         */
        $aNewValues = $this->getInputFilterUtil()->getFilteredInput($this->name.'_new');

        if (!is_array($this->data)) {
            $this->data = [];
        } elseif (array_key_exists('x', $this->data)) {
            unset($this->data['x']);
        }

        $oEditorObjectConf = TdbCmsTblConf::GetNewInstance();
        $oEditorObjectConf->LoadFromField('name', 'shop_variant_type_value');

        if (!is_array($aNewValues)) {
            return false;
        }

        foreach ($aNewValues as $sShopVariantTypeId => $sNewValueName) {
            $sNewValueName = trim($sNewValueName);
            if (!empty($sNewValueName)) {
                // first make sure the value does not exist already
                $oValue = TdbShopVariantTypeValue::GetNewInstance();
                if ($oValue->LoadFromFields(
                    ['name' => $sNewValueName, 'shop_variant_type_id' => $sShopVariantTypeId]
                )
                ) {
                    $this->data[$sShopVariantTypeId] = $oValue->id;
                } else {
                    // need to create entry
                    $aNewItemData = [
                        'shop_variant_type_id' => $sShopVariantTypeId,
                        'name' => $sNewValueName,
                        'url_name' => $this->getUrlNormalizationUtil()->normalizeUrl($sNewValueName),
                    ];
                    $oTableManager = new TCMSTableEditorManager();
                    $oTableManager->Init($oEditorObjectConf->id);
                    $oTableManager->Save($aNewItemData);
                    $this->data[$sShopVariantTypeId] = $oTableManager->sId;
                }
            }
        }

        // now save connecting records.... we can use the parent method
        return parent::GetSQL();
    }

    /**
     * called via ajax
     * generates all variants that where checked in the field matrix.
     *
     * @return string
     */
    public function generateVariants()
    {
        /**
         * @var array|null $aVariantParameters
         */
        $aVariantParameters = $this->getInputFilterUtil()->getFilteredInput('variantParameters');
        if (null === $aVariantParameters) {
            return '';
        }

        foreach ($aVariantParameters as $aVariant) {
            $oTableEditorManager = new TCMSTableEditorManager();
            $oTableEditorManager->sRestrictionField = 'variant_parent_id';
            $oTableEditorManager->sRestriction = $this->oTableRow->id;
            $oTableEditorManager->Init($this->oDefinition->fieldCmsTblConfId, $this->oTableRow->id);
            $oTableEditorManager->AllowEditByAll(true);
            $oTableEditorManager->Insert();
            $oTableEditorManager->oTableEditor->SaveFields($this->mapVariantArticleData($aVariant));

            foreach ($aVariant['variantIDs'] as $sVariantID) {
                $oTableEditorManager->AddMLTConnection($this->name, $sVariantID);
            }

            $nameList = $this->getProductVariantNameGenerator()->generateNamesForAllLanguages($oTableEditorManager->oTableEditor->oTable);
            $originalLanguageId = $oTableEditorManager->oTableEditor->oTableConf->GetLanguage();
            foreach ($nameList as $languageId => $name) {
                $oTableEditorManager->oTableEditor->oTableConf->SetLanguage($languageId);
                $oTableEditorManager->SaveField('name_variant_info', $name);
            }
            $oTableEditorManager->oTableEditor->oTableConf->SetLanguage($originalLanguageId);
        }

        return ServiceLocator::get('translator')->trans('chameleon_system_shop.field_shop_variant_details.msg_created_variants', ['%generatedVariantsCount%' => count($aVariantParameters)]);
    }

    /**
     * @return array
     */
    protected function mapVariantArticleData(array $variantData)
    {
        return [
            'price' => $variantData['variantPrice'],
        ];
    }

    /**
     * Returns false if the variant combinations reaches $this->maxPossibleVariantCombinations (default 400).
     * This is because of a limit of 1000 form fields as max for default PHP configurations.
     *
     * @return string|bool
     */
    public function getPossibleVariantCombinations()
    {
        $aData = [];
        /** @var TdbShopVariantSet $variantSet */
        $aVariantTypeNames = [];
        $aExistingVariantCombinations = [];

        $variantSet = $this->oTableRow->GetFieldShopVariantSet();
        $aVariantSurcharge = [];
        $aVariantParameter = [];

        $possibleVariantsCount = 1;
        if (null === $variantSet) {
            return '';
        }

        $variantTypes = $variantSet->GetFieldShopVariantTypeList();
        while ($variantType = $variantTypes->Next()) {
            $aVariantTypeNames[] = $variantType->GetName();

            $variantValueList = $this->getVariantValueList($variantType);
            $possibleVariantsCount *= $variantValueList->Length();

            if ($possibleVariantsCount > $this->maxPossibleVariantCombinations) {
                // whoops! o.k. looks like you should build a configurator instead of variants
                return false;
            }

            $aTypeVariants = [];
            while ($oValue = $variantValueList->Next()) {
                $aTypeVariants[] = $oValue->id.'|'.$oValue->GetName();
                $dSurCharge = $oValue->sqlData['surcharge'];
                if (null === $dSurCharge) {
                    $dSurCharge = 0;
                }
                $aVariantSurcharge[$oValue->id] = $dSurCharge;
            }

            $aVariantParameter[] = $aTypeVariants;
        }

        $aVariantMatrix = $this->generateVariantCombinations($aVariantParameter);

        // get all existing variants
        $oVariantList = $this->oTableRow->GetFieldShopArticleVariantsList([], false);

        /**
         * @var TdbShopArticle $oVariant
         */
        while ($oVariant = $oVariantList->Next()) {
            $oVariantTypeValueList = $oVariant->GetFieldShopVariantTypeValueList();
            $aVariantValues = [];
            while ($oVariantTypeValue = $oVariantTypeValueList->Next()) {
                $aVariantValues[] = $oVariantTypeValue->id;
            }

            asort($aVariantValues);
            $sVariantValueCombination = implode('|', $aVariantValues);

            $aExistingVariantCombinations[] = $sVariantValueCombination;
        }

        $aData['oVariantSet'] = $variantSet;
        $aData['aVariantMatrix'] = $aVariantMatrix;
        $aData['aVariantTypeNames'] = $aVariantTypeNames;
        $aData['aExistingVariantCombinations'] = $aExistingVariantCombinations;
        $aData['sFieldName'] = $this->name;
        $aData['sAjaxURL'] = $this->GenerateAjaxURL();
        $aData['oArticle'] = $this->oTableRow;
        $aData['aVariantSurcharge'] = $aVariantSurcharge;

        return $this->RenderVariantDetails('vShowPossibleVariants', $aData);
    }

    /**
     * @return TdbShopVariantTypeValueList
     */
    private function getVariantValueList(TdbShopVariantType $variantType)
    {
        $dbConnection = $this->getDatabaseConnection();

        /**
         * @var array $selectedVariantTypeValues
         */
        $selectedVariantTypeValues = $this->getInputFilterUtil()->getFilteredInput('variantTypeValues');

        if (false === is_array($selectedVariantTypeValues) || false === array_key_exists($variantType->id, $selectedVariantTypeValues)) {
            return $variantType->GetFieldShopVariantTypeValueList();
        }

        $selectedVariantValues = $selectedVariantTypeValues[$variantType->id];

        $query = 'SELECT * 
                            FROM `shop_variant_type_value`
                           WHERE `shop_variant_type_value`.`id` IN (%s) 
                        ORDER BY %s ASC 
                            ';

        $escapedSelectedVariantValues = implode(',', array_map([$dbConnection, 'quote'], $selectedVariantValues));
        $escapedFieldName = $dbConnection->quoteIdentifier($variantType->fieldShopVariantTypeValueCmsfieldname);
        $query = sprintf($query, $escapedSelectedVariantValues, $escapedFieldName);

        return TdbShopVariantTypeValueList::GetList($query);
    }

    /**
     * generates all variant combinations as multidimensional array from variant parameter array.
     * example: $aVariantParameter = array(array('red', 'blue', 'green'),array('S', 'L', 'XL'),array('car', 'truck', 'van'));.
     *
     * @param array $aVariantParameter - multidimensional array of variant parameters
     *
     * @return array
     */
    protected function generateVariantCombinations($aVariantParameter)
    {
        $out = [];
        if (1 === count($aVariantParameter)) {
            $x = array_shift($aVariantParameter);
            foreach ($x as $v) {
                $out[] = [$v];
            }

            return $out;
        }
        foreach ($aVariantParameter as $k => $v) {
            $b = $aVariantParameter;
            unset($b[$k]);
            $x = $this->generateVariantCombinations($b);
            foreach ($v as $v1) {
                foreach ($x as $v2) {
                    $out[] = array_merge([$v1], $v2);
                }
            }
            break;
        }

        return $out;
    }

    /**
     * sets methods that are allowed to be called via URL (ajax call).
     *
     * @return void
     */
    protected function DefineInterface()
    {
        parent::DefineInterface();
        $externalFunctions = ['generateVariants', 'getPossibleVariantCombinations'];
        $this->methodCallAllowed = array_merge($this->methodCallAllowed, $externalFunctions);
    }

    /**
     * @return UrlNormalizationUtil
     */
    private function getUrlNormalizationUtil()
    {
        return ServiceLocator::get('chameleon_system_core.util.url_normalization');
    }

    /**
     * @return InputFilterUtilInterface
     */
    private function getInputFilterUtil()
    {
        return ServiceLocator::get('chameleon_system_core.util.input_filter');
    }

    /**
     * @return ProductVariantNameGeneratorInterface
     */
    private function getProductVariantNameGenerator()
    {
        return ServiceLocator::get('chameleon_system_shop.product_variant.product_variant_name_generator');
    }

    /**
     * @return ViewRenderer
     */
    private function getViewRenderer()
    {
        return ServiceLocator::get('chameleon_system_view_renderer.view_renderer');
    }
}
