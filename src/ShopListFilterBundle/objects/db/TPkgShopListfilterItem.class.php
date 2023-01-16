<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\core\DatabaseAccessLayer\QueryModifierOrderByInterface;
use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;

class TPkgShopListfilterItem extends TAdbPkgShopListfilterItem
{
    const VIEW_PATH = 'pkgShopListfilter/views/db/ListfilterItems';

    const URL_PARAMETER_FILTER_DATA = 'aPkgShopListfilter';

    /**
     * you need to set this to the field name you want to filter by.
     *
     * @var string
     */
    protected $sItemFieldName = '';

    /**
     * the item list filtered by all other listfilter item aside from this one.
     *
     * @var TCMSRecordList|null
     */
    protected $oItemListFilteredByOtherItems;

    /**
     * the current user selection for the filter.
     *
     * @var array|null
     */
    protected $aActiveFilterData;

    /**
     * @var bool
     */
    protected $bIsActive = false;

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function PostLoadHook()
    {
        parent::PostLoadHook();
        $this->InitDataFromPostGet();
    }

    /**
     * called when an object recovers from serialization.
     *
     * @return void
     */
    protected function PostWakeUpHook()
    {
        parent::PostWakeUpHook();
        $this->InitDataFromPostGet();
    }

    /**
     * @return void
     */
    protected function InitDataFromPostGet()
    {
        $this->aActiveFilterData = null;
        $oGlobal = TGlobal::instance();
        if ($oGlobal->UserDataExists(TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA)) {
            $aData = $oGlobal->GetUserData(TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA);
            if (array_key_exists($this->id, $aData)) {
                $this->aActiveFilterData = $aData[$this->id];
                // remove empty values
                $this->aActiveFilterData = $this->RemoveEmptyValues($this->aActiveFilterData);
                if (!is_null($this->aActiveFilterData)) {
                    $this->bIsActive = true;
                }
            }
        }
        $this->OverloadViewSettings();
    }

    /**
     * @param array<string, mixed>|string $aData
     * @psalm-param array<string, mixed>|'' $aData
     * @return array<string, mixed>|null - Returns `null` if the resulting array would be empty or if an empty string was passed as input.
     */
    protected function RemoveEmptyValues($aData)
    {
        if (is_array($aData)) {
            foreach (array_keys($aData) as $sKey) {
                $aData[$sKey] = $this->RemoveEmptyValues($aData[$sKey]);
                if (is_null($aData[$sKey])) {
                    unset($aData[$sKey]);
                }
            }
            if (0 == count($aData)) {
                $aData = null;
            }
        } else {
            if ('' === $aData) {
                $aData = null;
            }
        }

        return $aData;
    }

    /**
     * overload view settings from filter-type if nothing is set in this filter-item.
     *
     * @return void
     */
    protected function OverloadViewSettings()
    {
        if (empty($this->fieldView)) {
            $oListFilterItemType = $this->GetFieldPkgShopListfilterItemType();
            if (!is_null($oListFilterItemType) && !TGlobal::IsCMSMode()) {
                $this->sqlData['view'] = $oListFilterItemType->fieldView;
                $this->fieldView = $this->sqlData['view'];
                $this->sqlData['view_class_type'] = $oListFilterItemType->fieldViewClassType;
                $this->fieldViewClassType = $this->sqlData['view_class_type'];
            }
        }
    }

    /**
     * return the url name for the input field that holds the data for this filter.
     *
     * @return string
     */
    public function GetURLInputName()
    {
        return urlencode(TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA).'['.urlencode($this->id).']';
    }

    /**
     * return setting of element as hidden input fields.
     *
     * @return string
     */
    public function GetActiveSettingAsHiddenInputField()
    {
        /**
         * @psalm-suppress InvalidArgument
         * @FIXME Passing an array (`aActiveFilterData`) to `TGlobal::OutHTML` which expects a string. Due to `htmlentities` being used in that method, this will return an empty string up to PHP7.4 but result in an fatal error in PHP8+
         */
        return '<input type="hidden" name="'.TGlobal::OutHTML($this->GetURLInputName()).'" value="'.TGlobal::OutHTML($this->aActiveFilterData).'" />';
    }

    /**
     * return the settings as an array.
     *
     * @return array
     */
    public function GetActiveSettingAsArray()
    {
        $aData = array();
        if (is_array($this->aActiveFilterData) || !empty($this->aActiveFilterData)) {
            $aData = array(TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA => array($this->id => $this->aActiveFilterData));
        }

        return $aData;
    }

    /**
     * return url that sets the current filter to some value.
     *
     * @param string $sValue
     *
     * @return string
     */
    public function GetAddFilterURL($sValue)
    {
        // @todo: the sorting is lost here (#29269)
        $oActiveListfilter = TdbPkgShopListfilter::GetActiveInstance();
        $aURLData = $oActiveListfilter->GetCurrentFilterAsArray();
        $aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA][$this->id] = $sValue;
        $aURLData[TdbPkgShopListfilter::URL_PARAMETER_IS_NEW_REQUEST] = '1';

