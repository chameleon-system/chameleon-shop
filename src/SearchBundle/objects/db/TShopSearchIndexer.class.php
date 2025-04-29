<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;
use Doctrine\DBAL\Connection;
use esono\pkgCmsCache\CacheInterface;
use Psr\Log\LoggerInterface;

if (!defined('PKG_SEARCH_USE_SEARCH_QUEUE')) {
    define('PKG_SEARCH_USE_SEARCH_QUEUE', true);
}

class TShopSearchIndexer extends TShopSearchIndexerAutoParent
{
    public const INDEX_SET_SIZE = 500; // indicates how many records are processed by each index step
    public const INDEX_TBL_PREFIX = '_index_';

    /** @var string[] */
    protected $aTablesToProcess = [];

    /**
     * set to false, if you want to update the index only for those objects that changed.
     *
     * @var bool
     */
    protected $bRegenerateCompleteIndex = true;

    /**
     * set index mode (full or partial).
     *
     * @param bool $bRegenerateCompleteIndex - set to true if you want a full reindexing or false if you do not
     *
     * @return void
     */
    public function SetRegenerateCompleteIndex($bRegenerateCompleteIndex)
    {
        $this->bRegenerateCompleteIndex = $bRegenerateCompleteIndex;
        if (!$this->bRegenerateCompleteIndex) {
            if (false == TGlobal::TableExists('shop_search_reindex_queue') || PKG_SEARCH_USE_SEARCH_QUEUE == false) {
                $this->bRegenerateCompleteIndex = true;
            }
        }
    }

    /**
     * @return void
     */
    protected function PostLoadHook()
    {
        parent::PostLoadHook();
        $sData = $this->fieldProcessdata;
        if (!empty($sData)) {
            $aTmp = unserialize($sData);
            if (is_array($aTmp)) {
                $this->aTablesToProcess = $aTmp;
            }
        }
    }

    /**
     * commit processing data to database.
     *
     * @return void
     */
    protected function CommitProcessData()
    {
        TdbShopSearchFieldWeight::AddQueryBlock(false, null, null);
        $sTmp = serialize($this->aTablesToProcess);
        $aData = $this->sqlData;
        $aData['processdata'] = $sTmp;
        $this->LoadFromRow($aData);
        $this->AllowEditByAll(true);
        $this->Save();
    }

    /**
     * returns the index status (false=not running, number = percent done).
     *
     * @return float|false
     */
    public function GetIndexStatus()
    {
        $dStatus = false;

        if ($this->IsRunning()) {
            $iRemainingRows = $this->GetRemainingRowCount();
            if (0 == $iRemainingRows) {
                $dStatus = false;
                $this->IndexCompletedHook();
            } else {
                /** @var float $dStatus */
                $dStatus = (($this->fieldTotalRowsToProcess - $iRemainingRows) / $this->fieldTotalRowsToProcess) * 100;
            }
        }

        return $dStatus;
    }

    /**
     * return the number of rows still to process.
     *
     * @return int
     */
    protected function GetRemainingRowCount()
    {
        $aTableList = $this->aTablesToProcess;
        $iCount = 0;
        foreach ($aTableList as $iWorkId) {
            $oIndexQuery = TdbShopSearchQuery::GetNewInstance();
            /** @var $oIndexQuery TdbShopSearchQuery */
            if ($oIndexQuery->Load($iWorkId)) {
                $iCount = $iCount + $oIndexQuery->NumberOfRecordsLeftToIndex();
            }
        }

        return $iCount;
    }

    /**
     * return true if the indexer is running.
     *
     * @return bool
     */
    public function IsRunning()
    {
        return '0000-00-00 00:00:00' == $this->fieldCompleted;
    }

    /**
     * @return bool
     */
    public function IndexerHasFinished()
    {
        $bIsDone = (!is_array($this->aTablesToProcess) || count($this->aTablesToProcess) < 1);
        if ($bIsDone) {
            $this->IndexCompletedHook();
        }

        return $bIsDone;
    }

