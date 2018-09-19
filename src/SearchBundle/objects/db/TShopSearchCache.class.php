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
    const MAX_CACHE_AGE_IN_SECONDS = 3600;

    /**
     * the search term.
     *
     * @var string
     */
    public $sSearchTerm = null;

    /**
     * the search term spellchecked.
     *
     * @var string
     */
    public $sSearchTermSpellChecked = null;

    /**
     * the search term spellchecked.
     *
     * @var string
     */
    public $sSearchTermSpellCheckedFormated = null;
    /**
     * a list of field specific search terms.
     *
     * @var array
     */
    public $aSearchTerms = null;
    /**
     * any filter applied to the search.
     *
     * @var array
     */
    public $aFilter = null;

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
            $oShop = TdbShop::GetInstance();
            $aFilter = $oShop->GetActiveFilter();
        }

        return $aFilter;
    }

    /**
     * return a search query based on the current filter.
     *
     * @param array $aExcludeFilterKeys - any filter keys (same as in $this->aFilter) you place in here
     *                                  will be excluded from the link
     *
     * @return string
     */
    public function GetSearchLink($aFilterAddition = array(), $aExcludeFilterKeys = array())
    {
        $sLink = '';

        $aParams = array();

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
            $aFilter = array();
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

        $oShop = TdbShop::GetInstance();
        $sLink = $oShop->GetLinkToSystemPage('search', $aParams);

        return $sLink;
    }

    public function GetSearchLinkForTerm($sTerm)
    {
        $aParams = array(TShopModuleArticlelistFilterSearch::PARAM_QUERY => $sTerm);
        $oShop = TdbShop::GetInstance();
        $sLink = $oShop->GetLinkToSystemPage('search', $aParams);

        return $sLink;
    }

    protected static function GetSearchCacheItem($sKey, $sShopId = null)
    {
        $oItem = null;
        $oItem = TdbShopSearchCache::GetNewInstance();
        $oItem->SetEnableObjectCaching(false);

        $aFindSearchCacheFilter = array(
            "`searchkey` = '".MySqlLegacySupport::getInstance()->real_escape_string($sKey)."'",
        );
        if (null !== $sShopId) {
            $aFindSearchCacheFilter[] = "`shop_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($sShopId)."'";
        }

        $query = 'SELECT * FROM `shop_search_cache`
                   WHERE '.implode(' AND ', $aFindSearchCacheFilter).'
                 ';

        $iMaxNumberOfAttempts = 50;
        $bDone = false;
        do {
            if ($aItem = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
                /** @var $oItem TdbShopSearchCache */
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
     * @param string $sKey   - key identifying the search
     * @param string $sQuery
     *
     * @return TdbShopSearchCache
     */
    public static function &CreateSearchCache($sKey, $sQuery, $sSearchTerm, $aSearchTerms = null, $aFilter = array())
    {
        $sShopId = null;
        $oShop = TdbShop::GetInstance();
        if ($oShop) {
            $sShopId = $oShop->id;
        }
        $oItem = self::GetSearchCacheItem($sKey, $sShopId);

        if (null === $oItem) {
            $oItem = TdbShopSearchCache::GetNewInstance();
            $aData = array('shop_id' => $oShop->id, 'searchkey' => $sKey, 'last_used_date' => date('Y-m-d H:i:s'), 'number_of_records_found' => -1);
            $oItem->LoadFromRow($aData);
            $oItem->AllowEditByAll(true);
            $oItem->Save();
            //die(0);
            $sTmpQuery = "SELECT MD5(CONCAT('".MySqlLegacySupport::getInstance()->real_escape_string($oItem->id)."-',tmpres.shop_article_id)) AS shop_search_cache_item_id,
        '".MySqlLegacySupport::getInstance()->real_escape_string($oItem->id)."' AS shop_search_cache_id, sum(tmpres.cms_search_weight) As cms_search_weight, tmpres.shop_article_id AS id from (".$sQuery.') AS tmpres group by tmpres.shop_article_id';
            if (TdbShopSearchIndexer::searchWithAND()) {
                $aTerms = TdbShopSearchIndexer::PrepareSearchWords($sSearchTerm, true);
                $sTmpQuery .= ' HAVING SUM(tmpres.wordhit) >= '.count($aTerms);
            }
            $sTmpQuery = $oItem->ProcessSearchQueryBeforeInsert($sTmpQuery);

            $query = "INSERT INTO `shop_search_cache_item` (`id`,`shop_search_cache_id`,`weight`, `shop_article_id`) {$sTmpQuery}";
            MySqlLegacySupport::getInstance()->query($query);
            $oItem->sqlData['number_of_records_found'] = MySqlLegacySupport::getInstance()->affected_rows();
            $oItem->fieldNumberOfRecordsFound = $oItem->sqlData['number_of_records_found'];
            $oItem->AllowEditByAll(true);
            $oItem->SaveFieldsFast(array('number_of_records_found' => $oItem->fieldNumberOfRecordsFound));
        } else {
            $aData = $oItem->sqlData;
            $aData['last_used_date'] = date('Y-m-d H:i:s');
            $oItem->LoadFromRow($aData);
            $oItem->AllowEditByAll(true);
            $oItem->SaveFieldsFast(array('last_used_date' => $aData['last_used_date']));
        }

        $oItem->sSearchTerm = $sSearchTerm;
        if (true === CMS_SHOP_SEARCH_ENABLE_SPELLCHECKER) {
            $oSpellCheck = &TCMSSpellcheck::GetInstance();
            $oSearchCacheObject = TdbShopSearchCache::GetNewInstance();
            $aCorrections = false;
            if (!empty($sSearchTerm)) {
                $aCorrections = $oSpellCheck->SuggestCorrection($oItem->sSearchTerm, array($oSearchCacheObject, 'GetBestSuggestion'));
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
     * @param $sQuery
     *
     * @return mixed
     */
    public function ProcessSearchQueryBeforeInsert($sQuery)
    {
        return $sQuery;
    }

    public function GetBestSuggestion($aWords)
    {
        $sBestWord = '';
        reset($aWords);

        while (empty($sBestWord) && ($sWord = current($aWords))) {
            $sEscapedWord = TShopSearchIndexer::PrepareSearchWord($sWord);
            if (!empty($sEscapedWord)) {
                $sIndexTable = TShopSearchIndexer::GetIndexTableNameForIndexLength(mb_strlen($sEscapedWord));
                $query = 'SELECT * FROM `'.MySqlLegacySupport::getInstance()->real_escape_string($sIndexTable)."` WHERE `substring` = '".MySqlLegacySupport::getInstance()->real_escape_string($sEscapedWord)."'";
                $tRes = MySqlLegacySupport::getInstance()->query($query);
                if (MySqlLegacySupport::getInstance()->num_rows($tRes) > 0) {
                    $sBestWord = $sWord;
                }
            }
            next($aWords);
        }
        if (empty($sBestWord)) {
            $sBestWord = $aWords[0];
        }

        return $sBestWord;
    }

    /**
     * return array with categorie ids and number of hits for that category.
     *
     * @return array
     */
    public function GetSearchResultCategoryHits($sFilters = '')
    {
        $aHits = array();
        if (!empty($this->fieldCategoryHits) && empty($sFilters)) {
            $aHits = unserialize($this->fieldCategoryHits);
        } else {
            $aCategoryCount = array();
            $sRestrcition = '';

            $query = '';
            if (!empty($sFilters)) {
                $query = "SELECT COUNT(DISTINCT `shop_search_cache_item`.`id`) AS shop_search_cache_item_count, `shop_category`.*
                      FROM `shop_search_cache_item`
                INNER JOIN `shop_article` ON `shop_search_cache_item`.`shop_article_id` = `shop_article`.`id`
                INNER JOIN `shop_article_shop_category_mlt` ON `shop_search_cache_item`.`shop_article_id` = `shop_article_shop_category_mlt`.`source_id`
                INNER JOIN `shop_category` ON `shop_article_shop_category_mlt`.`target_id` = `shop_category`.`id`
                     WHERE `shop_search_cache_item`.`shop_search_cache_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'
                       AND {$sFilters}
                  GROUP BY `shop_category`.`id`
                   ";
            } else {
                $query = "SELECT COUNT(DISTINCT `shop_search_cache_item`.`id`) AS shop_search_cache_item_count, `shop_category`.*
                      FROM `shop_search_cache_item`
                INNER JOIN `shop_article_shop_category_mlt` ON `shop_search_cache_item`.`shop_article_id` = `shop_article_shop_category_mlt`.`source_id`
                INNER JOIN `shop_category` ON `shop_article_shop_category_mlt`.`target_id` = `shop_category`.`id`
                     WHERE `shop_search_cache_item`.`shop_search_cache_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'
                  GROUP BY `shop_category`.`id`
                   ";
            }
            $oCatList = &TdbShopCategoryList::GetList($query);

            // now organize list into a tree... we trace back each node untill we reach a root node... in the end we
            // will have a collection of rows. then we merge these

            while ($oCat = &$oCatList->Next()) {
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
        if ($this->fieldNumberOfRecordsFound < 0) {
            $query = "SELECT COUNT(*) AS recs FROM `shop_search_cache_item` WHERE `shop_search_cache_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'";
            if ($row = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
                $this->fieldNumberOfRecordsFound = $row['recs'];
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
     */
    public function ClearCache()
    {
        $query = "DELETE FROM `shop_search_cache_item` WHERE `shop_search_cache_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."' ";
        MySqlLegacySupport::getInstance()->query($query);
        $query = "DELETE FROM `shop_search_cache` WHERE `id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'";
        MySqlLegacySupport::getInstance()->query($query);
        $this->id = null;
        $this->sqlData['id'] = null;
    }

    public static function ClearCompleteCache()
    {
        $query = 'TRUNCATE `shop_search_cache_item`';
        MySqlLegacySupport::getInstance()->query($query);
        $query = 'TRUNCATE `shop_search_cache`';
        MySqlLegacySupport::getInstance()->query($query);
    }
}
