<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterItemManufacturer extends TPkgShopListfilterItemMultiselect
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
     * return the query restriction for active filter. returns false if there
     * is no active restriction for this item.
     *
     * @return string
     */
    public function GetQueryRestrictionForActiveFilter()
    {
        $sQuery = parent::GetQueryRestrictionForActiveFilter();
        if (is_null($sQuery)) {
            $aValues = $this->aActiveFilterData;
            if (is_array($aValues) && count($aValues) > 0) {
                $quotedItemFieldName = $this->getDatabaseConnection()->quoteIdentifier($this->sItemFieldName);
                $sQuery = " `shop_article`.$quotedItemFieldName = '' ";
            }
        }

        return $sQuery;
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
            //select shop_manufacturer_id, that has available(searchable, invirtual, active, etc) articles of filtered categories
            $sIdSelect = $this->GetResultSetBaseQuery();

            //select id of available(searchable, invirtual, active, etc) articles of filtered categories
            $sArticleIdsQuery = $this->GetResultSetBaseQuery('id');
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
         INNER JOIN ($sArticleIdsQuery) AS Y ON `shop_article`.`id` = Y.`id`
           GROUP BY itemtable.$quotedTargetTableNameField
           ORDER BY itemtable.$quotedTargetTableNameField
          ";
            } else {
                $sItemQuery = "
             SELECT DISTINCT itemtable.$quotedTargetTableNameField AS attribute, 1 AS matches
               FROM $quotedItemTableName AS itemtable
         INNER JOIN `shop_article` ON `shop_article`.$quotedItemFieldName = itemtable.`id`
         INNER JOIN ($sIdSelect) AS Z ON itemtable.`id` = Z.$quotedItemFieldName
              WHERE itemtable.`active` = '1'
           ORDER BY itemtable.$quotedTargetTableNameField
          ";
            }

            $tRes = MySqlLegacySupport::getInstance()->query($sItemQuery);
            while ($aOption = MySqlLegacySupport::getInstance()->fetch_assoc($tRes)) {
                if (!empty($aOption['attribute'])) {
                    $aOptions[$aOption['attribute']] = $aOption['matches'];
                }
            }
            $this->SetInternalCache('aOptions', $aOptions);
        }

        return $aOptions;
    }
}