    /**
     * performs the next index set.
     *
     * @param bool $bIndexUsingTicker - if set to true, each call to this method will only index as many rows as defined by INDEX_SET_SIZE
     *
     * @return void
     */
    public function ProcessNextIndexStep($bIndexUsingTicker = true)
    {
        $this->getCache()->disable();
        // if no index is running, prepare the indexer
        $iTickerSize = self::INDEX_SET_SIZE;
        if (!$bIndexUsingTicker) {
            $iTickerSize = -1;
        }
        if (!$this->IsRunning()) {
            $this->InitializeIndexer();
        }

        if ($this->IndexerHasFinished()) {
            // indexing is done...
            $this->IndexCompletedHook();
        } elseif ($this->IsRunning()) {
            // now process the next tick...
            $iRecordsProcessed = 0;
            do {
                $iWorkId = $this->aTablesToProcess[0];
                $oIndexQuery = TdbShopSearchQuery::GetNewInstance();
                /** @var $oIndexQuery TdbShopSearchQuery */
                if ($oIndexQuery->Load($iWorkId)) {
                    $iRecordsProcessedByIndexer = $oIndexQuery->CreateIndexTick($iTickerSize);
                    $iRecordsProcessed += $iRecordsProcessedByIndexer;
                    if (($iRecordsProcessedByIndexer < $iTickerSize) || ($iTickerSize < 0)) {
                        // if we processed less than we requested, we must be done with this query
                        // so we drop it from the list of queries we need to process
                        array_shift($this->aTablesToProcess);
                    }
                } else {
                    // unalbe to load query object... so drop it from the list
                    array_shift($this->aTablesToProcess);
                }
            } while (!$this->IndexerHasFinished() && (($iRecordsProcessed < self::INDEX_SET_SIZE) || ($iTickerSize < 0)));
        }
        $this->CommitProcessData();
        $this->getCache()->enable();
    }

    /**
     * @return void
     */
    protected function IndexCompletedHook()
    {
        $this->aTablesToProcess = [];
        $this->CommitProcessData();

        $aData = $this->sqlData;
        $aData['completed'] = date('Y-m-d H:i:s');
        $this->LoadFromRow($aData);
        $this->AllowEditByAll(true);
        $this->Save();
        if (CMS_SEARCH_INDEX_USE_LOAD_FILE) {
            TdbShopSearchFieldWeight::GetFilePointer('', 'close');
        } else {
            TdbShopSearchFieldWeight::AddQueryBlock(false, null, null);
        }
        if (!$this->bRegenerateCompleteIndex) {
            $query = "DELETE FROM shop_search_reindex_queue WHERE `processing` = '1'";
            MySqlLegacySupport::getInstance()->query($query);
        }
        $this->CopyIndexTables();
    }

