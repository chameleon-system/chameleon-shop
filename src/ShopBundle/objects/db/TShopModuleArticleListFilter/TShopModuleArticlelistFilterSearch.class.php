<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopModuleArticlelistFilterSearch extends TdbShopModuleArticleListFilter
{
    const PARAM_QUERY = 'q';
    const SESSION_NAME_QUERY_CALL = 'TShopModuleArticlelistFilterSearchQUERYCALL';
    const URL_FILTER = 'lf';

    /**
     * @var bool
     */
    private $hasSearch = false;

    /**
     * prevent the use of the parent object when this filter finds not articles.
     *
     * @return bool
     */
    public function PreventUseOfParentObjectWhenNoRecordsAreFound()
    {
        return true;
    }

    /**
     * @return bool
     */
    protected function getHasSearch()
    {
        return $this->hasSearch;
    }

    /**
     * return the base of the query. overwrite this method for each filter to add custom filtering
     * the method should include a query of the form select shop_article.*,... FROM shop_article ... WHERE ...
     * NOTE: do not add order info or limit the query (overwrite GetListQueryOrderBy and GetListQueryLimit instead)
     * NOTE 2: the query will automatically be restricted to all active articles.
     * NOTE 3: you query must include a where statement.
     *
     * we overwrite the method here, to take the search terms from POST/GET. it is possible to pass one query string,
     * or an array of query strings. in the later case, you need to send the data in the form PARAM_QUERY[shop_search_field_weight_id]=value
     * if you want to combine the two, you need to pass the general query in PARAM_QUERY[0]=value
     *
     * @param TdbShopModuleArticleList $oListConfig
     *
     * @return string
     */
    protected function GetListQueryBase(&$oListConfig)
    {
        $sQueryString = '';
        $aQueryStrings = '';
        $aFilter = array();
        $this->GetQuerySearchPostParameters($sQueryString, $aQueryStrings, $aFilter);
        if ('' === $sQueryString && null === $aQueryStrings && 0 === count($aFilter)) {
            $this->hasSearch = false;
        } else {
            $this->hasSearch = true;
        }
        $sQuery = TdbShopSearchIndexer::GetSearchQuery($sQueryString, $aQueryStrings, $aFilter);

        $sFilter = TdbShop::GetActiveFilterString();
        if (!empty($sFilter)) {
            $sQuery .= " AND {$sFilter}";
        }

        return $sQuery;
    }

    /**
     * fetch the query string parameters based on the get/post data.
     *
     * @param string $sQueryString  - the query string that is searched for in all fields
     * @param mixed  $aQueryStrings - query strings that search only specific fields
     * @param array  $aFilter       - any additional filters (such as manufacturer)
     *
     * @return void
     */
    protected function GetQuerySearchPostParameters(&$sQueryString, &$aQueryStrings, &$aFilter)
    {
        $oGlobal = TGlobal::instance();
        $sQueryString = '';
        $aQueryStrings = null;
        $sRawPost = $oGlobal->GetUserData(self::PARAM_QUERY);
        if (is_array($sRawPost)) {
            if (array_key_exists(0, $sRawPost)) {
                $sQueryString = trim($sRawPost[0]);
                unset($sRawPost[0]);
            }
            $aQueryStrings = $sRawPost;
        } else {
            $sQueryString = trim($sRawPost);
        }

        $aFilter = TdbShop::GetActiveFilter();
    }

    /**
     * returns the order by part of the query.
     *
     * @param TdbShopModuleArticleList $oListConfig
     *
     * @return string
     */
    protected function GetListQueryOrderBy(&$oListConfig)
    {
        $sQuery = parent::GetListQueryOrderBy($oListConfig);
        //      if (empty($sQuery)) $sQuery = "`shop_article`.`list_rank` DESC, `shop_article`.`name`";
        return $sQuery;
    }

    /**
     * define the group by for the query.
     *
     * @param TdbShopModuleArticleList $oListConfig
     *
     * @return string
     */
    protected function GetListQueryGroupBy(&$oListConfig)
    {
        return ''; // 'GROUP BY `shop_article`.`id`';
    }

    /**
     * overwrite this if you want to prevent the list from caching this filter result.
     *
     * @return bool
     */
    public function _AllowCache()
    {
        return false;
    }

    /**
     * is called when the module initializes.
     *
     * @return void
     */
    public function ModuleInitHook()
    {
        parent::ModuleInitHook();
        $oShop = TdbShop::GetInstance();
        if ($oShop->fieldRedirectToNotFoundPageProductSearchOnNoResults) {
            /**
             * in this case the shop may redirect after running the search. since this occurs in the Execute of the
             * module, we need to prevent any other output from being auto-sent to the browser
             * @psalm-suppress UndefinedInterfaceMethod
             * @FIXME Method `SetBlockAutoFlushToBrowser` only exist on a single implementation of the interface
             */
            TGlobal::GetController()->SetBlockAutoFlushToBrowser(true);
        }
    }
}
