<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopVariantDisplayHandler extends TAdbShopVariantDisplayHandler
{
    const VIEW_PATH_BASE = 'pkgShop/views/db/TShopVariantDisplayHandler';

    /**
     * return an instance of the handler type for the given id.
     *
     * @param string $sId
     *
     * @example TdbShopVariantDisplayHandler
     */
    public static function GetInstance($sId)
    {
        $oRealObject = null;
        $oObject = TdbShopVariantDisplayHandler::GetNewInstance();
        /** @var $oObject TdbShopVariantDisplayHandler */
        if ($oObject->Load($sId)) {
            $sClassName = $oObject->fieldClass;
            $oRealObject = new $sClassName();
            $oRealObject->LoadFromRow($oObject->sqlData);
        }

        return $oRealObject;
    }

    /**
     * return the current active type select of the user (or active article).
     *
     * @param bool $bOnlyCheckPost
     *
     * @return array
     *
     * @deprecated since 6.2.13 - usages removed (replaced by ProductVariantServiceInterface::getProductBasedOnSelection())
     */
    public static function GetActiveVariantTypeSelection($bOnlyCheckPost = false)
    {
        static $aVariantTypeSelection = array(null, null);
        $index = $bOnlyCheckPost ? 1 : 0;
        $aSelectedTypeValues = $aVariantTypeSelection[$index];
        if (null === $aSelectedTypeValues) {
            $oGlobal = TGlobal::instance();
            $aSelectedTypeValues = $oGlobal->GetUserData(TdbShopVariantType::URL_PARAMETER);
            $oActiveArticle = TdbShop::GetActiveItem();

            if (!is_array($aSelectedTypeValues) && !$bOnlyCheckPost) {
                $aSelectedTypeValues = array();
                if (!is_null($oActiveArticle) && $oActiveArticle->IsVariant()) {
                    $oVariantSet = &$oActiveArticle->GetFieldShopVariantSet();
                    $oVariantTypes = $oVariantSet->GetFieldShopVariantTypeList();
                    $aSelectedTypeValues = array();
                    while ($oVariantType = $oVariantTypes->Next()) {
                        $aSelectedTypeValues[$oVariantType->id] = $oActiveArticle->GetVariantTypeActiveValue($oVariantType->id);
                    }
                }
            }
            if (is_array($aSelectedTypeValues)) {
                reset($aSelectedTypeValues);
                foreach ($aSelectedTypeValues as $sKey => $sVal) {
                    if (empty($sVal)) {
                        unset($aSelectedTypeValues[$sKey]);
                    }
                }
            }

            if (!is_array($aSelectedTypeValues)) {
                $aSelectedTypeValues = array();
            }

            if (!is_null($oActiveArticle) && !$oActiveArticle->IsVariant() && $oActiveArticle->HasVariants()) {
                $oVariantSet = $oActiveArticle->GetFieldShopVariantSet();
                if ($oVariantSet) {
                    $oVariantTypes = $oVariantSet->GetFieldShopVariantTypeList();
                    $aTmpSelectValue = array();
                    while ($oVariantType = $oVariantTypes->Next()) {
                        // if there is only one option - auto pick it
                        $oAvailableValues = $oActiveArticle->GetVariantValuesAvailableForType($oVariantType, $aTmpSelectValue);
                        if (null !== $oAvailableValues && array_key_exists($oVariantType->id, $aSelectedTypeValues) && !empty($aSelectedTypeValues[$oVariantType->id])) {
                            if (!$oAvailableValues->IsInList($aSelectedTypeValues[$oVariantType->id])) {
                                unset($aSelectedTypeValues[$oVariantType->id]);
                            }
                        }

                        if (!array_key_exists($oVariantType->id, $aSelectedTypeValues) || empty($aSelectedTypeValues[$oVariantType->id])) {
                            if ($oAvailableValues) {
                                $oAvailableValues->GoToStart();
                                if (1 == $oAvailableValues->Length()) {
                                    $oTmp = $oAvailableValues->Current();
                                    $aSelectedTypeValues[$oVariantType->id] = $oTmp->id;
                                }
                            }
                        }
                        if (array_key_exists($oVariantType->id, $aSelectedTypeValues)) {
                            $aTmpSelectValue[$oVariantType->id] = $aSelectedTypeValues[$oVariantType->id];
                        }
                    }
                }
            }
        }
        if (is_array($aSelectedTypeValues) && 0 == count($aSelectedTypeValues)) {
            $aSelectedTypeValues = false;
        }
        $aVariantTypeSelection[$index] = $aSelectedTypeValues;

        return $aSelectedTypeValues;
    }

    /**
     * return the first article matchin the current selection given a parent article
     * this can be usefull if a user has selected color but not size for a clothing article and you
     * want to display the images of the color variant.
     *
     * @param TdbShopArticle $oParentArticle
     * @param bool           $bOnlyIfAPartialSelectionExists - set to true if you also want to fetch the first variant if no
     *                                                       user selection (such as color) exists
     *
     * @return TdbShopArticle
     *
     * @deprecated since 6.2.13 - replaced by ProductVariantServiceInterface::getProductBasedOnSelection()
     */
    public static function GetArticleMatchingCurrentSelection(TdbShopArticle &$oParentArticle, $bOnlyIfAPartialSelectionExists = true)
    {
        $aActiveSelection = TdbShopVariantDisplayHandler::GetActiveVariantTypeSelection(true);
        if (!$bOnlyIfAPartialSelectionExists || (is_array($aActiveSelection) && count($aActiveSelection))) {
            return $oParentArticle->GetVariantFromValues($aActiveSelection);
        } else {
            return null;
        }
    }

    /**
     * render the filter.
     *
     * @param TdbShopArticle $oArticle      - the article object for which we want to render the view
     * @param string         $sViewName     - name of the view
     * @param string         $sViewType     - where to look for the view
     * @param array          $aCallTimeVars - optional parameters to pass to render method
     *
     * @return string
     */
    public function Render(&$oArticle, $sViewName = 'vStandard', $sViewType = 'Customer', $aCallTimeVars = array())
    {
        $oView = new TViewParser();

        $oVariantSet = &$oArticle->GetFieldShopVariantSet();

        $oView->AddVar('oDisplayHandler', $this);
        $oView->AddVar('aSelectedTypeValues', self::GetActiveVariantTypeSelection());
        $oView->AddVar('oVariantSet', $oVariantSet);
        $oView->AddVar('oArticle', $oArticle);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($oArticle, $sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, TdbShopVariantDisplayHandler::VIEW_PATH_BASE.'/'.get_class($this), $sViewType);
    }

    /**
     * use this method to add any variables to the render method that you may
     * require for some view.
     *
     * @param TdbShopArticle $oArticle  - the article object for which we want to render the view
     * @param string         $sViewName - the view being requested
     * @param string         $sViewType - the location of the view (Core, Custom-Core, Customer)
     *
     * @return array
     */
    protected function GetAdditionalViewVariables(&$oArticle, $sViewName, $sViewType)
    {
        return array();
    }

    /**
     * Add view based clear cache triggers for the Render method here.
     *
     * @param array          $aClearTriggers - clear trigger array (with current contents)
     * @param TdbShopArticle $oArticle       - the article object for which we want to render the view
     * @param string         $sViewName      - view being requested
     * @param string         $sViewType      - location of the view (Core, Custom-Core, Customer)
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function AddClearCacheTriggers(&$aClearTriggers, &$oArticle, $sViewName, $sViewType)
    {
        $aClearTriggers[] = array('table' => $this->table, 'id' => $this->id);
        $aClearTriggers[] = array('table' => $oArticle->table, 'id' => $oArticle->id);

        // also react to ANY changes in the variant def tables... and any related articles
        $aClearTriggers[] = array('table' => 'shop_variant_type', 'id' => '');
        $aClearTriggers[] = array('table' => 'shop_variant_type_value', 'id' => '');

        $aCacheInfos = TdbShopArticle::GetCacheRelevantTables();
        foreach ($aCacheInfos as $sTableName) {
            $aClearTriggers[] = array('table' => $sTableName, 'id' => '');
        }
    }
}