    /**
     * initialize indexer.
     *
     * @return void
     */
    public function InitializeIndexer()
    {
        $bWorkToDo = true;
        if (!$this->bRegenerateCompleteIndex) {
            // drop index
            $query = "UPDATE shop_search_reindex_queue SET `processing` = '1'";
            MySqlLegacySupport::getInstance()->query($query);
            $query = "SELECT COUNT(*) AS reccount FROM shop_search_reindex_queue WHERE `processing` = '1'";
            $aCount = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query));
            if ($aCount['reccount'] > 0) {
                $query = "SHOW TABLES LIKE '_index_%'";
                $tRes = MySqlLegacySupport::getInstance()->query($query);
                while ($aIndexTable = MySqlLegacySupport::getInstance()->fetch_row($tRes)) {
                    $query = "DELETE FROM {$aIndexTable[0]} USING {$aIndexTable[0]}, shop_search_reindex_queue WHERE {$aIndexTable[0]}.`shop_article_id`= shop_search_reindex_queue.object_id AND `shop_search_reindex_queue`.`processing`='1' ";
                    MySqlLegacySupport::getInstance()->query($query);
                }
                $sQuery = "DELETE FROM shop_search_reindex_queue WHERE `processing` = '1' AND `action` = 'delete'";
                MySqlLegacySupport::getInstance()->query($sQuery);

                $query = "SELECT COUNT(*) AS reccount FROM shop_search_reindex_queue WHERE `processing` = '1'";
                $aCount = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query));
                if (0 == $aCount['reccount']) {
                    $bWorkToDo = false;
                }
            } else {
                $bWorkToDo = false;
            }
        }
        if ($bWorkToDo) {
            $this->aTablesToProcess = [];
            $aData = $this->sqlData;
            $aData['started'] = date('Y-m-d H:i:s');
            $aData['completed'] = date('0000-00-00 00:00:00');
            $iTotalRows = 0;
            $query = 'SELECT shop_search_query.*
                    FROM shop_search_query
              INNER JOIN shop_search_field_weight ON shop_search_query.id = shop_search_field_weight.shop_search_query_id
                GROUP BY shop_search_query.id
                 ';
            $oIndexQueries = TdbShopSearchQueryList::GetList($query);
            while ($oIndexQuery = $oIndexQueries->Next()) {
                $oIndexQuery->StartIndex($this->bRegenerateCompleteIndex);
                $iNumberOfItems = $oIndexQuery->NumberOfRecordsLeftToIndex();
                if ($iNumberOfItems > 0) {
                    $iTotalRows += $iNumberOfItems;
                    $this->aTablesToProcess[] = $oIndexQuery->id;
                }
            }
            $aData['total_rows_to_process'] = $iTotalRows;
            $sTmp = serialize($this->aTablesToProcess);
            $aData['processdata'] = $sTmp;

            $this->LoadFromRow($aData);
            $this->AllowEditByAll(true);
            $this->Save();

            $this->CommitProcessData();

            $this->CreateIndexTables();
        }
    }

    /**
     * get the shop we use for config data for the indexer... for now we just take
     * the first we find. later we need some way to connect the two.
     *
     * @return TdbShop|false
     */
    protected static function GetShopConfigForIndexer()
    {
        static $oShop;
        if (!isset($oShop)) {
            // get first shop we find
            $oShops = TdbShopList::GetList();
            $oShop = $oShops->Current();
        }

        return $oShop;
    }

    /*
    * returns true, if ALL index tables exists
    * @return boolean
    */
    /**
     * @return bool
     */
    public function IndexHasContent()
    {
        $bHasContent = true;
        $aTables = static::GetAllIndexTableNames();
        foreach ($aTables as $sTableName => $iLength) {
            $bHasContent = ($bHasContent && TGlobal::TableExists($sTableName));
        }

        return $bHasContent;
    }

    /**
     * create all index tables for the field and drop tables no longer needed.
     *
     * @return void
     */
    public function CreateIndexTables()
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $aIndexTableNames = TdbShopSearchIndexer::GetAllIndexTableNames();
        $aTmpIndex = [];
        foreach ($aIndexTableNames as $sTableName => $length) {
            $aTmpIndex['_tmp' . $sTableName] = $length;
        }
        $aIndexTableNames = $aTmpIndex;

        foreach ($aIndexTableNames as $sTableName => $iLength) {
            if (TCMSRecord::TableExists($sTableName)) {
                $quotedTableName = $connection->quoteIdentifier($sTableName);
                $query = "DROP TABLE {$quotedTableName}";
                $connection->executeStatement($query);
            }

            $quotedTableName = $connection->quoteIdentifier($sTableName);

            $query = "
            CREATE TABLE {$quotedTableName} (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `shop_article_id` CHAR(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'the shop article',
                `substring` CHAR({$iLength}) NOT NULL COMMENT 'the substring',
                `cms_language_id` CHAR(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'language of substring',
                `occurrences` INT NOT NULL COMMENT 'Number of times the substring occured in the field for that article',
                `weight` FLOAT NOT NULL COMMENT 'calculated weight for the substring',
                `shop_search_field_weight_id` CHAR(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'field for which the search term was made'
            )
        ";
            $connection->executeStatement($query);
        }

        // drop index fields not needed
        $sBaseName = '_tmp' . self::INDEX_TBL_PREFIX;
        $quotedLike = $connection->quote($sBaseName . '%');
        $query = "SHOW TABLES LIKE {$quotedLike}";
        $statement = $connection->executeQuery($query);

        while (false !== ($aTableData = $statement->fetchNumeric())) {
            if (!array_key_exists($aTableData[0], $aIndexTableNames)) {
                $quotedTableToDrop = $connection->quoteIdentifier($aTableData[0]);
                $dropQuery = "DROP TABLE {$quotedTableToDrop}";
                $connection->executeStatement($dropQuery);
            }
        }
    }

    /**
     * @return false|null
     */
    protected function CopyIndexTables()
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');
        /* @var $logger \Psr\Log\LoggerInterface */
        $logger = ServiceLocator::get('monolog.logger.search_indexer');

        $logger->info('copy index tables start');
        $aIndexTableNames = TdbShopSearchIndexer::GetAllIndexTableNames();
        foreach ($aIndexTableNames as $sTableName => $iLength) {
            $logger->info('Load data for table ' . $sTableName);
            $sTmpTableName = '_tmp' . $sTableName;
            $quotedTmpTable = $connection->quoteIdentifier($sTmpTableName);

            if (CMS_SEARCH_INDEX_USE_LOAD_FILE) {
                $sFile = TdbShopSearchFieldWeight::GetTmpFileNameForTableImport($sTmpTableName);
                if (file_exists($sFile)) {
                    $quotedFile = $connection->quote($sFile);
                    $query = "LOAD DATA LOCAL INFILE {$quotedFile} INTO TABLE {$quotedTmpTable} (shop_article_id, substring, occurrences, weight, shop_search_field_weight_id)";
                    $connection->executeStatement($query);
                    unlink($sFile);
                }
            }
            $logger->info('Load data for table ' . $sTableName . ' DONE');

            if ($this->bRegenerateCompleteIndex) {
                $logger->info('recreate index for table ' . $sTableName);

                $queries = [
                    "ALTER TABLE {$quotedTmpTable} ADD INDEX (`substring`, `cms_language_id`, `shop_search_field_weight_id`)",
                    "ALTER TABLE {$quotedTmpTable} ADD INDEX (`shop_search_field_weight_id`, `cms_language_id`)",
                    "ALTER TABLE {$quotedTmpTable} ADD INDEX (`shop_article_id`, `substring`, `shop_search_field_weight_id`, `cms_language_id`)",
                ];
                foreach ($queries as $query) {
                    $connection->executeStatement($query);
                }

                $logger->info('recreate index for table ' . $sTableName . ' DONE');
            } else {
                $quotedTable = $connection->quoteIdentifier($sTableName);
                $query = "
                INSERT INTO {$quotedTable} (shop_article_id, substring, occurrences, weight, shop_search_field_weight_id, cms_language_id)
                SELECT shop_article_id, substring, occurrences, weight, shop_search_field_weight_id, cms_language_id
                FROM {$quotedTmpTable}
            ";
                $connection->executeStatement($query);

                $query = "DROP TABLE {$quotedTmpTable}";
                $connection->executeStatement($query);
            }
        }

        if ($this->bRegenerateCompleteIndex) {
            $logger->info('start table rename');
            reset($aIndexTableNames);

            $bAllowIndexRename = false;
            foreach ($aIndexTableNames as $sTableName => $iLength) {
                $sTmpTableName = '_tmp' . $sTableName;
                $quotedTmpTable = $connection->quoteIdentifier($sTmpTableName);
                $query = "SELECT COUNT(*) AS matches FROM {$quotedTmpTable}";
                $statement = $connection->executeQuery($query);
                $aMatches = $statement->fetchAssociative();

                if ($aMatches && (int) $aMatches['matches'] > 0) {
                    $bAllowIndexRename = true;
                    break;
                }
            }

            if (false === $bAllowIndexRename) {
                $logger->info('NO data in tmp index tables - keeping old index!');
                return false;
            }

            foreach ($aIndexTableNames as $sTableName => $iLength) {
                $logger->info('rename ' . $sTableName);
                $sTmpTableName = '_tmp' . $sTableName;
                $quotedTmpTable = $connection->quoteIdentifier($sTmpTableName);
                $quotedTable = $connection->quoteIdentifier($sTableName);

                if (TGlobal::TableExists($sTableName)) {
                    $tmpOldTable = $connection->quoteIdentifier($sTableName . '_tmp');
                    $query = "
                    RENAME TABLE {$quotedTable} TO {$tmpOldTable},
                                 {$quotedTmpTable} TO {$quotedTable}
                ";
                    $connection->executeStatement($query);

                    $query = "DROP TABLE {$tmpOldTable}";
                    $connection->executeStatement($query);
                } else {
                    $query = "RENAME TABLE {$quotedTmpTable} TO {$quotedTable}";
                    $connection->executeStatement($query);
                }
                $logger->info('rename ' . $sTableName . ' DONE');
            }
        }

        // now drop indext tables no longer needed
        // drop index fields not needed
        $sBaseName = self::INDEX_TBL_PREFIX;

        foreach (['', '_tmp'] as $prefix) {
            $likePattern = $prefix . $sBaseName . '%';
            $quotedPattern = $connection->quote($likePattern);
            $query = "SHOW TABLES LIKE {$quotedPattern}";
            $statement = $connection->executeQuery($query);

            while (false !== ($aTableData = $statement->fetchNumeric())) {
                if (!array_key_exists(str_replace('_tmp', '', $aTableData[0]), $aIndexTableNames)) {
                    $quotedOldTable = $connection->quoteIdentifier($aTableData[0]);
                    $query = "DROP TABLE {$quotedOldTable}";
                    $connection->executeStatement($query);
                }
            }
        }

        TdbShopSearchCache::ClearCompleteCache();
        $logger->info('search index create completed');
    }

    /**
     * get all index tables for the field.
     *
     * @return array
     */
    public static function GetAllIndexTableNames()
    {
        static $aNames;
        if (!isset($aNames)) {
            $aNames = [];
            $oShop = TdbShopSearchIndexer::GetShopConfigForIndexer();
            for ($i = $oShop->fieldShopSearchMinIndexLength; $i <= $oShop->fieldShopSearchMaxIndexLength; ++$i) {
                $aNames[TdbShopSearchIndexer::GetIndexTableNameForIndexLength($i)] = $i;
            }
        }

        return $aNames;
    }

    /**
     * return the index table name for a given index length.
     *
     * @param int $iIndexLength - index length
     *
     * @return string
     */
    public static function GetIndexTableNameForIndexLength($iIndexLength)
    {
        return self::INDEX_TBL_PREFIX.$iIndexLength;
    }

    /**
     * return a search for the given search term(s) - but cache the query for later use.
     *
     * @param string $sSearchTerm - searched for in all fields (if not empty)
     * @param array $aSearchTerms - assoc array with shop_search_field_weight_id as key - search only the specified fields
     * @param array $aFilter - any sql filters you want to add
     * @param string $sLanguageId - the language we search in. if null, we get the language from TGlobal
     *
     * @return string
     */
    public static function GetSearchQuery($sSearchTerm, $aSearchTerms = null, $aFilter = [], $sLanguageId = null)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $sLanguageId = self::getLanguageService()->getActiveLanguageId();

        $aCacheKeys = [
            'class' => __CLASS__,
            'type' => 'searchquery',
            'sSearchTerm' => $sSearchTerm,
            'aSearchTerms' => serialize($aSearchTerms),
            'cms_language_id' => $sLanguageId,
        ];
        $sCacheKey = TCacheManagerRuntimeCache::GetKey($aCacheKeys);
        $sQuery = self::GenerateSearchQuery($sSearchTerm, $sLanguageId, $aSearchTerms);

        // now create search cache.. unless a cache entry exists
        $oSearchCache = TdbShopSearchCache::CreateSearchCache($sCacheKey, $sQuery, $sSearchTerm, $aSearchTerms, $aFilter);

        $quotedCacheId = $connection->quote($oSearchCache->id);

        $sQuery = "
        SELECT DISTINCT
            `shop_search_cache_item`.`weight` AS cms_search_weight,
            `shop_article`.*
        FROM `shop_article`
        LEFT JOIN `shop_article_stats` ON `shop_article`.`id` = `shop_article_stats`.`shop_article_id`
        INNER JOIN `shop_search_cache_item` ON `shop_article`.`id` = `shop_search_cache_item`.`shop_article_id`
        LEFT JOIN `shop_manufacturer` ON `shop_article`.`shop_manufacturer_id` = `shop_manufacturer`.`id`
        LEFT JOIN `shop_category` ON `shop_article`.`shop_category_id` = `shop_category`.`id`
        LEFT JOIN `shop_article_shop_category_mlt` ON `shop_article`.`id` = `shop_article_shop_category_mlt`.`source_id`
        WHERE `shop_search_cache_item`.`shop_search_cache_id` = {$quotedCacheId}
    ";

        return $sQuery;
    }

    /**
     * return a search for the given search term(s).
     *
     * @param string $sSearchTerm - searched for in all fields (if not empty)
     * @param string $sLanguageId - the language we are searching in. is no language passed, then we will get the id from TGlobal
     * @param array $aSearchTerms - assoc array with shop_search_field_weight_id as key - search only the specified fields
     *
     * @return string
     */
    protected static function GenerateSearchQuery($sSearchTerm, $sLanguageId, $aSearchTerms = null)
    {
        // add general word... if it exists
        $aTableList = [];
        $aTableQueries = [];
        $manualArticleSelection = [];

        if (!empty($sSearchTerm)) {
            $bFilterIgnoreIgnoreWords = false; // default = false because we would lose partial words
            if (TdbShopSearchIndexer::searchWithAND()) {
                // we have to exclude ignore words, else you would not get any results for searches with an ignore word
                $bFilterIgnoreIgnoreWords = true;
            }
            $aTerms = TdbShopSearchIndexer::PrepareSearchWords($sSearchTerm, $bFilterIgnoreIgnoreWords);
            if (count($aTerms) > 0) {
                $aSearchInfo = TdbShopSearchIndexer::GetWordListQuery($aTerms, $sLanguageId);
                $aAffectedTables = array_keys($aSearchInfo);

                $aTableList = array_merge($aTableList, $aAffectedTables);
                foreach ($aSearchInfo as $sQueryTableName => $aQueries) {
                    if (!array_key_exists($sQueryTableName, $aTableQueries)) {
                        $aTableQueries[$sQueryTableName] = [];
                    }
                    $aTableQueries[$sQueryTableName] = array_merge($aTableQueries[$sQueryTableName], $aQueries);
                }

                // fetch manually selected articles for search words
                $oShop = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
                $oManuelArticleSelections = TdbShopSearchKeywordArticleList::GetListForShopKeywords($oShop->id, $aTerms, $sLanguageId);
                while ($oManuelArticleSelection = $oManuelArticleSelections->Next()) {
                    $aTmpArticleList = $oManuelArticleSelection->GetMLTIdList('shop_article');
                    $manualArticleSelection = array_merge($manualArticleSelection, $aTmpArticleList);
                }
            }
        }

        // now work through the other search terms
        if (is_array($aSearchTerms)) {
            foreach ($aSearchTerms as $iFieldIndex => $sTerm) {
                $aTerms = TdbShopSearchIndexer::PrepareSearchWords($sTerm, false);
                if (count($aTerms) > 0) {
                    $aSearchInfo = TdbShopSearchIndexer::GetWordListQuery($aTerms, $sLanguageId, [$iFieldIndex], 'AND');
                    $aAffectedTables = array_keys($aSearchInfo);
                    $aTableList = array_merge($aTableList, $aAffectedTables);
                    foreach ($aSearchInfo as $sQueryTableName => $aQueries) {
                        if (!array_key_exists($sQueryTableName, $aTableQueries)) {
                            $aTableQueries[$sQueryTableName] = [];
                        }
                        $aTableQueries[$sQueryTableName] = array_merge($aTableQueries[$sQueryTableName], $aQueries);
                    }
                }
            }
        }

        if (count($aTableList) < 1) {
            // we found nothing... so return a valid query with no results
            $sQuery = 'SELECT `shop_article`.`id` AS shop_article_id, 1 AS wordhit, 0 AS cms_search_weight
                      FROM shop_article
                 LEFT JOIN `shop_article_stats` ON `shop_article`.`id` = `shop_article_stats`.`shop_article_id`
                    WHERE 1=0
                  ';
        } else {
            $manualSelectionWeight = '';
            $quotedManualArticleSelectionString = implode(',',
                array_map([self::getDatabaseConnectionStatic(), 'quote'], $manualArticleSelection));
            if (count($manualArticleSelection) > 0) {
                $manualSelectionWeight = " + IF(shop_article_id IN ($quotedManualArticleSelectionString),100,0)";
            }
            $aQueryBlocks = [];
            foreach ($aTableList as $sTableName) {
                if (count($aTableQueries[$sTableName]) > 1 && TdbShopSearchIndexer::searchWithAND()) {
                    foreach ($aTableQueries[$sTableName] as $sTmpWordString) {
                        $aQueryBlocks[] = "select *, 1 AS wordhit,(sum(weight)$manualSelectionWeight) AS cms_search_weight
                                   from {$sTableName} where {$sTmpWordString}
                               group by shop_article_id ";
                    }
                } else {
                    $aQueryBlocks[] = "select *, 1 AS wordhit,(sum(weight)$manualSelectionWeight) AS cms_search_weight
                          from {$sTableName} where (".implode(' OR ', $aTableQueries[$sTableName]).')
                         group by shop_article_id ';
                }
            }
            if (count($manualArticleSelection) > 0) {
                $query = "SELECT
                            0 as id,
                            `shop_article`.`id` AS shop_article_id,
                           'xxx' substring,
                           '".$sLanguageId."' cms_language_id,
                           1 AS occurrences,
                           100 AS weight,
                           'xxx' AS shop_search_field_weight_id,
                           '1' AS wordhit,
                           100 AS cms_search_weight
                      FROM shop_article WHERE id IN ($quotedManualArticleSelectionString)";
                $aQueryBlocks[] = $query;
            }

            $sQuery = implode(' UNION ALL ', $aQueryBlocks);
        }

        return $sQuery;
    }

    /**
     * return subquery that searches for the terms in aTerms in all fields defined by aFieldRestriction
     * if no restrctions are passed, we search in all.
     *
     * @param array $aTerms
     * @param array $aFieldRestrictions
     * @param string $sTypeOfFieldRestriction - if the field restrictions are ORed or ANDed
     * @param string $sLanguageId
     *
     * @return array - returns an array with one parameter holding the query, the other a list of relevant table names
     */
    protected static function GetWordListQuery($aTerms, $sLanguageId, $aFieldRestrictions = null, $sTypeOfFieldRestriction = 'OR')
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $aTableQueries = [];

        // get affected tables
        $aTableList = [];
        foreach ($aTerms as $sTerm) {
            $sTableName = TdbShopSearchIndexer::GetIndexTableNameForIndexLength(mb_strlen($sTerm));
            if (!in_array($sTableName, $aTableList, true)) {
                $aTableList[] = $sTableName;
            }

            if (false === TdbShopSearchIndexer::searchWithAND()) {
                $sSoundEX = TdbShopSearchIndexer::GetSoundexForWord($sTerm);
                $sSoundTable = TdbShopSearchIndexer::GetIndexTableNameForIndexLength(mb_strlen($sSoundEX));
                if (!in_array($sSoundTable, $aTableList, true)) {
                    $aTableList[] = $sSoundTable;
                }
            }
        }

        $aFieldRestrictionQueries = [];
        if (is_array($aFieldRestrictions)) {
            foreach ($aTableList as $sTableName) {
                $quotedRestrictions = array_map(function ($value) use ($connection) {
                    return $connection->quote($value);
                }, $aFieldRestrictions);
                $aFieldRestrictionQueries[$sTableName] = '(' . implode(',', $quotedRestrictions) . ')';
            }
        }

        // build query
        $aTableQueries = [];
        $quotedLanguageId = $connection->quote($sLanguageId);

        foreach ($aTerms as $sTerm) {
            $sTermTable = TdbShopSearchIndexer::GetIndexTableNameForIndexLength(mb_strlen($sTerm));
            if (!array_key_exists($sTermTable, $aTableQueries)) {
                $aTableQueries[$sTermTable] = [];
            }

            $quotedTerm = $connection->quote($sTerm);

            $sTmpQuery = " (
            {$sTermTable}.`substring` = {$quotedTerm}
            AND ({$sTermTable}.`cms_language_id` = {$quotedLanguageId} OR {$sTermTable}.`cms_language_id` = '')
        ";
            if (isset($aFieldRestrictionQueries[$sTermTable])) {
                $sTmpQuery .= " AND {$sTermTable}.`shop_search_field_weight_id` IN {$aFieldRestrictionQueries[$sTermTable]}";
            }
            $sTmpQuery .= ')';
            $aTableQueries[$sTermTable][] = $sTmpQuery;

            if (false === TdbShopSearchIndexer::searchWithAND()) {
                // OR `{$sSoundTable}`.`substring` = '".MySqlLegacySupport::getInstance()->real_escape_string($sSoundEX)."')
                $sSoundEX = TdbShopSearchIndexer::GetSoundexForWord($sTerm);
                if ('0000' !== $sSoundEX) {
                    $sSoundTable = TdbShopSearchIndexer::GetIndexTableNameForIndexLength(mb_strlen($sSoundEX));

                    if (!array_key_exists($sSoundTable, $aTableQueries)) {
                        $aTableQueries[$sSoundTable] = [];
                    }

                    $quotedSoundEX = $connection->quote($sSoundEX);

                    $sTmpQuerySoundex = " (
                    {$sSoundTable}.`substring` = {$quotedSoundEX}
                    AND ({$sSoundTable}.`cms_language_id` = {$quotedLanguageId} OR {$sSoundTable}.`cms_language_id` = '')
                ";
                    if (isset($aFieldRestrictionQueries[$sSoundTable])) {
                        $sTmpQuerySoundex .= " AND {$sSoundTable}.`shop_search_field_weight_id` IN {$aFieldRestrictionQueries[$sSoundTable]}";
                    }
                    $sTmpQuerySoundex .= ')';
                    $aTableQueries[$sSoundTable][] = $sTmpQuerySoundex;
                }
            }
        }

        return $aTableQueries;
    }

    /**
     * splits the search words into an array with prepared search words.
     *
     * @param bool $bFilterIgnoreWords - removes ignore words form the list if set
     * @param string $sOrigianlString
     *
     * @return array
     */
    public static function PrepareSearchWords($sOrigianlString, $bFilterIgnoreWords = true)
    {
        $aWords = [];
        $sOrigianlString = strip_tags($sOrigianlString);
        $sOrigianlString = str_replace(['/', "\n", '-', '_', '&', '+'], [' ', ' ', ' ', ' ', ' ', ' '], $sOrigianlString);
        $aTmpWords = explode(' ', $sOrigianlString);
        foreach ($aTmpWords as $iIndex => $sWord) {
            $sWord = trim($sWord);
            if (!empty($sWord)) {
                $sTmpWord = TdbShopSearchIndexer::PrepareSearchWord($sWord, $bFilterIgnoreWords);
                if (!empty($sTmpWord)) {
                    $aWords[] = $sTmpWord;
                }
            }
        }

        return $aWords;
    }

    /**
     * cut the word the the right size, remove umlaute, etc.
     *
     * @param string $sWord
     * @param bool $bFilterIgnoreWords - removes ignore words if set
     *
     * @return string
     */
    public static function PrepareSearchWord($sWord, $bFilterIgnoreWords = true)
    {
        $sCleanWord = trim($sWord);

        // remove .,:;"'
        $aRemove = ['°' => '', '´' => '', '*' => '', '`' => '', '.' => '', ',' => '', ':' => '', ';' => '', '"' => '', "'" => '', '-' => '', '?' => '', '!' => '', '(' => '', ')' => '', '#' => '', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', 'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue'];
        $sCleanWord = str_replace(array_keys($aRemove), array_values($aRemove), $sCleanWord);

        $oShop = TdbShopSearchIndexer::GetShopConfigForIndexer();
        $iMinWordLength = $oShop->fieldShopSearchMinIndexLength;
        $iMaxWordLength = $oShop->fieldShopSearchMaxIndexLength;

        if (mb_strlen($sCleanWord) >= $iMinWordLength) {
            if (mb_strlen($sCleanWord) > $iMaxWordLength) {
                $sCleanWord = mb_substr($sCleanWord, 0, $iMaxWordLength);
            }
        } else {
            $sCleanWord = '';
        }

        if ($bFilterIgnoreWords && TdbShopSearchIndexer::IsIgnoreWord($sCleanWord)) {
            $sCleanWord = '';
        }
        $sCleanWord = trim($sCleanWord);

        $sCleanWord = mb_strtolower($sCleanWord);

        return $sCleanWord;
    }

    /**
     * return true if the word is in the ignore list.
     *
     * @param string $sWord
     *
     * @return bool
     */
    protected static function IsIgnoreWord($sWord)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        static $aIgnoreWordCache;
        static $bCompleteLoad = null;

        // first try to cache all... if we have too many (more than 500) then work on a word base system instead
        if (!isset($aIgnoreWordCache)) {
            $aIgnoreWordCache = [];
            $oShop = TdbShopSearchIndexer::GetShopConfigForIndexer();

            $quotedShopId = $connection->quote($oShop->id);

            $query = "
            SELECT DISTINCT `name`
              FROM `shop_search_ignore_word`
             WHERE `shop_id` = {$quotedShopId}
        ";

            $statement = $connection->executeQuery($query);

            if ($statement->rowCount() <= 500) {
                $bCompleteLoad = true;
            } else {
                $bCompleteLoad = false;
            }

            $count = 0;
            while ($count < 500 && ($aRow = $statement->fetchNumeric())) {
                $aIgnoreWordCache[mb_strtolower($aRow[0])] = 1; // store as assoc so we later have a quick lookup
                ++$count;
            }
        }

        $sTmpWord = mb_strtolower($sWord);
        $bIgnore = false;

        if (array_key_exists($sTmpWord, $aIgnoreWordCache)) {
            $bIgnore = true;
        } elseif (false === $bCompleteLoad) {
            // check if we find it in the db
            $oShop = TdbShopSearchIndexer::GetShopConfigForIndexer();

            $quotedShopId = $connection->quote($oShop->id);
            $quotedWord = $connection->quote($sWord);

            $query = "
            SELECT DISTINCT `name`
              FROM `shop_search_ignore_word`
             WHERE `shop_id` = {$quotedShopId}
               AND `name` = {$quotedWord}
        ";

            $statement = $connection->executeQuery($query);

            if ($statement->rowCount() > 0) {
                $aIgnoreWordCache[$sTmpWord] = 1;
                $bIgnore = true;
            }
        }

        return $bIgnore;
    }

    /**
     * original word.
     *
     * @param string $sWord
     *
     * @return string
     */
    public static function GetSoundexForWord($sWord)
    {
        return soundex($sWord);
    }

    /**
     * @param string $sTable
     * @param string $sId
     * @param string $sType
     *
     * @return false|null
     */
    public static function UpdateIndex($sTable, $sId, $sType)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        if (false === PKG_SEARCH_USE_SEARCH_QUEUE) {
            return false;
        }

        static $bReindexQueueExists = false;

        if (false === $bReindexQueueExists) {
            $bReindexQueueExists = true;
            if (false === TGlobal::TableExists('shop_search_reindex_queue')) {
                $query = "
                CREATE TABLE IF NOT EXISTS `shop_search_reindex_queue` (
                    `object_id` CHAR(36) NOT NULL,
                    `datecreated` DATETIME NOT NULL,
                    `action` ENUM('update','delete') NOT NULL,
                    `processing` ENUM('0','1') NOT NULL,
                    KEY `processing` (`processing`),
                    KEY `action` (`action`),
                    UNIQUE `object_id` (`object_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='holds ids of objects that need to be reindext'
            ";
                $connection->executeStatement($query);
            }
        }

        $quotedTable = $connection->quote($sTable);

        // also trigger changes in the search index (cache)
        $query = "
        SELECT `shop_search_field_weight`.*, `shop_search_query`.`query`
          FROM `shop_search_field_weight`
    INNER JOIN `shop_search_query` ON `shop_search_field_weight`.`shop_search_query_id` = `shop_search_query`.`id`
         WHERE `shop_search_field_weight`.`tablename` = {$quotedTable}
      GROUP BY `shop_search_query_id`
    ";

        $statement = $connection->executeQuery($query);

        while ($aSearchField = $statement->fetchAssociative()) {
            $sQuery = $aSearchField['query'];

            // Add restriction
            $oRecordList = new TCMSRecordList();
            $oRecordList->Load($sQuery);
            $oRecordList->AddFilterString('`' . $sTable . "`.`id` = " . $connection->quote($sId));
            $sNewQuery = $oRecordList->GetActiveQuery();

            $quotedType = $connection->quote($sType);
            $currentDateTime = $connection->quote(date('Y-m-d H:i:s'));

            $sTmpQuery = "
            SELECT CMSQUERY._shop_article_id AS shop_article_id, {$currentDateTime}, {$quotedType}, '0'
              FROM ({$sNewQuery}) AS CMSQUERY
        ";

            try {
                $connection->executeStatement($sTmpQuery);
            } catch (\Exception $e) {
                // Fallback in case of alias name problems
                $sTmpQuery = "
                SELECT CMSQUERY.shop_article_id AS shop_article_id, {$currentDateTime}, {$quotedType}, '0'
                  FROM ({$sNewQuery}) AS CMSQUERY
            ";
            }

            $query = "
            REPLACE DELAYED `shop_search_reindex_queue` (`object_id`, `datecreated`, `action`, `processing`)
            {$sTmpQuery}
        ";
            $connection->executeStatement($query);
        }
    }

    /**
     * returns always true!
     * non AND searches are disabled currently.
     *
     * @return bool
     */
    public static function searchWithAND()
    {
        $bUseAnd = false;
        $oShop = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
        if ($oShop && array_key_exists('shop_search_use_boolean_and', $oShop->sqlData)) {
            $bUseAnd = ('1' == $oShop->sqlData['shop_search_use_boolean_and']);
        }

        return $bUseAnd;
    }

    /**
     * @return Connection
     */
    private static function getDatabaseConnectionStatic()
    {
        return ServiceLocator::get('database_connection');
    }

    private function getCache(): CacheInterface
    {
        return ServiceLocator::get('chameleon_system_core.cache');
    }
}
