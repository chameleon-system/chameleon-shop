<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopSearchCache extends TShopSearchCacheAutoParent
{
    public const MAX_CACHE_AGE_IN_SECONDS = 3600;

    /**
     * the search term.
     *
     * @var string
     */
    public $sSearchTerm;

    /**
     * the search term spellchecked.
     *
     * @var string
     */
    public $sSearchTermSpellChecked;

    /**
     * the search term spellchecked.
     *
     * @var string
     */
    public $sSearchTermSpellCheckedFormated;
    /**
     * a list of field specific search terms.
     *
     * @var array
     */
    public $aSearchTerms;
    /**
     * any filter applied to the search.
     *
     * @var array
     */
    public $aFilter;

    /**
     * @param string|null $id
     * @param string|null $sLanguageId
     */
    public function __construct($id = null, $sLanguageId = null)
    {
        $this->SetChangeTriggerCacheChange(false);
        parent::__construct($id, $sLanguageId);
    }

    /**
     * return current filter.
     *
     * @return array
     */
    protected function GetCurrentFilter()
    {
        static $aFilter = 'x';
        if ('x' == $aFilter) {
            $oShop = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
            $aFilter = $oShop->GetActiveFilter();
        }

        return $aFilter;
    }

    /**
     * return a search query based on the current filter.
     *
     * @param array $aExcludeFilterKeys - any filter keys (same as in $this->aFilter) you place in here
     *                                  will be excluded from the link
     * @param array<string, string> $aFilterAddition
     *
     * @return string
     */
    public function GetSearchLink($aFilterAddition = [], $aExcludeFilterKeys = [])
    {
        $sLink = '';

        $aParams = [];

        if (is_array($this->aSearchTerms) && count($this->aSearchTerms) > 0) {
            $aParams[TShopModuleArticlelistFilterSearch::PARAM_QUERY.'[0]'] = $this->sSearchTerm;
            reset($this->aSearchTerms);
            foreach ($this->aSearchTerms as $fieldId => $sVal) {
                $aParams[TShopModuleArticlelistFilterSearch::PARAM_QUERY."[{$fieldId}]"] = $sVal;
            }
            reset($this->aSearchTerms);
        } else {
            $aParams[TShopModuleArticlelistFilterSearch::PARAM_QUERY] = $this->sSearchTerm;
        }

        $aFilter = $this->GetCurrentFilter();
        if (!is_array($aFilter) && count($aFilterAddition) > 0) {
            $aFilter = [];
        }
        foreach ($aFilterAddition as $sFilterKey => $sFilterValue) {
            $aFilter[$sFilterKey] = $sFilterValue;
        }

        // add filter
        if (is_array($aFilter) && count($aFilter) > 0) {
            reset($aFilter);
            foreach ($aFilter as $sFilterKeyName => $sVal) {
                if (!in_array($sFilterKeyName, $aExcludeFilterKeys)) {
                    $aParams[TShopModuleArticlelistFilterSearch::URL_FILTER."[{$sFilterKeyName}]"] = $sVal;
                }
            }
        }

        $oShop = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
        $sLink = $oShop->GetLinkToSystemPage('search', $aParams);

        return $sLink;
    }

    /**
     * @param string $sTerm
     *
     * @return string
     */
    public function GetSearchLinkForTerm($sTerm)
    {
        $aParams = [TShopModuleArticlelistFilterSearch::PARAM_QUERY => $sTerm];
        $oShop = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
        $sLink = $oShop->GetLinkToSystemPage('search', $aParams);

        return $sLink;
    }

    /**
     * @param string $sKey
     * @param string|null $sShopId
     *
     * @return TdbShopSearchCache|null
     */
    protected static function GetSearchCacheItem($sKey, $sShopId = null)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $oItem = TdbShopSearchCache::GetNewInstance();
        $oItem->SetEnableObjectCaching(false);

        $aFindSearchCacheFilter = [
            '`searchkey` = '.$connection->quote($sKey),
        ];
        if (null !== $sShopId) {
            $aFindSearchCacheFilter[] = '`shop_id` = '.$connection->quote($sShopId);
        }

        $query = 'SELECT * FROM `shop_search_cache`
              WHERE '.implode(' AND ', $aFindSearchCacheFilter);

        $iMaxNumberOfAttempts = 50;
        $bDone = false;
        do {
            $statement = $connection->executeQuery($query);
            $aItem = $statement->fetchAssociative();

            if ($aItem) {
                /* @var $oItem TdbShopSearchCache */
                $oItem->LoadFromRow($aItem);
                if ($oItem->CacheIsStale()) {
                    $oItem->ClearCache();
                    $oItem = null;
                }
            } else {
                $oItem = null;
            }

            if (null !== $oItem && -1 == $oItem->fieldNumberOfRecordsFound) {
                $itemDate = DateTime::createFromFormat('Y-m-d H:i:s', $oItem->fieldLastUsedDate);
                if ((time() - $itemDate->getTimestamp()) < 5) {
                    // search is being executed by another process... give it a chance to complete
                    --$iMaxNumberOfAttempts;
                    usleep(50000);
                } else {
                    $bDone = true;
                }
            } else {
                $bDone = true;
            }
        } while (false === $bDone && $iMaxNumberOfAttempts > 0);

        if (0 == $iMaxNumberOfAttempts) {
            $oItem = null;
        }

        return $oItem;
    }

    /**
     * creates/refreshes a search cache query.
     *
     * @param string $sKey - key identifying the search
     * @param string $sQuery
     * @param string $sSearchTerm
     * @param array|null $aSearchTerms
     * @param array $aFilter
     *
     * @return TdbShopSearchCache
     */
    public static function CreateSearchCache($sKey, $sQuery, $sSearchTerm, $aSearchTerms = null, $aFilter = [])
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $sShopId = null;
        $oShop = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
        if ($oShop) {
            $sShopId = $oShop->id;
        }
        $oItem = self::GetSearchCacheItem($sKey, $sShopId);

        if (null === $oItem) {
            $oItem = TdbShopSearchCache::GetNewInstance();
            $aData = [
                'shop_id' => $oShop->id,
                'searchkey' => $sKey,
                'last_used_date' => date('Y-m-d H:i:s'),
                'number_of_records_found' => -1,
            ];
            $oItem->LoadFromRow($aData);
            $oItem->AllowEditByAll(true);
            $oItem->Save();
            // die(0);
            $quotedItemId = $connection->quote($oItem->id);

            $sTmpQuery = "
            SELECT
                MD5(CONCAT({$quotedItemId}, '-', tmpres.shop_article_id)) AS shop_search_cache_item_id,
                {$quotedItemId} AS shop_search_cache_id,
                SUM(tmpres.cms_search_weight) AS cms_search_weight,
                tmpres.shop_article_id AS id
            FROM ({$sQuery}) AS tmpres
            GROUP BY tmpres.shop_article_id
        ";

            if (TdbShopSearchIndexer::searchWithAND()) {
                $aTerms = TdbShopSearchIndexer::PrepareSearchWords($sSearchTerm, true);
                $sTmpQuery .= ' HAVING SUM(tmpres.wordhit) >= '.count($aTerms);
            }

            $sTmpQuery = $oItem->ProcessSearchQueryBeforeInsert($sTmpQuery);

            $insertQuery = "
            INSERT INTO `shop_search_cache_item` (`id`, `shop_search_cache_id`, `weight`, `shop_article_id`)
            {$sTmpQuery}
        ";

            $affectedRows = $connection->executeStatement($insertQuery);

            $oItem->sqlData['number_of_records_found'] = $affectedRows;
            $oItem->fieldNumberOfRecordsFound = $affectedRows;
            $oItem->AllowEditByAll(true);
            $oItem->SaveFieldsFast(['number_of_records_found' => $oItem->fieldNumberOfRecordsFound]);
        } else {
            $aData = $oItem->sqlData;
            $aData['last_used_date'] = date('Y-m-d H:i:s');
            $oItem->LoadFromRow($aData);
            $oItem->AllowEditByAll(true);
            $oItem->SaveFieldsFast(['last_used_date' => $aData['last_used_date']]);
        }

        $oItem->sSearchTerm = $sSearchTerm;

        if (true === CMS_SHOP_SEARCH_ENABLE_SPELLCHECKER) {
            $oSpellCheck = TCMSSpellcheck::GetInstance();
            $oSearchCacheObject = TdbShopSearchCache::GetNewInstance();
            $aCorrections = false;
            if (!empty($sSearchTerm)) {
                $aCorrections = $oSpellCheck->SuggestCorrection($oItem->sSearchTerm, [$oSearchCacheObject, 'GetBestSuggestion']);
            }
            if ($aCorrections) {
                $oItem->sSearchTermSpellChecked = $aCorrections['string'];
                $oItem->sSearchTermSpellCheckedFormated = $oItem->sSearchTerm;
                foreach ($aCorrections['corrections'] as $sOrg => $sCorrection) {
                    $oItem->sSearchTermSpellCheckedFormated = str_replace($sOrg, '<span class="correction">'.$sCorrection.'</span>', $oItem->sSearchTermSpellCheckedFormated);
                }
            } else {
                $oItem->sSearchTermSpellChecked = null;
                $oItem->sSearchTermSpellCheckedFormated = null;
            }
        }

        $oItem->aSearchTerms = $aSearchTerms;
        $oItem->aFilter = $aFilter;
        $oShop->SetActiveSearchCacheObject($oItem);

        return $oItem;
    }

    /**
     * callback usable to manipulate the search cache query before it is executed.
     *
     * @param string $sQuery
     *
     * @return string
     */
    public function ProcessSearchQueryBeforeInsert($sQuery)
    {
        return $sQuery;
    }

    /**
     * @param string[] $aWords
     *
     * @return string
     *
     * @throws TPkgCmsException_Log
     */
    public function GetBestSuggestion($aWords)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $sBestWord = '';
        reset($aWords);

        while (empty($sBestWord) && ($sWord = current($aWords))) {
            $sEscapedWord = TShopSearchIndexer::PrepareSearchWord($sWord);
            if (!empty($sEscapedWord)) {
                $sIndexTable = TShopSearchIndexer::GetIndexTableNameForIndexLength(mb_strlen($sEscapedWord));

                $quotedTable = $connection->quoteIdentifier($sIndexTable);
                $quotedWord = $connection->quote($sEscapedWord);

                $query = "SELECT * FROM {$quotedTable} WHERE `substring` = {$quotedWord}";

                $statement = $connection->executeQuery($query);

                if ($statement->rowCount() > 0) {
                    $sBestWord = $sWord;
                }
            }
            next($aWords);
        }

        if (empty($sBestWord) && !empty($aWords)) {
            $sBestWord = $aWords[0];
        }

        return $sBestWord;
    }

    /**
     * return array with categorie ids and number of hits for that category.
     *
     * @param string $sFilters
     *
     * @return array
     */
    public function GetSearchResultCategoryHits($sFilters = '')
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $aHits = [];
        if (!empty($this->fieldCategoryHits) && empty($sFilters)) {
            $aHits = unserialize($this->fieldCategoryHits);
        } else {
            $aCategoryCount = [];
            $sRestriction = '';

            $query = '';
            $quotedId = $connection->quote($this->id);

            if (!empty($sFilters)) {
                $query = "
                SELECT COUNT(DISTINCT `shop_search_cache_item`.`id`) AS shop_search_cache_item_count, `shop_category`.*
                  FROM `shop_search_cache_item`
            INNER JOIN `shop_article` ON `shop_search_cache_item`.`shop_article_id` = `shop_article`.`id`
            INNER JOIN `shop_article_shop_category_mlt` ON `shop_search_cache_item`.`shop_article_id` = `shop_article_shop_category_mlt`.`source_id`
            INNER JOIN `shop_category` ON `shop_article_shop_category_mlt`.`target_id` = `shop_category`.`id`
                 WHERE `shop_search_cache_item`.`shop_search_cache_id` = {$quotedId}
                   AND {$sFilters}
              GROUP BY `shop_category`.`id`
            ";
            } else {
                $query = "
                SELECT COUNT(DISTINCT `shop_search_cache_item`.`id`) AS shop_search_cache_item_count, `shop_category`.*
                  FROM `shop_search_cache_item`
            INNER JOIN `shop_article_shop_category_mlt` ON `shop_search_cache_item`.`shop_article_id` = `shop_article_shop_category_mlt`.`source_id`
            INNER JOIN `shop_category` ON `shop_article_shop_category_mlt`.`target_id` = `shop_category`.`id`
                 WHERE `shop_search_cache_item`.`shop_search_cache_id` = {$quotedId}
              GROUP BY `shop_category`.`id`
            ";
            }

            $oCatList = TdbShopCategoryList::GetList($query);

            // now organize list into a tree... we trace back each node untill we reach a root node... in the end we
            // will have a collection of rows. then we merge these
            while ($oCat = $oCatList->Next()) {
                if (!array_key_exists($oCat->id, $aHits)) {
                    $aHits[$oCat->id] = 0;
                }
                $aHits[$oCat->id] += $oCat->sqlData['shop_search_cache_item_count'];

                // add all children to vector
                while ($oParent = $oCat->GetParent()) {
                    $oParent->sqlData['shop_search_cache_item_count'] = $oCat->sqlData['shop_search_cache_item_count'];
                    if (!array_key_exists($oParent->id, $aHits)) {
                        $aHits[$oParent->id] = 0;
                    }
                    $aHits[$oParent->id] += $oParent->sqlData['shop_search_cache_item_count'];

                    $oCat = $oParent;
                }
            }

            if (empty($sFilters)) {
                $this->fieldCategoryHits = serialize($aHits);
                $this->sqlData['category_hits'] = $this->fieldCategoryHits;
                $this->AllowEditByAll(true);
                $this->Save();
            }
        }

        return $aHits;
    }

    /**
     * return number of records found.
     *
     * @return int
     */
    public function GetNumberOfHits()
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        if ($this->fieldNumberOfRecordsFound < 0) {
            $quotedId = $connection->quote($this->id);

            $query = "
            SELECT COUNT(*) AS recs
              FROM `shop_search_cache_item`
             WHERE `shop_search_cache_id` = {$quotedId}
        ";

            $statement = $connection->executeQuery($query);
            $row = $statement->fetchAssociative();

            if ($row) {
                $this->fieldNumberOfRecordsFound = (int) $row['recs'];
                $this->sqlData['number_of_records_found'] = $this->fieldNumberOfRecordsFound;
                $this->AllowEditByAll(true);
                $this->Save();
            }
        }

        return $this->fieldNumberOfRecordsFound;
    }

    /**
     * return true if the cache has become stale - will remove stale cache entries.
     *
     * @return bool
     */
    public function CacheIsStale()
    {
        $cachAge = strtotime($this->fieldLastUsedDate);
        $bIsStale = ((time() - $cachAge) > TdbShopSearchCache::MAX_CACHE_AGE_IN_SECONDS);

        return $bIsStale;
    }

    /**
     * clear cache item.
     *
     * @return void
     */
    public function ClearCache()
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $quotedId = $connection->quote($this->id);

        $query = "
        DELETE FROM `shop_search_cache_item`
         WHERE `shop_search_cache_id` = {$quotedId}
    ";
        $connection->executeStatement($query);

        $query = "
        DELETE FROM `shop_search_cache`
         WHERE `id` = {$quotedId}
    ";
        $connection->executeStatement($query);

        $this->id = null;
        $this->sqlData['id'] = null;
    }

    /**
     * @return void
     */
    public static function ClearCompleteCache()
    {
        $query = 'TRUNCATE `shop_search_cache_item`';
        MySqlLegacySupport::getInstance()->query($query);
        $query = 'TRUNCATE `shop_search_cache`';
        MySqlLegacySupport::getInstance()->query($query);
    }
}
