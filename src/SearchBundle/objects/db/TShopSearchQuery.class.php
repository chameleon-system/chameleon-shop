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
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        if (!$this->IndexIsRunning()) {
            $query = $this->fieldQuery . ' LIMIT 0,1';
            $statement = $connection->executeQuery($query);

            if ($tmp = $statement->fetchAssociative()) {
                $aData = $this->sqlData;
                $aData['index_running'] = '1';
                $aData['index_started'] = date('Y-m-d H:i:s');
                $this->LoadFromRow($aData);
                $this->AllowEditByAll(true);
                $this->Save();

                $sFields = '';
                $aFields = array_keys($tmp);
                $aFields[count($aFields) - 1] = 'xxx_shop_article_id';

                foreach ($aFields as $iFieldIndex => $sFieldName) {
                    $quotedField = $connection->quoteIdentifier($sFieldName);
                    $aFields[$iFieldIndex] = $quotedField;

                    if ('id' === $sFieldName || '_id' === substr($sFieldName, -3)) {
                        $sFields .= "{$quotedField} CHAR(36) NOT NULL, ";
                    } elseif ('cmsident' === $sFieldName) {
                        $sFields .= "{$quotedField} BIGINT UNSIGNED NOT NULL, ";
                    } else {
                        $sFields .= "{$quotedField} TEXT NOT NULL, ";
                    }
                }

                $sTableName = $this->GetIndexRawTableName();
                $quotedTableName = $connection->quoteIdentifier($sTableName);

                $query = "
                CREATE TABLE {$quotedTableName} (
                    {$sFields}
                    `sysid` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
                ) ENGINE=InnoDB
            ";
                $connection->executeStatement($query);

                // Copy data
                $query = "
                INSERT INTO {$quotedTableName} (" . implode(',', $aFields) . ") 
                " . $this->fieldQuery;
                $connection->executeStatement($query);

                // Add index
                $query = "
                ALTER TABLE {$quotedTableName} 
                ADD INDEX (`xxx_shop_article_id`)
            ";
                $connection->executeStatement($query);

                // Delete unnecessary records if not regenerating complete index
                if (!$bRegenerateCompleteIndex) {
                    $query = "
                    DELETE {$quotedTableName}
                      FROM {$quotedTableName}
                 LEFT JOIN `shop_search_reindex_queue` ON {$quotedTableName}.`xxx_shop_article_id` = `shop_search_reindex_queue`.`object_id`
                     WHERE `shop_search_reindex_queue`.`object_id` IS NULL
                        OR (`shop_search_reindex_queue`.`processing` = '1' AND `shop_search_reindex_queue`.`action` = 'delete')
                ";
                    $connection->executeStatement($query);
                }

                // Handle variant fields
                $oVariantSets = TdbShopVariantSetList::GetList();
                $oVariantSets->GoToStart();
                while ($oVariantSet = $oVariantSets->Next()) {
                    $aDeleteFields = $aFields;

                    foreach ($aDeleteFields as $iFieldIndex => $sField) {
                        // remove fields allowed to be edited
                        if ($oVariantSet->AllowEditOfField(str_replace('`', '', $sField))) {
                            unset($aDeleteFields[$iFieldIndex]);
                        }
                    }

                    $query = "
                    UPDATE {$quotedTableName} AS TARGET
                    INNER JOIN `shop_article` AS PA ON TARGET.`xxx_shop_article_id` = PA.`id`
                    INNER JOIN `shop_article` AS P ON PA.`variant_parent_id` = P.`id`
                    SET TARGET.`xxx_shop_article_id` = P.`id`
                ";

                    $aFixedList = ['`xxx_shop_article_id`', '`id`'];
                    if (count($aDeleteFields) > 0) {
                        foreach ($aDeleteFields as $sDeleteField) {
                            if (!in_array($sDeleteField, $aFixedList, true)) {
                                $query .= ", TARGET.{$sDeleteField} = ''";
                            }
                        }
                    }

                    $quotedVariantSetId = $connection->quote($oVariantSet->id);
                    $query .= "
                    WHERE P.`shop_variant_set_id` = {$quotedVariantSetId}
                ";

                    $connection->executeStatement($query);
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
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $iRealNumberProcessed = 0;

        $oFields = TdbShopSearchFieldWeightList::GetListForShopSearchQueryId($this->id);

        if (CMS_SHOP_INDEX_LOAD_DELAY_MILLISECONDS > 0) {
            usleep(CMS_SHOP_INDEX_LOAD_DELAY_MILLISECONDS * 1000);
        }

        $sLimit = '';
        if ($iNumberOfRowsToProcess >= 0) {
            $sLimit = " LIMIT 0, {$iNumberOfRowsToProcess}";
        }

        $quotedTableName = $connection->quoteIdentifier($this->GetIndexRawTableName());
        $query = "SELECT * FROM {$quotedTableName}{$sLimit}";

        try {
            $statement = $connection->executeQuery($query);

            while ($aRow = $statement->fetchAssociative()) {
                $oFields->GoToStart();
                while ($oField = $oFields->Next()) {
                    $oField->CreateIndexTick($aRow);
                }

                $quotedSysId = $connection->quote($aRow['sysid']);

                $deleteQuery = "
                DELETE FROM {$quotedTableName}
                 WHERE `sysid` = {$quotedSysId}
            ";
                $connection->executeStatement($deleteQuery);

                ++$iRealNumberProcessed;
            }
        } catch (\Exception $e) {
            trigger_error('SQL Error: ' . $e->getMessage(), E_USER_WARNING);
        }

        if (($iRealNumberProcessed < $iNumberOfRowsToProcess) || ($iNumberOfRowsToProcess < 0)) {
            $dropQuery = "DROP TABLE {$quotedTableName}";
            $connection->executeStatement($dropQuery);
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
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $iCount = 0;

        if ($this->fieldIndexRunning) {
            $sIndexTableName = $this->GetIndexRawTableName();
            $quotedTableNameLike = $connection->quote($sIndexTableName);

            $query = "SHOW TABLES LIKE {$quotedTableNameLike}";
            $statement = $connection->executeQuery($query);
            $bTableExists = ($statement->rowCount() >= 1);

            if ($bTableExists) {
                $quotedTableName = $connection->quoteIdentifier($sIndexTableName);
                $query = "
                SELECT COUNT(`sysid`) AS reccount
                  FROM {$quotedTableName}
            ";
                $statement = $connection->executeQuery($query);
                $row = $statement->fetchAssociative();

                if ($row) {
                    $iCount = (int) $row['reccount'];
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
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $sIndexTableName = $this->GetIndexRawTableName();

        if (TCMSRecord::TableExists($sIndexTableName)) {
            $quotedTableName = $connection->quoteIdentifier($sIndexTableName);

            $query = "DROP TABLE {$quotedTableName}";
            $connection->executeStatement($query);
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
