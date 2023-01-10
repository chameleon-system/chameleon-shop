<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopSearchQuery extends TAdbShopSearchQuery
{
    /**
     * returns true if the index is still running.
     * will stop the index if no entries are left to index.
     *
     * @return bool
     */
    public function IndexIsRunning()
    {
        $bIsRunning = false;
        if ($this->fieldIndexRunning) {
            $bIsRunning = ($this->NumberOfRecordsLeftToIndex() > 0);
        }

        return $bIsRunning;
    }

    /**
     * prepare the index table.
     *
     * @param bool $bRegenerateCompleteIndex
     *
     * @return void
     */
    public function StartIndex($bRegenerateCompleteIndex = true)
    {
        if (!$this->IndexIsRunning()) {
            $res = MySqlLegacySupport::getInstance()->query($this->fieldQuery.' LIMIT 0,1');
            // we do not care about the type contents of the fields... just how many there are
            if ($tmp = MySqlLegacySupport::getInstance()->fetch_assoc($res)) {
                // got something.... so start indexeer
                $aData = $this->sqlData;
                $aData['index_running'] = '1';
                $aData['index_started'] = date('Y-m-d H:i:s');
                $this->LoadFromRow($aData);
                $this->AllowEditByAll(true);
                $this->Save();

                $sFields = '';
                $aFields = array_keys($tmp);
                // the last entry in the list will be renamed to shop_article_id
                $aFields[count($aFields) - 1] = 'xxx_shop_article_id';
                foreach ($aFields as $iFieldIndex => $sFieldName) {
                    $aFields[$iFieldIndex] = '`'.MySqlLegacySupport::getInstance()->real_escape_string($sFieldName).'`';
                    if ('id' == $sFieldName || '_id' == substr($sFieldName, -3)) {
                        $sFields .= "{$aFields[$iFieldIndex]} CHAR(36) NOT NULL ,";
                    } elseif ('cmsident' == $sFieldName) {
                        $sFields .= "{$aFields[$iFieldIndex]} BIGINT UNSIGNED NOT NULL ,";
                    } else {
                        $sFields .= "{$aFields[$iFieldIndex]} TEXT NOT NULL ,";
                    }
                }
                $sTableName = $this->GetIndexRawTableName();
                $query = 'CREATE TABLE `'.MySqlLegacySupport::getInstance()->real_escape_string($sTableName)."` (
                      {$sFields}
                      `sysid` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
                    ) ENGINE = InnoDB";
                MySqlLegacySupport::getInstance()->query($query);
                // now copy data...
                $query = 'INSERT INTO `'.MySqlLegacySupport::getInstance()->real_escape_string($sTableName).'` ('.implode(',', $aFields).') '.$this->fieldQuery;
                MySqlLegacySupport::getInstance()->query($query);
                $query = 'ALTER TABLE `'.MySqlLegacySupport::getInstance()->real_escape_string($sTableName).'` ADD INDEX (  `xxx_shop_article_id` )';
                MySqlLegacySupport::getInstance()->query($query);

                // now delete all records that do not require changes
                if (!$bRegenerateCompleteIndex) {
                    $query = 'DELETE `'.MySqlLegacySupport::getInstance()->real_escape_string($sTableName).'`
                             FROM `'.MySqlLegacySupport::getInstance()->real_escape_string($sTableName).'`
                        LEFT JOIN shop_search_reindex_queue ON `'.MySqlLegacySupport::getInstance()->real_escape_string($sTableName)."`.`xxx_shop_article_id`= shop_search_reindex_queue.object_id
                            WHERE `shop_search_reindex_queue`.`object_id` IS NULL OR (`shop_search_reindex_queue`.`processing`='1' AND `shop_search_reindex_queue`.`action` = 'delete')
                            ";
                    MySqlLegacySupport::getInstance()->query($query);
                }
                // now unset all varianten fields that can not be changed + change the id of the varaint to the variant parent
                $oVariantSets = TdbShopVariantSetList::GetList();
                $oVariantSets->GoToStart();
                while ($oVariantSet = $oVariantSets->Next()) {
                    $aDeleteFields = $aFields;
                    foreach ($aDeleteFields as $iFieldIndex => $sField) {
                        if ($oVariantSet->AllowEditOfField($sField)) {
                            unset($aDeleteFields[$iFieldIndex]);
                        }
                    }
                    $query = 'UPDATE `'.MySqlLegacySupport::getInstance()->real_escape_string($sTableName).'` AS TARGET
                  INNER JOIN shop_article AS PA ON TARGET.xxx_shop_article_id = PA.id
                  INNER JOIN shop_article AS P ON PA.variant_parent_id = P.id
            SET TARGET.xxx_shop_article_id = P.id
                       ';
                    $aFixedList = array('`xxx_shop_article_id`', '`id`');
                    if (count($aDeleteFields) > 0) {
                        reset($aDeleteFields);
                        foreach ($aDeleteFields as $sDeleteField) {
                            if (!in_array($sDeleteField, $aFixedList)) {
                                $query .= ", TARGET.{$sDeleteField} = ''";
                            }
                        }
                    }
                    $query .= " WHERE P.shop_variant_set_id = '".$oVariantSet->id."'";
                    MySqlLegacySupport::getInstance()->query($query);
                }
            }
        }
    }

    /**
     * creates index for the number of rows requested. returns the real number of rows processed.
     *
     * @param int $iNumberOfRowsToProcess - if set to -1 we will process all rows
     *
     * @return int
     */
    public function CreateIndexTick($iNumberOfRowsToProcess)
    {
        $iRealNumberProcessed = 0;
        $oFields = TdbShopSearchFieldWeightList::GetListForShopSearchQueryId($this->id);
        if (CMS_SHOP_INDEX_LOAD_DELAY_MILLISECONDS > 0) {
            usleep(CMS_SHOP_INDEX_LOAD_DELAY_MILLISECONDS * 1000);
        }

        // fetch as many rows as we can....
        $sLimit = " LIMIT 0,{$iNumberOfRowsToProcess}";
        if ($iNumberOfRowsToProcess < 0) {
            $sLimit = '';
        }
        $query = 'SELECT * FROM `'.MySqlLegacySupport::getInstance()->real_escape_string($this->GetIndexRawTableName())."` {$sLimit}";
        $tres = MySqlLegacySupport::getInstance()->query($query);
        $sError = MySqlLegacySupport::getInstance()->error();
        if (!empty($sError)) {
            trigger_error('SQL Error: '.$sError, E_USER_WARNING);
        } else {
            while ($aRow = MySqlLegacySupport::getInstance()->fetch_assoc($tres)) {
                $oFields->GoToStart();
                while ($oField = $oFields->Next()) {
                    $oField->CreateIndexTick($aRow);
                }
                $query = 'DELETE FROM `'.MySqlLegacySupport::getInstance()->real_escape_string($this->GetIndexRawTableName())."` WHERE `sysid` = '".MySqlLegacySupport::getInstance()->real_escape_string($aRow['sysid'])."'";
                MySqlLegacySupport::getInstance()->query($query);
                ++$iRealNumberProcessed;
            }
        }

        if (($iRealNumberProcessed < $iNumberOfRowsToProcess) || ($iNumberOfRowsToProcess < 0)) {
            $query = 'DROP TABLE `'.MySqlLegacySupport::getInstance()->real_escape_string($this->GetIndexRawTableName()).'`';
            MySqlLegacySupport::getInstance()->query($query);
        }

        return $iRealNumberProcessed;
    }

    /**
     * returns the number of records that still need to be indext for the query
     * will stop the index if no entries are left to index.
     *
     * @return int
     */
    public function NumberOfRecordsLeftToIndex()
    {
        $iCount = 0;
        if ($this->fieldIndexRunning) {
            $sIndexTableName = $this->GetIndexRawTableName();
            $query = "SHOW TABLES LIKE '".MySqlLegacySupport::getInstance()->real_escape_string($sIndexTableName)."'";
            $tRes = MySqlLegacySupport::getInstance()->query($query);
            $bTableExists = (MySqlLegacySupport::getInstance()->num_rows($tRes) >= 1);
            if ($bTableExists) {
                $query = 'SELECT COUNT(sysid) AS reccount FROM `'.MySqlLegacySupport::getInstance()->real_escape_string($sIndexTableName).'`';
                if ($trow = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
                    $iCount = $trow['reccount'];
                }
            }
            if ($iCount < 1) {
                $this->StopIndex();
            }
        }

        return $iCount;
    }

    /**
     * stop the current index operation.
     *
     * @return void
     */
    public function StopIndex()
    {
        $sIndexTableName = $this->GetIndexRawTableName();
        if (TCMSRecord::TableExists($sIndexTableName)) {
            $query = 'DROP TABLE `'.MySqlLegacySupport::getInstance()->real_escape_string($sIndexTableName).'`';
            MySqlLegacySupport::getInstance()->query($query);
        }

        $aData = $this->sqlData;
        $aData['index_running'] = '0';
        $aData['index_completed'] = date('Y-m-d H:i:s');
        $this->LoadFromRow($aData);
        $this->AllowEditByAll(true);
        $this->Save();
    }

    /**
     * return the index table name.
     *
     * @return string
     */
    protected function GetIndexRawTableName()
    {
        return '_cmsrawindex_'.md5($this->fieldName);
    }
}
