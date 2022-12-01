<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterItemList extends TAdbPkgShopListfilterItemList
{
    /**
     * create a new instance.
     *
     * @param string|null $sQuery
     * @param string|null $iLanguageId
     */
    public function __construct($sQuery = null, $iLanguageId = null)
    {
        $this->bAllowItemCache = true;
        parent::__construct($sQuery, $iLanguageId);
    }

    /**
     * return sql condition for the current filter list. optionaly excluding the element passed.
     *
     * @param TdbPkgShopListfilterItem $oExcludeItem
     * @param bool                     $bReturnAsArray - set to true if you want an array with the query parts instead of a string
     *
     * @return string|string[]
     * @psalm-return ($bReturnAsArray is true ? string[] : string)
     */
    public function GetQueryRestriction($oExcludeItem = null, $bReturnAsArray = false)
    {
        $sQuery = '';
        $aQueryItems = array();
        $iPointer = $this->getItemPointer();
        $this->GoToStart();
        while ($oItem = $this->Next()) {
            if (is_null($oExcludeItem) || !$oExcludeItem->IsSameAs($oItem)) {
                $sTmpQuery = trim($oItem->GetQueryRestrictionForActiveFilter());
                if (!empty($sTmpQuery)) {
                    $aQueryItems[] = $sTmpQuery;
                }
            }
        }
        $this->setItemPointer($iPointer);
        if (!$bReturnAsArray) {
            if (count($aQueryItems)) {
                $sQuery = '('.implode(') AND (', $aQueryItems).')';
            }

            return $sQuery;
        } else {
            return $aQueryItems;
        }
    }

    /**
     * return the setting of the list element as input fields (hidden).
     *
     * @return string
     */
    public function GetListSettingAsInputFields()
    {
        $sInput = '';
        $aInputItems = array();
        $iPointer = $this->getItemPointer();
        $this->GoToStart();
        while ($oItem = $this->Next()) {
            $sTmp = trim($oItem->GetActiveSettingAsHiddenInputField());
            if (!empty($sTmp)) {
                $aInputItems[] = $sTmp;
            }
        }
        $this->setItemPointer($iPointer);
        if (count($aInputItems)) {
            $sInput = implode("\n", $aInputItems);
        }

        return $sInput;
    }

    /**
     * return the setting of the list element as input fields (hidden).
     *
     * @return array
     */
    public function GetListSettingAsArray()
    {
        $aSettings = array();
        $iPointer = $this->getItemPointer();
        $this->GoToStart();
        while ($oItem = $this->Next()) {
            $aTmpSetting = $oItem->GetActiveSettingAsArray();
            $aSettings = array_merge_recursive($aSettings, $aTmpSetting);
        }
        $this->setItemPointer($iPointer);

        return $aSettings;
    }

    /**
     * factory returning an element for the list.
     *
     * @param array $aData
     *
     * @return TdbPkgShopListfilterItem
     */
    protected function _NewElement($aData)
    {
        $oElement = false;
        // try to fetch the element from _items first
        $sTableObject = $this->sTableObject;

        if (!empty($aData['pkg_shop_listfilter_item_type'])) {
            $oListfilterItemType = TdbPkgShopListfilterItemType::GetNewInstance($aData['pkg_shop_listfilter_item_type']);
            $sTableObject = $oListfilterItemType->fieldClass;
        }

        /**
         * @FIXME Empty if-statement?
         */
        if (!class_exists($sTableObject, false)) {
        }

        if (!is_null($this->sTableName)) {
            $oElement = new $sTableObject();
            $oElement->table = $this->sTableName;
        } else {
            $oElement = new $sTableObject();
        }
        /** @var $oElement TCMSRecord */
        $oElement->SetLanguage($this->iLanguageId);
        $oElement->LoadFromRow($aData);

        return $oElement;
    }
}
