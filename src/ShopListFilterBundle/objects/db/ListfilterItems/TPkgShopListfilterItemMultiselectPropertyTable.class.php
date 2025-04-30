<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterItemMultiselectPropertyTable extends TPkgShopListfilterItemMultiselect
{
    /**
     * return option as assoc array (name=>count).
     *
     * @return array
     */
    public function GetOptions()
    {
        $aOptions = $this->GetFromInternalCache('aOptions');
        if (is_null($aOptions)) {
            if (!empty($this->fieldMysqlFieldName)) {
                $this->sItemTableName = $this->fieldMysqlFieldName;
            }

            $aOptions = [];
            $sIdSelect = $this->GetResultSetBaseQuery();
            $databaseConnection = $this->getDatabaseConnection();
            $quotedItemTableName = $databaseConnection->quoteIdentifier($this->sItemTableName);
            $quotedTargetTableNameField = $databaseConnection->quoteIdentifier($this->GetTargetTableNameField());

            if (PKG_SHOP_LISTFILTER_ENABLE_COUNT_PER_FILTER_ITEM) {
                $sItemQuery = "
             SELECT itemtable.$quotedTargetTableNameField AS attribute,
                    COUNT(itemtable.$quotedTargetTableNameField) AS matches
               FROM $quotedItemTableName AS itemtable
         INNER JOIN ({$sIdSelect}) AS Z ON itemtable.`shop_article_id` = Z.`id`
           GROUP BY itemtable.$quotedTargetTableNameField
          ";
            } else {
                $sItemQuery = "
             SELECT DISTINCT itemtable.$quotedTargetTableNameField AS attribute, 1 AS matches
               FROM $quotedItemTableName AS itemtable
         INNER JOIN ({$sIdSelect}) AS Z ON itemtable.`shop_article_id` = Z.`id`
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
        $sBaseQuery = $this->getQueryModifierOrderByService()->getQueryWithoutOrderBy($sBaseQuery);

        return $sBaseQuery;
    }
}
