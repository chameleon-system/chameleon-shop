<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * possible filter config defines.
 *
 * PKG_SHOP_LISTFILTER_ENABLE_COUNT_PER_FILTER_ITEM -> set to true if you want a count per filter option
 *
 * PKG_SHOP_LISTFILTER_ATTRIBUTE_FILTER_SELECT_VARIANTS -> set to true if you want the filter to include variants
 *
 * PKG_SHOP_LISTFILTER_ATTRIBUTE_FILTER_SELECT_VARIANTS_PARENTS -> set to true if you want to include variants and
 * show only parent articles in list. If false you have to show variants in article list.
 *
 * /**/
class TPkgShopListfilterItemShopAttributeList extends TPkgShopListfilterItemMultiselectMLT
{
    /**
     * you need to set this to the table name of the connected table.
     *
     * @var string
     */
    protected $sItemTableName = 'shop_attribute_value';

    /**
     * you need to set this to the field name in the article table (note: the field is not derived from
     * the table name since this may differ).
     *
     * @var string
     */
    protected $sItemFieldName = 'shop_attribute_value_mlt';

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
            $oShopAttribute = $this->GetFieldShopAttribute();
            if (is_object($oShopAttribute)) {
                $sIdSelect = $this->GetResultSetBaseQuery();
                // now add the filter from the filter module...
                $query = 'CREATE TEMPORARY TABLE  `_tmp_category_article` (
                                                   `id` CHAR( 36 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL
                                                    ) ENGINE = MEMORY';
                MySqlLegacySupport::getInstance()->query($query);
                $query = "INSERT INTO _tmp_category_article (`id`) {$sIdSelect}";
                MySqlLegacySupport::getInstance()->query($query);
                $query = 'ALTER TABLE  `_tmp_category_article` ADD PRIMARY KEY (  `id` )';
                MySqlLegacySupport::getInstance()->query($query);
                $databaseConnection = $this->getDatabaseConnection();
                $quotedItemTableName = $databaseConnection->quoteIdentifier($this->sItemTableName);
                $quotedTableName = $databaseConnection->quoteIdentifier("shop_article_{$this->sItemFieldName}");
                $quotedTargetTableNameField = $databaseConnection->quoteIdentifier($this->GetTargetTableNameField());
                $quotedShopAttributeId = $databaseConnection->quote($oShopAttribute->id);

                if (PKG_SHOP_LISTFILTER_ENABLE_COUNT_PER_FILTER_ITEM) {
                    $sMatchCountString = "itemtable.$quotedTargetTableNameField AS attribute, COUNT(itemtable.$quotedTargetTableNameField) AS matches";
                } else {
                    $sMatchCountString = "DISTINCT itemtable.$quotedTargetTableNameField AS attribute, 1 AS matches";
                }
                if (defined(
                        'PKG_SHOP_LISTFILTER_ATTRIBUTE_FILTER_SELECT_VARIANTS'
                    ) && PKG_SHOP_LISTFILTER_ATTRIBUTE_FILTER_SELECT_VARIANTS
                ) {
                    $sItemQuery = "
                         SELECT {$sMatchCountString}
                           FROM $quotedItemTableName AS itemtable
                     INNER JOIN $quotedTableName AS T ON itemtable.`id` = T.`target_id`
                     INNER JOIN `shop_article` ON T.`source_id` = `shop_article`.`id`
                     INNER JOIN `_tmp_category_article` AS Z ON `shop_article`.`variant_parent_id` = Z.`id`
                          WHERE itemtable.`shop_attribute_id` = $quotedShopAttributeId
                      ";
                } else {
                    $sItemQuery = "
                         SELECT {$sMatchCountString}
                           FROM $quotedItemTableName AS itemtable
                     INNER JOIN $quotedTableName AS T ON itemtable.`id` = T.`target_id`
                     INNER JOIN `_tmp_category_article` AS Z ON T.`source_id` = Z.`id`
                          WHERE itemtable.`shop_attribute_id` = $quotedShopAttributeId
                      ";
                }
                if (PKG_SHOP_LISTFILTER_ENABLE_COUNT_PER_FILTER_ITEM) {
                    if (defined(
                            'PKG_SHOP_LISTFILTER_ATTRIBUTE_FILTER_SELECT_VARIANTS_PARENTS'
                        ) && PKG_SHOP_LISTFILTER_ATTRIBUTE_FILTER_SELECT_VARIANTS_PARENTS
                    ) {
                        $sItemQuery .= "GROUP BY `shop_article`.`variant_parent_id` , itemtable.$quotedTargetTableNameField";
                    } else {
                        $sItemQuery .= "GROUP BY itemtable.$quotedTargetTableNameField";
                    }
                }
                $sItemQuery .= 'ORDER BY itemtable.`name` ASC';
                $tRes = MySqlLegacySupport::getInstance()->query($sItemQuery);
                while ($aOption = MySqlLegacySupport::getInstance()->fetch_assoc($tRes)) {
                    if (defined(
                            'PKG_SHOP_LISTFILTER_ATTRIBUTE_FILTER_SELECT_VARIANTS_PARENTS'
                        ) && PKG_SHOP_LISTFILTER_ATTRIBUTE_FILTER_SELECT_VARIANTS_PARENTS
                    ) {
                        if (isset($aOptions[$aOption['attribute']])) {
                            ++$aOptions[$aOption['attribute']];
                        } else {
                            $aOptions[$aOption['attribute']] = 1;
                        }
                    } else {
                        $aOptions[$aOption['attribute']] = $aOption['matches'];
                    }
                }
                $this->OrderOptions($aOptions);
                $this->SetInternalCache('aOptions', $aOptions);

                MySqlLegacySupport::getInstance()->query('DROP TEMPORARY TABLE `_tmp_category_article`');
            }
        }

        return $aOptions;
    }

    /**
     * return the query restriction for active filter. returns false if there
     * is no active restriction for this item.
     *
     * @return string
     */
    public function GetQueryRestrictionForActiveFilter()
    {
        $sQuery = $this->GetFromInternalCache('sQueryRestrictionForActiveFilter');
        if (null === $sQuery) {
            $databaseConnection = $this->getDatabaseConnection();
            $aValues = $this->aActiveFilterData;
            if (is_array($aValues) && count($aValues) > 0) {
                $sItemListQuery = $this->GetSQLQueryForQueryRestrictionForActiveFilter();
                $aIdList = array();
                if (!empty($sItemListQuery)) {
                    $tRes = MySqlLegacySupport::getInstance()->query($sItemListQuery);
                    $aIdList = array();
                    while ($aItemRow = MySqlLegacySupport::getInstance()->fetch_assoc($tRes)) {
                        $aIdList[] = $databaseConnection->quote($aItemRow['source_id']);
                    }
                }

                if (count($aIdList) > 0) {
                    if (defined(
                            'PKG_SHOP_LISTFILTER_ATTRIBUTE_FILTER_SELECT_VARIANTS'
                        ) && PKG_SHOP_LISTFILTER_ATTRIBUTE_FILTER_SELECT_VARIANTS
                    ) {
                        //now we have a list of variant article ids but we need parent ids so we fetch the parent id for each variant id without duplicates
                        $sParentQuery = 'SELECT `shop_article`.`variant_parent_id`
                                 FROM `shop_article`
                                WHERE `shop_article`.`id` IN ('.implode(',', $aIdList).")
                                AND `shop_article`.`variant_parent_id` != '' ";
                        $rResult = MySqlLegacySupport::getInstance()->query($sParentQuery);
                        $aIdList = array();

                        while ($aRow = MySqlLegacySupport::getInstance()->fetch_assoc($rResult)) {
                            if (!in_array($aRow['variant_parent_id'], $aIdList)) {
                                $aIdList[] = $databaseConnection->quote($aRow['variant_parent_id']);
                            }
                        }
                    }

                    $sQuery = '`shop_article`.`id` IN ('.implode(',', $aIdList).')';
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
        $sItemListQuery = parent::GetSQLQueryForQueryRestrictionForActiveFilter();
        $quotedItemTableName = $this->getDatabaseConnection()->quoteIdentifier($this->sItemTableName);
        $sItemListQuery .= " AND $quotedItemTableName.`shop_attribute_id` = '".MySqlLegacySupport::getInstance()->real_escape_string(
                $this->fieldShopAttribute
            )."'";

        return $sItemListQuery;
    }
}
