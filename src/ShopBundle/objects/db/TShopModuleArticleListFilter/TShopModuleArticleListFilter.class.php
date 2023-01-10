<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopModuleArticleListFilter extends TShopModuleArticleListFilterAutoParent
{
    /**
     * set to true if the base query was generated instead of a custom query from an extension.
     *
     * @var bool
     */
    protected $bUsedBaseQuery = false;

    /**
     * set to false if you want to prevent the list from caching.
     *
     * @var bool
     */
    public $bAllowCache = true;

    /**
     * @var array<string, mixed>
     */
    private static $cache = [];

    /**
     * factory creates a new instance and returns it.
     *
     * @param string|array $sData     - either the id of the object to load, or the row with which the instance should be initialized
     * @param string       $sLanguage - init with the language passed
     *
     * @return TdbShopModuleArticleListFilter     */
    public static function GetNewInstance($sData = null, $sLanguage = null): TdbShopModuleArticleListFilter
    {
        $canBeCached = false;
        $cacheKey = null;
        if (false === is_array($sData)) {
            $canBeCached = true;
            $cacheKey = "$sData|$sLanguage";
            if (isset(self::$cache[$cacheKey])) {
                return clone self::$cache[$cacheKey];
            }
        }
        $oObject = parent:: GetNewInstance($sData, $sLanguage);
        if ($oObject && false !== $oObject->sqlData && isset($oObject->sqlData['class']) && !empty($oObject->sqlData['class'])) {
            $sClassName = $oObject->sqlData['class'];
            $oNewObject = new $sClassName();
            $oNewObject->LoadFromRow($oObject->sqlData);

            if ($canBeCached) {
                /**
                 * @psalm-suppress InvalidPropertyAssignmentValue - through `$canBeCached` we know that at this place, `$cacheKey` is not `null`
                 */
                self::$cache[$cacheKey] = $oNewObject;
            }

            return $oNewObject;
        }

        return $oObject;
    }

    /**
     * is called when the module initializes.
     *
     * @return void
     */
    public function ModuleInitHook()
    {
    }

    /**
     * prevent the use of the parent object when this filter finds not articles.
     *
     * @return bool
     */
    public function PreventUseOfParentObjectWhenNoRecordsAreFound()
    {
        return false;
    }

    /**
     * optional allows you to specify a list filter, that will be used instead, if this list filter has 0 matches.
     * if set, then this will overwrite, returns the parent as default
     * note: this will only be called if PreventUseOfParentObjectWhenNoRecordsAreFound returns false.
     *
     * @return TdbShopModuleArticleListFilter|null
     */
    public function getFallbackListFilter()
    {
        if ('tdbshopmodulearticlelistfilter' === strtolower(get_class($this))) {
            return null;
        }
        $sParentListClass = strtolower(get_parent_class($this));
        if ('tdbshopmodulearticlelistfilter' == $sParentListClass || 'tshopmodulearticlelistfilter' == $sParentListClass) {
            return null;
        }
        $aFilterData = $this->sqlData;
        /** @var TdbShopModuleArticleListFilter $oFilterObject */
        $oFilterObject = new $sParentListClass();
        $oFilterObject->LoadFromRow($aFilterData);

        return $oFilterObject;
    }

    /**
     * return the query used to select the records.
     * Note: the query should include order by, and a limit on the number of records in total, but no paging data
     * Note2: you should NOT overwrite this method. overwrite GetListQueryBase, GetListQueryOrderBy, and GetListQueryLimit instead.
     *
     * @param TdbShopModuleArticleList $oListConfig
     *
     * @return string
     */
    public function GetListQuery($oListConfig)
    {
        $sQuery = $this->GetListQueryBase($oListConfig);
        $aRestrictions = $this->GetGlobalQueryRestrictions($oListConfig);
        if (count($aRestrictions) > 0) {
            $sQuery .= ' AND (('.implode(') AND (', $aRestrictions).'))';
        }
        $sQuery .= ' '.$this->GetListQueryGroupBy($oListConfig);
        $sOrderBy = $this->GetListQueryOrderBy($oListConfig);
        if (!empty($sOrderBy)) {
            $sQuery .= ' ORDER BY '.$sOrderBy;
        }
        $sQuery .= ' '.$this->GetListQueryLimit($oListConfig);
        //echo '[ '.$sQuery.' ]';
        return $sQuery;
    }

    /**
     * query restrictions added to the list.
     *
     * @param TdbShopModuleArticleList $oListConfig
     *
     * @return string[]
     */
    protected function GetGlobalQueryRestrictions($oListConfig)
    {
        $aRestrictions = array();
        $bRestrictToParentArticles = (false == $this->AllowArticleVariants());
        $aRestrictions[] = TdbShopArticleList::GetActiveArticleQueryRestriction($bRestrictToParentArticles);

        return $aRestrictions;
    }

    /**
     * return true if you want to include article variants in the result set
     * false if you only want parent-articles.
     *
     * @return bool
     */
    protected function AllowArticleVariants()
    {
        return false;
    }

    /**
     * return the base of the query. overwrite this method for each filter to add custom filtering
     * the method should include a query of the form select shop_article.*,... FROM shop_article ... WHERE ...
     * NOTE: do not add order info or limit the query (overwrite GetListQueryOrderBy and GetListQueryLimit instead)
     * NOTE 2: the query will automatically be restricted to all active articles.
     * NOTE 3: you query must include a where statement.
     *
     * @param TdbShopModuleArticleList $oListConfig
     *
     * @return string
     */
    protected function GetListQueryBase($oListConfig)
    {
        $this->bUsedBaseQuery = true;
        $sQuery = $this->GetListBaseQueryRestrictedToCategories($oListConfig);

        return $sQuery;
    }

    /**
     * return a query for all manually assigned articles. this list can be restricted to a list of categories.
     *
     * @param TdbShopModuleArticleList $oListConfig
     * @param array                    $aCategoryList
     *
     * @return string
     */
    protected function GetListBaseQueryRestrictedToCategories($oListConfig, $aCategoryList = null)
    {
        $aCustRestriction = array();

        // get category products
        $categories = $oListConfig->GetMLTIdList('shop_category_mlt');

        $databaseConnection = $this->getDatabaseConnection();
        $quotedListConfigId = $databaseConnection->quote($oListConfig->id);
        $quotedCategories = implode(',', array_map(array($databaseConnection, 'quote'), $categories));

        $sQuery = "SELECT DISTINCT `shop_module_article_list_article`.`name` AS conf_alternativ_header, (-1*`shop_module_article_list_article`.`position`) AS cms_search_weight, `shop_article`.*
                 FROM `shop_article`
            LEFT JOIN `shop_article_shop_category_mlt` ON `shop_article`.`id` = `shop_article_shop_category_mlt`.`source_id`
            LEFT JOIN `shop_article_stats` ON `shop_article`.`id` = `shop_article_stats`.`shop_article_id`
            LEFT JOIN `shop_article_stock` ON `shop_article`.`id` = `shop_article_stock`.`shop_article_id`
            LEFT JOIN `shop_module_article_list_article` ON (`shop_module_article_list_article`.`shop_module_article_list_id` = $quotedListConfigId AND `shop_article`.`id` = `shop_module_article_list_article`.`shop_article_id`)
              ";
        if (count($categories) > 0) {
            $aCustRestriction[] = "`shop_article_shop_category_mlt`.`target_id` IN ($quotedCategories)";
        }

        $manuallySelectedArticles = $oListConfig->GetFieldShopModuleArticleListArticleList();
        if ($manuallySelectedArticles->Length() > 0) {
            $aCustRestriction[] = "`shop_module_article_list_article`.`shop_module_article_list_id` = $quotedListConfigId";
        }

        // get warengruppen
        $productGroupRestriction = $oListConfig->GetMLTIdList('shop_article_group_mlt');
        if (count($productGroupRestriction) > 0) {
            $sQuery .= ' LEFT JOIN `shop_article_shop_article_group_mlt` ON `shop_article`.`id` = `shop_article_shop_article_group_mlt`.`source_id` ';
            $productGroupRestriction = implode(',', array_map(array($databaseConnection, 'quote'), $productGroupRestriction));
            $aCustRestriction[] = "`shop_article_shop_article_group_mlt`.`target_id` IN ($productGroupRestriction)";
        }

        $sCustQuery = 'WHERE 1=0';
        if (count($aCustRestriction) > 0) {
            $sCustQuery = 'WHERE ('.implode("\nOR ", $aCustRestriction).')';
        }
        if (null !== $aCategoryList) {
            $escapedCategoryList = implode(',', array_map(array($databaseConnection, 'quote'), $aCategoryList));
            $sCustQuery .= " AND (`shop_article_shop_category_mlt`.`target_id` IN ($escapedCategoryList))";
        }
        $sQuery .= $sCustQuery;

        return $sQuery;
    }

    /**
     * define the group by for the query.
     *
     * @param TdbShopModuleArticleList $oListConfig
     *
     * @return string
     */
    protected function GetListQueryGroupBy($oListConfig)
    {
        return ''; // 'GROUP BY `shop_article`.`id`';
    }

    /**
     * returns the order by part of the query.
     *
     * @param TdbShopModuleArticleList $oListConfig
     *
     * @return string
     */
    protected function GetListQueryOrderBy($oListConfig)
    {
        $sQuery = '';
        $oOrder = $oListConfig->GetFieldShopModuleArticlelistOrderby();
        if (!is_null($oOrder)) {
            $sQuery = $oOrder->GetOrderByString();
        } else {
            // add default order by for manual lists
            $oFilter = $oListConfig->GetFieldShopModuleArticleListFilter();
            if (null !== $oFilter && 'TdbShopModuleArticleListFilter' === \get_class($oFilter)) {
                if (!empty($sQuery)) {
                    $sQuery = ', '.$sQuery;
                }
                $sQuery = 'cms_search_weight desc'.$sQuery;
            }
        }

        return $sQuery;
    }

    /**
     * add the limit to the query.
     *
     * @param TdbShopModuleArticleList $oListConfig
     *
     * @return string
     */
    protected function GetListQueryLimit($oListConfig)
    {
        if ($oListConfig->fieldNumberOfArticles > 0) {
            return 'LIMIT 0,'.MySqlLegacySupport::getInstance()->real_escape_string($oListConfig->fieldNumberOfArticles);
        } else {
            return '';
        }
    }

    /**
     * overwrite this if you want to prevent the list from caching this filter result.
     *
     * @return bool
     */
    public function _AllowCache()
    {
        return $this->bAllowCache;
    }

    /**
     * return any cache relevant parameters to the list class here.
     *
     * @return array
     */
    public function _GetCacheParameters()
    {
        $aParams = array();
        $oShop = TdbShop::GetInstance();

        $oActiveCategory = $oShop->GetActiveCategory();
        if (!is_null($oActiveCategory)) {
            $aParams['activecategoryid'] = $oActiveCategory->id;
        }

        return $aParams;
    }

    /**
     * return any for this list relevant clear cache triggers:
     * if the content that is to be cached comes from the database (as ist most often the case)
     * then this function should return an array of assoc arrays that point to the
     * tables and records that are associated with the content. one table entry has
     * two fields:
     *   - table - the name of the table
     *   - id    - the record in question. if this is empty, then any record change in that
     *             table will result in a cache clear.
     *
     * @return array
     */
    public function _GetCacheTableInfos()
    {
        $aClearCacheInfo = array();

        return $aClearCacheInfo;
    }
}
