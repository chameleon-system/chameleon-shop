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
 * base class used to select from a specific variant type.
/**/
class TPkgShopListfilterItemVariant extends TPkgShopListfilterItemMultiselectMLT
{
    /**
     * you need to set this to the table name of the connected table.
     *
     * @var string
     */
    protected $sItemTableName = 'shop_variant_type_value';

    /**
     * you need to set this to the field name in the article table (note: the field is not derived from
     * the table name since this may differ).
     *
     * @var string
     */
    protected $sItemFieldName = 'shop_variant_type_value_mlt';

    /**
     * @var string
     */
    protected $sVariantTypeIdentifier = 'color';

    /**
     * return the variant type matching "color".
     *
     * @param array $aRow - the data of the current article
     *
     * @return TdbShopVariantType|false
     */
    protected function GetVariantType($aRow)
    {
        $sKey = 'oVariantType_'.$aRow['shop_variant_set_id'];

        /** @var TdbShopVariantType|null $oVariantType */
        $oVariantType = $this->GetFromInternalCache($sKey);

        if (null === $oVariantType) {
            $oVariantType = TdbShopVariantType::GetNewInstance();
            if (!empty($aRow['shop_variant_set_id'])) {
                if (!$oVariantType->LoadFromFields(array('identifier' => $this->sVariantTypeIdentifier, 'shop_variant_set_id' => $aRow['shop_variant_set_id']))) {
                    $oVariantType = false;
                }
            } else {
                $oVariantType = false;
            }
            $this->SetInternalCache($sKey, $oVariantType);
        }

        return $oVariantType;
    }

