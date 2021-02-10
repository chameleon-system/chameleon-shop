<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterItemMultiselectMLT extends TPkgShopListfilterItemMultiselect
{
    /**
     * return the query restriction for active filter. returns false if there
     * is no active restriction for this item.
     *
     * @return string
     */
    public function GetQueryRestrictionForActiveFilter()
    {
        $sQuery = $this->GetFromInternalCache('sQueryRestrictionForActiveFilter');
        if (is_null($sQuery)) {
            if (true === is_array($this->aActiveFilterData) && count($this->aActiveFilterData) > 0) {
                $sItemListQuery = $this->GetSQLQueryForQueryRestrictionForActiveFilter();
                $aIdList = array();
                if (!empty($sItemListQuery)) {
                    $tRes = MySqlLegacySupport::getInstance()->query($sItemListQuery);
                    $aIdList = array();
                    while ($aItemRow = MySqlLegacySupport::getInstance()->fetch_assoc($tRes)) {
                        $aIdList[] = MySqlLegacySupport::getInstance()->real_escape_string($aItemRow['source_id']);
                    }
                }

                if (count($aIdList) > 0) {
                    $sQuery = "`shop_article`.`id` IN ('".implode("','", $aIdList)."')";
                }
            }
            $this->SetInternalCache('sQueryRestrictionForActiveFilter', $sQuery);
        }

        return $sQuery;
    }

    /**
     * builds the sql query for the GetQueryRestrictionForActiveFilter method
     * we only want to show results that are values of the selected shop attribute in the filter item.
     *
     * @return string
     */
    protected function GetSQLQueryForQueryRestrictionForActiveFilter()
    {
        $aValues = TTools::MysqlRealEscapeArray($this->aActiveFilterData);
        $sEscapedTargetTable = MySqlLegacySupport::getInstance()->real_escape_string($this->sItemTableName);
        $sEscapedTargetMLTTable = MySqlLegacySupport::getInstance()->real_escape_string('shop_article_'.$this->sItemTableName.'_mlt');

        $sItemListQuery = "SELECT `{$sEscapedTargetMLTTable}`.*
                           FROM `{$sEscapedTargetTable}`
                     INNER JOIN `{$sEscapedTargetMLTTable}` ON `{$sEscapedTargetTable}`.`id` = `{$sEscapedTargetMLTTable}`.`target_id`
                          WHERE ".$this->GetTargetTableNameField()." IN ('".implode("','", $aValues)."')";

        return $sItemListQuery;
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
            $databaseConnection = $this->getDatabaseConnection();
            $quotedItemTableName = $databaseConnection->quoteIdentifier($this->sItemTableName);
            $quotedTableName = $databaseConnection->quoteIdentifier("shop_article_{$this->sItemFieldName}");
            $quotedTargetTableNameField = $databaseConnection->quoteIdentifier($this->GetTargetTableNameField());
            if (PKG_SHOP_LISTFILTER_ENABLE_COUNT_PER_FILTER_ITEM) {
                $sItemQuery = "
             SELECT itemtable.$quotedTargetTableNameField AS attribute, COUNT(itemtable.$quotedTargetTableNameField) AS matches
               FROM $quotedItemTableName AS itemtable
         INNER JOIN $quotedTableName AS T ON itemtable.`id` = T.`target_id`
         INNER JOIN ($sIdSelect) AS Z ON T.`source_id` = Z.`id`
           GROUP BY itemtable.$quotedTargetTableNameField
          ";
            } else {
                $sItemQuery = "
             SELECT DISTINCT itemtable.$quotedTargetTableNameField AS attribute, 1 AS matches
               FROM $quotedItemTableName AS itemtable
         INNER JOIN $quotedTableName AS T ON itemtable.`id` = T.`target_id`
         INNER JOIN ($sIdSelect) AS Z ON T.`source_id` = Z.`id`
          ";
            }

            $tRes = MySqlLegacySupport::getInstance()->query($sItemQuery);
            while ($aOption = MySqlLegacySupport::getInstance()->fetch_assoc($tRes)) {
                $aOptions[$aOption['attribute']] = $aOption['matches'];
            }
            $this->OrderOptions($aOptions);
            $this->SetInternalCache('aOptions', $aOptions);
        }

        return $aOptions;
    }

    /**
     * {@inheritdoc}
     */
    protected function GetResultSetBaseQuery($sFieldName = '')
    {
        $sQuery = $this->oItemListFilteredByOtherItems->GetActiveQuery();
        $sTmpQuery = mb_strtoupper($sQuery);
        $sTmpQuery = str_replace("\n", ' ', $sTmpQuery);
        $iFromPos = strpos($sTmpQuery, ' FROM ');
        $sBaseQuery = 'SELECT DISTINCT `shop_article`.`id` '.substr($sQuery, $iFromPos);
        $queryModifierOrderByService = $this->getQueryModifierOrderByService();
        $sBaseQuery = $queryModifierOrderByService->getQueryWithoutOrderBy($sBaseQuery);

        return $sBaseQuery;
    }
}
