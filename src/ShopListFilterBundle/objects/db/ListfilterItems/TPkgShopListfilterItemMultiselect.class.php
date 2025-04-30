<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterItemMultiselect extends TdbPkgShopListfilterItem
{
    /**
     * you need to set this to the table name of the connected table.
     *
     * @var string
     */
    protected $sItemTableName = 'shop_manufacturer';

    /**
     * you need to set this to the field name in the article table (note: the field is not derived from
     * the table name since this may differ).
     *
     * @var string
     */
    protected $sItemFieldName = 'shop_manufacturer_id';

    /**
     * return true if the item is selected.
     *
     * @param string $sItemName
     *
     * @return bool
     */
    public function IsSelected($sItemName)
    {
        $bIsSelected = false;
        if (is_array($this->aActiveFilterData)) {
            $bIsSelected = in_array($sItemName, $this->aActiveFilterData);
        } elseif (!empty($this->aActiveFilterData) && is_string($this->aActiveFilterData)) {
            $bIsSelected = ($sItemName == $this->aActiveFilterData);
        }

        return $bIsSelected;
    }

    /**
     * return true if a item is selected.
     *
     * @return bool
     */
    public function IsActiveFilter()
    {
        $bIsSelected = false;
        if (is_array($this->aActiveFilterData)) {
            $bIsSelected = true;
        }

        return $bIsSelected;
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
            $aOptions = [];
            $sIdSelect = $this->GetResultSetBaseQuery();
            $databaseConnection = $this->getDatabaseConnection();
            $quotedItemTableName = $databaseConnection->quoteIdentifier($this->sItemTableName);
            $quotedItemFieldName = $databaseConnection->quoteIdentifier($this->sItemFieldName);
            $quotedTargetTableNameField = $databaseConnection->quoteIdentifier($this->GetTargetTableNameField());
            if (PKG_SHOP_LISTFILTER_ENABLE_COUNT_PER_FILTER_ITEM) {
                $sItemQuery = "
             SELECT itemtable.$quotedTargetTableNameField AS attribute, COUNT(itemtable.$quotedTargetTableNameField) AS matches
               FROM $quotedItemTableName AS itemtable
         INNER JOIN `shop_article` ON `shop_article`.$quotedItemFieldName = itemtable.`id`
         INNER JOIN ($sIdSelect) AS Z ON itemtable.`id` = Z.$quotedItemFieldName
           GROUP BY itemtable.$quotedTargetTableNameField
          ";
            } else {
                $sItemQuery = "
             SELECT DISTINCT itemtable.$quotedTargetTableNameField AS attribute, 1 AS matches
               FROM $quotedItemTableName AS itemtable
         INNER JOIN `shop_article` ON `shop_article`.$quotedItemFieldName = itemtable.`id`
         INNER JOIN ($sIdSelect) AS Z ON itemtable.`id` = Z.$quotedItemFieldName
          ";
            }

            $tRes = MySqlLegacySupport::getInstance()->query($sItemQuery);
            while ($aOption = MySqlLegacySupport::getInstance()->fetch_assoc($tRes)) {
                if (!empty($aOption['attribute'])) {
                    $aOptions[$aOption['attribute']] = $aOption['matches'];
                }
            }
            $this->OrderOptions($aOptions);
            $this->SetInternalCache('aOptions', $aOptions);
        }

        return $aOptions;
    }

    /**
     * gets the query from the active article list and changes it so the query
     * only returns the article ids found. The resulting query can be used
     * by filter objects to select available options for all articles found.
     *
     * @param string $sFieldName return query with this field as selected field in table 'shop_article'
     *
     * @return string
     */
    protected function GetResultSetBaseQuery($sFieldName = '')
    {
        $sSearchingFieldName = $this->sItemFieldName;
        if (!empty($sFieldName)) {
            $sSearchingFieldName = $sFieldName;
        }
        $databaseConnection = $this->getDatabaseConnection();
        $quotedSearchingFieldName = $databaseConnection->quoteIdentifier($sSearchingFieldName);
        $sQuery = $this->oItemListFilteredByOtherItems->GetActiveQuery();
        $sTmpQuery = mb_strtoupper($sQuery);
        $sTmpQuery = str_replace("\n", ' ', $sTmpQuery);
        $iFromPos = strpos($sTmpQuery, ' FROM ');
        $sBaseQuery = "SELECT DISTINCT `shop_article`.$quotedSearchingFieldName ".substr($sQuery, $iFromPos);
        $sBaseQuery = $this->getQueryModifierOrderByService()->getQueryWithoutOrderBy($sBaseQuery);

        return $sBaseQuery;
    }

    /**
     * gets the query from the active article list and changes it so the query
     * only returns the article ids found. The resulting query can be used
     * by filter objects to select available options for all articles found.
     *
     * @return string
     */
    protected function getAvailableArticlesQuery()
    {
        $sQuery = $this->oItemListFilteredByOtherItems->GetActiveQuery();
        $sTmpQuery = mb_strtoupper($sQuery);
        $sTmpQuery = str_replace("\n", ' ', $sTmpQuery);
        $iFromPos = strpos($sTmpQuery, ' FROM ');
        $sBaseQuery = 'SELECT DISTINCT `shop_article`.`id` '.substr($sQuery, $iFromPos);

        return $sBaseQuery;
    }

    /**
     * return the query restriction for active filter. returns false if there
     * is no active restriction for this item.
     *
     * @return string|null
     */
    public function GetQueryRestrictionForActiveFilter()
    {
        /** @var string|null $sQuery */
        $sQuery = $this->GetFromInternalCache('sQueryRestrictionForActiveFilter');

        if (is_null($sQuery)) {
            $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');
            $aValues = $this->aActiveFilterData;

            if (is_array($aValues) && count($aValues) > 0) {
                $quotedValues = array_map([$connection, 'quote'], $aValues);
                $quotedTargetField = $connection->quoteIdentifier($this->GetTargetTableNameField());
                $quotedTable = $connection->quoteIdentifier($this->sItemTableName);
                $sItemListQuery = "SELECT * FROM {$quotedTable} WHERE {$quotedTargetField} IN (".implode(',', $quotedValues).')';

                $aIdList = [];
                $result = $connection->executeQuery($sItemListQuery);
                while ($row = $result->fetchAssociative()) {
                    $aIdList[] = $connection->quote($row['id']);
                }

                if (count($aIdList) > 0) {
                    $quotedField = $connection->quoteIdentifier($this->sItemFieldName);
                    $sQuery = "`shop_article`.{$quotedField} IN (".implode(',', $aIdList).')';
                }
            }
            $this->SetInternalCache('sQueryRestrictionForActiveFilter', $sQuery);
        }

        return $sQuery;
    }

    /**
     * return setting of element as hidden input fields.
     *
     * @return string
     */
    public function GetActiveSettingAsHiddenInputField()
    {
        $sHTML = '';
        if (is_array($this->aActiveFilterData)) {
            reset($this->aActiveFilterData);
            foreach ($this->aActiveFilterData as $sItemName) {
                $sHTML .= '<input type="hidden" name="'.TGlobal::OutHTML($this->GetURLInputName()).'[]" value="'.TGlobal::OutHTML($sItemName).'" />';
            }
            reset($this->aActiveFilterData);
        }

        return $sHTML;
    }

    /**
     * returns the name field of the target table.
     *
     * @return string
     */
    protected function GetTargetTableNameField()
    {
        static $targetTableFieldNameCache = [];
        if (true === array_key_exists($this->sItemTableName, $targetTableFieldNameCache)) {
            return $targetTableFieldNameCache[$this->sItemTableName];
        }
        $oTargetTableConf = TdbCmsTblConf::GetNewInstance();
        /* @var $oTargetTableConf TdbCmsTblConf */
        $oTargetTableConf->LoadFromField('name', $this->sItemTableName);
        $targetTableFieldName = $oTargetTableConf->GetNameColumn();
        $sClassName = TCMSTableToClass::GetClassName(TCMSTableToClass::PREFIX_CLASS, $oTargetTableConf->fieldName);
        if (call_user_func([$sClassName, 'CMSFieldIsTranslated'], $targetTableFieldName)) {
            $sLanguagePrefix = TGlobal::GetLanguagePrefix();
            if (!empty($sLanguagePrefix)) {
                $targetTableFieldName = $targetTableFieldName.'__'.$sLanguagePrefix;
            }
        }
        $targetTableFieldNameCache[$this->sItemTableName] = $targetTableFieldName;

        return $targetTableFieldName;
    }
}