    /**
     * @param array<string, mixed> $aOptions
     *
     * @return void
     */
    protected function OrderOptions(array &$aOptions): void
    {
        // get the variant type based on the first value
        if (count($aOptions) > 0) {
            $aTmpOption = TTools::MysqlRealEscapeArray(array_keys($aOptions));
            $sKey = $aTmpOption[0];
            $query = "SELECT `shop_variant_type`.*
                    FROM `shop_variant_type_value`
              INNER JOIN `shop_variant_type` ON `shop_variant_type_value`.`shop_variant_type_id` = `shop_variant_type`.`id`
                   WHERE `shop_variant_type_value`.`name` = '".$sKey."'
                     AND `shop_variant_type`.`identifier` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->sVariantTypeIdentifier)."'
                 ";
            if ($aType = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
                $query = "SELECT `shop_variant_type_value`.`name`
                      FROM `shop_variant_type_value`
                INNER JOIN `shop_variant_type` ON `shop_variant_type_value`.`shop_variant_type_id` = `shop_variant_type`.`id`
                     WHERE `shop_variant_type`.`identifier` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->sVariantTypeIdentifier)."'
                       AND `shop_variant_type_value`.`name` IN ('".implode("','", $aTmpOption)."')
                  ORDER BY `shop_variant_type_value`.`".MySqlLegacySupport::getInstance()->real_escape_string($aType['shop_variant_type_value_cmsfieldname']).'`
                   ';
                $tRes = MySqlLegacySupport::getInstance()->query($query);
                $aNewOptions = array();
                while ($aRow = MySqlLegacySupport::getInstance()->fetch_assoc($tRes)) {
                    if (array_key_exists($aRow['name'], $aOptions)) {
                        $aNewOptions[$aRow['name']] = $aOptions[$aRow['name']];
                    }
                }
                $aOptions = $aNewOptions;
            }
        }
    }

    /**
     * return the item name for a given ID.
     *
     * @param string $sFieldName
     * @param string $sFieldValue
     * @param array  $aRow
     *
     * @return string[]
     */
    public function GetItemName($sFieldName, $sFieldValue, $aRow)
    {
        /** @var array<string, string[]> $aLookupList */
        static $aLookupList = array();

        if (!array_key_exists($aRow['id'], $aLookupList)) {
            $aLookupList[$aRow['id']] = array();
            $oVariantType = $this->GetVariantType($aRow);
            if ($oVariantType) {
                $oArticle = TdbShopArticle::GetNewInstance();
                $oArticle->LoadFromRow($aRow);

                /** @var string[] $aResult */
                $aResult = array();
                $oVariantValues = $oArticle->GetVariantValuesAvailableForType($oVariantType);
                if ($oVariantValues) {
                    while ($oVariantValue = $oVariantValues->Next()) {
                        if (!empty($oVariantValue->fieldNameGrouped)) {
                            $aResult[] = $oVariantValue->fieldNameGrouped;
                        } else {
                            $aResult[] = $oVariantValue->GetName();
                        }
                    }
                }
                $aLookupList[$aRow['id']] = $aResult;
            }
        }

        return $aLookupList[$aRow['id']];
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
            $aValues = $this->aActiveFilterData;
            if (!is_array($aValues)) {
                if (empty($aValues)) {
                    $aValues = array();
                } else {
                    $aValues = array($aValues);
                }
            }
            if (count($aValues) > 0) {
                $aValues = TTools::MysqlRealEscapeArray($aValues);
                //          $oVariantType = $this->GetVariantType();

                $sItemListQuery = "SELECT DISTINCT `shop_article`.`variant_parent_id`
                      FROM `shop_variant_type_value`
                INNER JOIN `shop_article_shop_variant_type_value_mlt` ON `shop_variant_type_value`.`id` = `shop_article_shop_variant_type_value_mlt`.`target_id`
                INNER JOIN `shop_article` ON `shop_article_shop_variant_type_value_mlt`.`source_id` = `shop_article`.`id`
                INNER JOIN `shop_article` AS PARENTARTICLE ON `shop_article`.`variant_parent_id` = PARENTARTICLE.`id`
                INNER JOIN `shop_variant_type` ON `shop_variant_type_value`.`shop_variant_type_id` = `shop_variant_type`.`id`
                     WHERE (`shop_variant_type_value`.`name` IN ('".implode("','", $aValues)."') OR `shop_variant_type_value`.`name_grouped` IN ('".implode("','", $aValues)."'))
                       AND `shop_variant_type`.`identifier` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->sVariantTypeIdentifier)."'
                       AND PARENTARTICLE.`active` = '1'
                   ";
                $sActiveRestrictions = TdbShopArticleList::GetActiveArticleQueryRestriction(false);
                if (!empty($sActiveRestrictions)) {
                    $sItemListQuery .= ' AND ('.$sActiveRestrictions.')';
                }
                $tRes = MySqlLegacySupport::getInstance()->query($sItemListQuery);
                //echo $sItemListQuery;echo "\n\n";
                $aIdList = array();
                while ($aItemRow = MySqlLegacySupport::getInstance()->fetch_assoc($tRes)) {
                    $aIdList[] = MySqlLegacySupport::getInstance()->real_escape_string($aItemRow['variant_parent_id']);
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
            $quotedTargetTableNameField = $databaseConnection->quoteIdentifier($this->GetTargetTableNameField());

            if (PKG_SHOP_LISTFILTER_ENABLE_COUNT_PER_FILTER_ITEM) {
                $sItemQuery = "SELECT `shop_variant_type_value`.{$quotedTargetTableNameField} AS attribute, COUNT(`shop_variant_type_value`.{$quotedTargetTableNameField}) as matches
                FROM `shop_variant_type_value`
          INNER JOIN `shop_article_shop_variant_type_value_mlt` ON (`shop_variant_type_value`.`id` = `shop_article_shop_variant_type_value_mlt`.`target_id` AND `shop_variant_type_value`.`shop_variant_type_id` IN (".$this->getVariantTypeIds()."))
          INNER JOIN `shop_article` ON `shop_article_shop_variant_type_value_mlt`.`source_id` = `shop_article`.`id`
          INNER JOIN ($sIdSelect) AS PARENTS ON `shop_article`.`variant_parent_id` = PARENTS.`id`
               WHERE ".TdbShopArticleList::GetActiveArticleQueryRestriction(false)."
               GROUP BY `shop_variant_type_value`.{$quotedTargetTableNameField}
                ";
            } else {
                $sItemQuery = "SELECT DISTINCT `shop_variant_type_value`.{$quotedTargetTableNameField} AS attribute, 1 as matches
                FROM `shop_variant_type_value`
          INNER JOIN `shop_article_shop_variant_type_value_mlt` ON (`shop_variant_type_value`.`id` = `shop_article_shop_variant_type_value_mlt`.`target_id` AND `shop_variant_type_value`.`shop_variant_type_id` IN (".$this->getVariantTypeIds()."))
          INNER JOIN `shop_article` ON `shop_article_shop_variant_type_value_mlt`.`source_id` = `shop_article`.`id`
          INNER JOIN ($sIdSelect) AS PARENTS ON `shop_article`.`variant_parent_id` = PARENTS.`id`
               WHERE ".TdbShopArticleList::GetActiveArticleQueryRestriction(false).'
                ';
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
     * @return string
     */
    public function getVariantTypeIds()
    {
        $aId = array();
        $query = "SELECT `id` FROM shop_variant_type WHERE `identifier` = '{$this->sVariantTypeIdentifier}'";
        $tRes = MySqlLegacySupport::getInstance()->query($query);
        while ($aRow = MySqlLegacySupport::getInstance()->fetch_assoc($tRes)) {
            $aId[] = MySqlLegacySupport::getInstance()->real_escape_string($aRow['id']);
        }

        return "'".implode("','", $aId)."'";
    }
}