        return $this->getActivePageService()->getLinkToActivePageRelative($aURLData);
    }

    /**
     * set the item list filtered by all other filter items aside from this one.
     *
     * @param TCMSRecordList $oItemListFilteredByOtherItems
     *
     * @return void
     */
    public function SetFilteredItemList($oItemListFilteredByOtherItems)
    {
        $this->oItemListFilteredByOtherItems = $oItemListFilteredByOtherItems;
    }

    /**
     * return option as assoc array (name=>count).
     *
     * @return array
     */
    public function GetOptions()
    {
        $aOptions = $this->GetFromInternalCache('aOptions');
        if (is_null($aOptions)) {
            $aOptions = array();
            $sIdSelect = $this->GetResultSetBaseQuery();

            if (PKG_SHOP_LISTFILTER_ENABLE_COUNT_PER_FILTER_ITEM) {
                $sItemQuery = "SELECT `shop_article`.`{$this->sItemFieldName}` AS value, COUNT( `shop_article`.`{$this->sItemFieldName}` ) AS matches
                           FROM `shop_article`
                     INNER JOIN ({$sIdSelect}) AS Z ON `shop_article`.`id` = Z.`id`
                       GROUP BY `shop_article`.`{$this->sItemFieldName}`
                          ";
            } else {
                $sItemQuery = "SELECT DISTINCT `shop_article`.`{$this->sItemFieldName}` AS value, 1 AS matches
                           FROM `shop_article`
                     INNER JOIN ({$sIdSelect}) AS Z ON `shop_article`.`id` = Z.`id`
                          ";
            }
            $tRes = MySqlLegacySupport::getInstance()->query($sItemQuery);
            while ($aOption = MySqlLegacySupport::getInstance()->fetch_assoc($tRes)) {
                $aOptions[$aOption['value']] = $aOption['matches'];
            }
            $this->OrderOptions($aOptions);
            $this->SetInternalCache('aOptions', $aOptions);
        }

        return $aOptions;
    }

    /**
     * render the filter.
     *
     * @param string $sViewName     - name of the view
     * @param string $sViewType     - where to look for the view
     * @param array  $aCallTimeVars - optional parameters to pass to render method
     *
     * @return string
     */
    public function Render($sViewName = 'standard', $sViewType = 'Customer', $aCallTimeVars = array())
    {
        $oView = new TViewParser();
        /** @var $oView TViewParser */
        $oView->AddVar('oListItem', $this);
        $oView->AddVar('oFilterType', $this->GetFieldPkgShopListfilterItemType());
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, TdbPkgShopListfilterItem::VIEW_PATH, $sViewType);
    }

    /**
     * use this method to add any variables to the render method that you may
     * require for some view.
     *
     * @param string $sViewName - the view being requested
     * @param string $sViewType - the location of the view (Core, Custom-Core, Customer)
     *
     * @return array
     */
    protected function GetAdditionalViewVariables($sViewName, $sViewType)
    {
        $aViewVariables = array();

        return $aViewVariables;
    }

    /**
     * return the query restriction for active filter. returns false if there
     * is no active restriction for this item.
     *
     * @return string|false
     */
    public function GetQueryRestrictionForActiveFilter()
    {
        return '';
    }

    /**
     * gets the query from the active article list and changes it so the query
     * only returns the article ids found. The resulting query can be used
     * by filter objects to select available options for all articles found.
     *
     * @return string
     */
    protected function GetResultSetBaseQuery()
    {
        static $sBaseQuery = null;
        if (is_null($sBaseQuery)) {
            $sQuery = $this->oItemListFilteredByOtherItems->GetActiveQuery();
            $sTmpQuery = mb_strtoupper($sQuery);
            $sTmpQuery = str_replace("\n", ' ', $sTmpQuery);
            $iFromPos = strpos($sTmpQuery, ' FROM ');
            $sBaseQuery = 'SELECT DISTINCT `shop_article`.`id` '.substr($sQuery, $iFromPos);
            //        $sBaseQuery = "`_tmp_category_article`";
            $sBaseQuery = $this->getQueryModifierOrderByService()->getQueryWithoutOrderBy($sBaseQuery);
        }

        return $sBaseQuery;
    }

    /**
     * @return bool
     */
    public function IsActive()
    {
        return $this->bIsActive;
    }

    /**
     * @param array<string, mixed> $aOptions
     *
     * @return void
     */
    protected function OrderOptions(array &$aOptions): void
    {
        $SortOrder = SORT_NUMERIC;
        reset($aOptions);
        if (is_array($aOptions)) {
            foreach ($aOptions as $sKey => $sValue) {
                if (!is_numeric($sKey)) {
                    $SortOrder = SORT_STRING;
                    break;
                }
            }
            ksort($aOptions, $SortOrder);
        }
    }

    /**
     * @return QueryModifierOrderByInterface
     */
    protected function getQueryModifierOrderByService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.query_modifier.order_by');
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
