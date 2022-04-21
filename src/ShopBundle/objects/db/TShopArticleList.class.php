<?php

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopArticleList extends TShopArticleListAutoParent
{
    const VIEW_PATH = '/pkgShop/views/db/TShopArticleList';
    const SESSIN_DUMP_NAME = 'esshoparticlelists';
    const URL_LIST_KEY_NAME = 'listkey';
    const URL_LIST_REQUEST = 'listrequest';
    const URL_LIST_CURRENT_PAGE = 'listpage';

    /**
     * identifies the list object with a module spot.
     *
     * @var string
     */
    protected $sListIdentKey = null;

    /**
     * returns a subquery that can be used to reduce a query set to only active articles.
     *
     * @param bool $bRestrictToVariantParentArticles
     *
     * @return string
     */
    public static function GetActiveArticleQueryRestriction($bRestrictToVariantParentArticles = true)
    {
        $sVariantParentRestriction = '';
        if ($bRestrictToVariantParentArticles) {
            $sVariantParentRestriction = " AND `shop_article`.`variant_parent_id` = '' ";
        }
        $sQuery = "`shop_article`.`active` = '1' AND `shop_article`.`variant_parent_is_active` = '1' {$sVariantParentRestriction} AND `shop_article`.`virtual_article` = '0' AND `shop_article`.`is_searchable` = '1'";

        return $sQuery;
    }

    /**
     * return active article list for the given category id.
     *
     * @param int    $iCategoryId                        - this may alos be an ARRAY! in that case all articles from any of the listed categories IDs will be returned
     * @param string $sOrderString
     * @param int    $iLimit
     * @param array  $aFilter                            - any filters you want to add to the list
     *
     * @return TdbShopArticleList
     */
    public static function &LoadCategoryArticleList($iCategoryId, $sOrderString = null, $iLimit = -1, $aFilter = array())
    {
        return TdbShopArticleList::LoadCategoryArticleListForCategoryList(array($iCategoryId), $sOrderString, $iLimit, $aFilter);
    }

    /**
     * return active article list for the given category id.
     *
     * @param array  $aCategoryIdList                    - an array of categories
     * @param string $sOrderString
     * @param int    $iLimit
     * @param array  $aFilter                            - any filters you want to add to the list
     *
     * @return TdbShopArticleList
     */
    public static function &LoadCategoryArticleListForCategoryList($aCategoryIdList, $sOrderString = null, $iLimit = -1, $aFilter = array(), $sCustomBaseQuery = null)
    {
        if (is_null($sOrderString)) {
            $sOrderString = '`shop_article`.`list_rank` DESC, `shop_article`.`name` ASC';
        }

        if (is_null($sCustomBaseQuery)) {
            // see comment at end of method to find out why we select id or * depending on presence of order by
            $sQueryFieldsToSelect = '`shop_article`.`id`';
            if (empty($sOrderString)) {
                $sQueryFieldsToSelect = '`shop_article`.*';
            }
            $query = "SELECT DISTINCT {$sQueryFieldsToSelect}
                  FROM `shop_article`
            INNER JOIN `shop_article_shop_category_mlt` ON `shop_article`.`id` = `shop_article_shop_category_mlt`.`source_id`
                 WHERE
               ";
        } else {
            $query = $sCustomBaseQuery;
        }

        //            INNER JOIN `shop_article_shop_category_mlt` ON `shop_article`.`id` = `shop_article_shop_category_mlt`.`source_id`

        $sCatRestriction = '';
        if (is_array($aCategoryIdList)) {
            $aCategoryIdList = TTools::MysqlRealEscapeArray($aCategoryIdList);
            $sCatRestriction = " `shop_article_shop_category_mlt`.`target_id` IN ('".implode("','", $aCategoryIdList)."') ";
        } else {
            $sCatRestriction = " `shop_article_shop_category_mlt`.`target_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($aCategoryIdList)."' ";
        }
        if (!empty($sCatRestriction)) {
            //$query .= " `shop_article`.`id` IN (SELECT `shop_article_shop_category_mlt`.`source_id` FROM `shop_article_shop_category_mlt` WHERE {$sCatRestriction}) ";
            $query .= $sCatRestriction;
        } else {
            $query .= ' 1 ';
        }
        //      $query .= $sCatRestriction;

        $sActiveArticleSnippid = TdbShopArticleList::GetActiveArticleQueryRestriction();
        if (!empty($sActiveArticleSnippid)) {
            $query .= ' AND ('.$sActiveArticleSnippid.')';
        }
        if (count($aFilter) > 0) {
            $aTmpFilter = array();
            foreach ($aFilter as $sKey => $sField) {
                $aTmpFilter[] = MySqlLegacySupport::getInstance()->real_escape_string($sKey)."='".MySqlLegacySupport::getInstance()->real_escape_string($sField)."'";
            }
            $query .= ' AND ('.implode(' AND ', $aTmpFilter).')';
        }

        //      die($query);
        //

        $sRestrictions = '';
        if (!empty($sOrderString)) {
            $sRestrictions .= ' ORDER BY '.$sOrderString;
        }

        if ($iLimit > 0) {
            $sRestrictions .= ' LIMIT 0,'.$iLimit;
        }

        $sFullQuery = $query.$sRestrictions;

        /**
         * for some reason, ordering the subquery with a select on only id and then joining that with the full article table is a) super fast and b) results in the correct order
         * while select all records right away on the order query is extremely slow (factor 10).
         */
        if (!empty($sOrderString)) {
            $sFullQuery = "SELECT `shop_article`.*
                         FROM `shop_article`
                   INNER JOIN ({$sFullQuery}) AS X ON `shop_article`.`id` = X.`id`";
        }

        /*      $sFullQuery = "SELECT `shop_article`.*
        FROM `shop_article`
       WHERE `shop_article`.`id` IN (
        {$query}
       ) {$sRestrictions}";*/
        $oList = &TdbShopArticleList::GetList($sFullQuery, null, false);

        return $oList;
    }

    /**
     * return active article list.
     *
     * @param int    $iCategoryId
     * @param string $sOrderString
     * @param int    $iLimit
     * @param array  $aFilter      - any filters you want to add to the list
     *
     * @return TdbShopArticleList
     */
    public static function LoadArticleList($sOrderString = null, $iLimit = -1, $aFilter = array())
    {
        if (is_null($sOrderString)) {
            $sOrderString = '`shop_article`.`list_rank` DESC, `shop_article`.`name` ASC';
        }
        $query = "SELECT `shop_article`.*
                  FROM `shop_article`
                 WHERE `shop_article`.`variant_parent_id` = ''
               ";
        $sActiveArticleSnippid = TdbShopArticleList::GetActiveArticleQueryRestriction();
        if (!empty($sActiveArticleSnippid)) {
            $query .= ' AND ('.$sActiveArticleSnippid.')';
        }
        if (count($aFilter) > 0) {
            $aTmpFilter = array();
            foreach ($aFilter as $sKey => $sField) {
                $aTmpFilter[] = MySqlLegacySupport::getInstance()->real_escape_string($sKey)."='".MySqlLegacySupport::getInstance()->real_escape_string($sField)."'";
            }
            $query .= ' AND ('.implode(' AND ', $aTmpFilter).')';
        }
        if (!empty($sOrderString)) {
            $query .= ' ORDER BY '.$sOrderString;
        }

        if ($iLimit > 0) {
            $query .= ' LIMIT 0,'.$iLimit;
        }

        $oList = &TdbShopArticleList::GetList($query);

        return $oList;
    }

    /**
     * used to display the article list.
     *
     * @param string $sViewName     - the view to use
     * @param string $sViewType     - where the view is located (Core, Custom-Core, Customer)
     * @param array  $aCallTimeVars - place any custom vars that you want to pass through the call here
     *
     * @return string
     */
    public function Render($sViewName = 'standard', $sViewType = 'Core', $aCallTimeVars = array())
    {
        $oView = new TViewParser();
        $sViewIdentKey = $this->StoreListObjectInSession($sViewName, $sViewType, $aCallTimeVars);
        $this->GoToStart();
        $oView->AddVar('oArticleList', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $oView->AddVar('sViewIdentKey', $sViewIdentKey);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, TdbShopArticleList::VIEW_PATH, $sViewType);
    }

    /**
     * return an IdentKey for this list.
     *
     * @return string
     */
    public function GetListIdentKey()
    {
        if (null === $this->sListIdentKey) {
            $oGlobal = TGlobal::instance();
            $oExecutingModule = &$oGlobal->GetExecutingModulePointer();
            $this->sListIdentKey = $oExecutingModule->sModuleSpotName;
        }

        return $this->sListIdentKey;
    }

    /**
     * restore serialized dump in session. returns id key for item. also calls
     * session cleanup method.
     *
     * @return string
     */
    public function StoreListObjectInSession($sViewName = null, $sViewType = null, $aCallTimeVars = null)
    {
        $oGlobal = TGlobal::instance();
        $sItemKey = $this->GetListIdentKey();
        $oExecutingModule = &$oGlobal->GetExecutingModulePointer();

        if (!array_key_exists(TdbShopArticleList::SESSIN_DUMP_NAME, $_SESSION)) {
            $_SESSION[TdbShopArticleList::SESSIN_DUMP_NAME] = array();
        }
        if (!array_key_exists($sItemKey, $_SESSION[TdbShopArticleList::SESSIN_DUMP_NAME])) {
            $_SESSION[TdbShopArticleList::SESSIN_DUMP_NAME][$sItemKey] = array();
        }
        $aOldData = $_SESSION[TdbShopArticleList::SESSIN_DUMP_NAME][$sItemKey];
        if (is_null($sViewName) && array_key_exists('sViewName', $aOldData)) {
            $sViewName = $aOldData['sViewName'];
        }
        if (is_null($sViewType) && array_key_exists('sViewType', $aOldData)) {
            $sViewType = $aOldData['sViewType'];
        }
        if (is_null($aCallTimeVars) && array_key_exists('aCallTimeVars', $aOldData)) {
            $aCallTimeVars = $aOldData['aCallTimeVars'];
        }

        if (0 === $this->GetPageSize()) {
            unset($_SESSION[TdbShopArticleList::SESSIN_DUMP_NAME][$sItemKey]);
        } else {
            $_SESSION[TdbShopArticleList::SESSIN_DUMP_NAME][$sItemKey] = array( //        'oExecutingModule'=>$oExecutingModule,
                'lastchanged' => time(), 'sObjectDump' => base64_encode(serialize($this)), 'iStartRecord' => $this->GetStartRecordNumber(), 'iPageSize' => $this->GetPageSize(), 'sViewName' => $sViewName, 'sViewType' => $sViewType, 'aCallTimeVars' => $aCallTimeVars, );
        }

        return $sItemKey;
    }

    /**
     * return list data for key stored in session. returns NULL if no item or no session is found
     * note: the item will be removed from session.
     *
     * @param string $sListSessionKey
     *
     * @return array|null
     */
    public static function GetInstanceDataFromSession($sListSessionKey)
    {
        if (!array_key_exists(TdbShopArticleList::SESSIN_DUMP_NAME, $_SESSION)) {
            $_SESSION[TdbShopArticleList::SESSIN_DUMP_NAME] = array();
        }
        $aList = null;
        if (array_key_exists($sListSessionKey, $_SESSION[TdbShopArticleList::SESSIN_DUMP_NAME])) {
            $aList = $_SESSION[TdbShopArticleList::SESSIN_DUMP_NAME][$sListSessionKey];
        }

        return $aList;
    }

    /**
     * create a list based on the session data for the session key passed
     * returns NULL if the object can not be found.
     *
     * @param string $sListSessionKey
     *
     * @return TdbShopArticleList|null
     */
    public static function &GetInstanceFromSession($sListSessionKey)
    {
        $oList = null;
        $aListData = TdbShopArticleList::GetInstanceDataFromSession($sListSessionKey);
        if (!is_null($aListData)) {
            if (array_key_exists('sObjectDump', $aListData)) {
                $oList = unserialize(base64_decode($aListData['sObjectDump']));
                $oGlobal = TGlobal::instance();
                if ($oGlobal->UserDataExists(TdbShopArticleList::URL_LIST_KEY_NAME)) {
                    $callingIdentKey = $oGlobal->GetUserData(TdbShopArticleList::URL_LIST_KEY_NAME);
                    if ($sListSessionKey == $callingIdentKey) {
                        $sRequest = $oGlobal->GetUserData(TdbShopArticleList::URL_LIST_REQUEST);
                        $oList->HandleURLRequest($sRequest);
                    }
                }
            }
        }

        return $oList;
    }

    /**
     * remove serialized dump from session.
     *
     * @param string $sListSessionKey
     * @return void
     */
    public static function removeInstanceFromSession($sListSessionKey)
    {
        if (isset($_SESSION[TdbShopArticleList::SESSIN_DUMP_NAME]) &&
            isset($_SESSION[TdbShopArticleList::SESSIN_DUMP_NAME][$sListSessionKey])) {
            unset($_SESSION[TdbShopArticleList::SESSIN_DUMP_NAME][$sListSessionKey]);
        }
    }

    /**
     * use this method to add any variables to the render method that you may
     * require for some view.
     *
     * @param string $sViewName - the view being requested
     * @param string $sViewType - the location of the view (Core, Custom-Core, Customer)
     *
     * @return array
     */
    protected function GetAdditionalViewVariables($sViewName, $sViewType)
    {
        $aViewVariables = array();

        return $aViewVariables;
    }

    /**
     * set the paging info from session.. if no value is set in session, use the passed values.
     *
     * @param int $iStartRecord
     * @param int $iPageSize
     */
    public function RestorePagingInfoFromSession($iStartRecord, $iPageSize)
    {
        $identKey = $this->GetListIdentKey();
        $aTmpData = TdbShopArticleList::GetInstanceDataFromSession($identKey);
        $bPagingWasRestoredFromSession = false;
        if (!is_null($aTmpData)) {
            $bPagingWasRestoredFromSession = $this->SetPagingInfo($aTmpData['iStartRecord'], $aTmpData['iPageSize']);
        }
        if (false === $bPagingWasRestoredFromSession) {
            // use default... BUT if there is a post request for this list (ie list key given) and a page
            // is transfered, we use it
            $oGlobal = TGlobal::instance();
            $sRequestKey = $oGlobal->GetUserData(TdbShopArticleList::URL_LIST_KEY_NAME);
            if ($sRequestKey == $identKey) {
                $iRequestStartPage = $oGlobal->GetUserData(TdbShopArticleList::URL_LIST_CURRENT_PAGE);
                $iRequestStartPage = intval($iRequestStartPage);
                if ($iRequestStartPage > 0) {
                    $iStartRecord = $iPageSize * ($iRequestStartPage - 1);
                }
                $this->SetPagingInfo($iStartRecord, $iPageSize);
                $sRequest = $oGlobal->GetUserData(TdbShopArticleList::URL_LIST_REQUEST);
                $this->HandleURLRequest($sRequest);
            } else {
                if (false === $this->SetPagingInfo($iStartRecord, $iPageSize)) {
                    $this->SetPagingInfo(0, $iPageSize);
                }
            }
        }
    }

    /**
     * return link to next page, or false if there is no next page.
     *
     * @return string|false
     */
    public function GetNextPageLink()
    {
        $sLink = false;
        if ($this->HasNextPage()) {
            $oGlobal = TGlobal::instance();
            $oExecutingModule = &$oGlobal->GetExecutingModulePointer();
            $aAdditionalParameters = array('module_fnc['.$oExecutingModule->sModuleSpotName.']' => 'ChangePage', TdbShopArticleList::URL_LIST_KEY_NAME => $this->GetListIdentKey(), TdbShopArticleList::URL_LIST_REQUEST => 'NextPage', TdbShopArticleList::URL_LIST_CURRENT_PAGE => $this->GetCurrentPageNumber());
            $sLink = $this->getActivePageService()->getLinkToActivePageRelative($aAdditionalParameters, TdbShopArticleList::GetParametersToIgnoreInPageLinks());
        }

        return $sLink;
    }

    /**
     * returns the javascript call to fetch the items for the next page.
     *
     * @param bool $bGetAsJSFunction - set to false if you just want the link
     *
     * @return string|false
     */
    public function GetNextPageLinkAsAJAXCall($bGetAsJSFunction = true)
    {
        $sLink = false;
        if ($this->HasNextPage()) {
            $oGlobal = TGlobal::instance();
            $oExecutingModule = &$oGlobal->GetExecutingModulePointer();
            $aAdditionalParameters = array('module_fnc['.$oExecutingModule->sModuleSpotName.']' => 'ExecuteAjaxCall', '_fnc' => 'ChangePageAjax', TdbShopArticleList::URL_LIST_KEY_NAME => $this->GetListIdentKey(), TdbShopArticleList::URL_LIST_REQUEST => 'NextPage',
            );
            $sLink = $this->getActivePageService()->getLinkToActivePageRelative($aAdditionalParameters, TdbShopArticleList::GetParametersToIgnoreInPageLinks());
            if ($bGetAsJSFunction) {
                $sLink = "GetAjaxCall('{$sLink}', ShowListItems)";
            }
        }

        return $sLink;
    }

    /**
     * returns the javascript call to fetch the items for the next page.
     *
     * @param bool $bGetAsJSFunction - set to false if you just want the link
     *
     * @return string|false
     */
    public function GetPreviousPageLinkAsAJAXCall($bGetAsJSFunction = true)
    {
        $sLink = false;
        if ($this->HasPreviousPage()) {
            $oGlobal = TGlobal::instance();
            $oExecutingModule = &$oGlobal->GetExecutingModulePointer();
            $aAdditionalParameters = array('module_fnc['.$oExecutingModule->sModuleSpotName.']' => 'ExecuteAjaxCall', '_fnc' => 'ChangePageAjax', TdbShopArticleList::URL_LIST_KEY_NAME => $this->GetListIdentKey(), TdbShopArticleList::URL_LIST_REQUEST => 'PreviousPage', TdbShopArticleList::URL_LIST_CURRENT_PAGE => $this->GetCurrentPageNumber() - 2,
            );
            $sLink = $this->getActivePageService()->getLinkToActivePageRelative($aAdditionalParameters, TdbShopArticleList::GetParametersToIgnoreInPageLinks());
            if ($bGetAsJSFunction) {
                $sLink = "GetAjaxCall('{$sLink}', ShowListItems)";
            }
        }

        return $sLink;
    }

    /**
     * return link to previous page, or false if there is no next page.
     *
     * @return string|false
     */
    public function GetPreviousPageLink()
    {
        $sLink = false;
        if ($this->HasPreviousPage()) {
            $oGlobal = TGlobal::instance();
            $oExecutingModule = &$oGlobal->GetExecutingModulePointer();
            $aAdditionalParameters = array('module_fnc['.$oExecutingModule->sModuleSpotName.']' => 'ChangePage', TdbShopArticleList::URL_LIST_KEY_NAME => $this->GetListIdentKey(), TdbShopArticleList::URL_LIST_REQUEST => 'PreviousPage', TdbShopArticleList::URL_LIST_CURRENT_PAGE => ($this->GetCurrentPageNumber() - 2));
            $sLink = $this->getActivePageService()->getLinkToActivePageRelative($aAdditionalParameters, TdbShopArticleList::GetParametersToIgnoreInPageLinks());
        }

        return $sLink;
    }

    /**
     * return javascript call that allows you to jump to the specified page (NOTE: pages start at zero).
     *
     * @param int  $iPageNumber
     * @param bool $bGetAsJSFunction
     *
     * @return string|false
     */
    public function GetPageJumpLinkAsAJAXCall($iPageNumber, $bGetAsJSFunction = true)
    {
        $sLink = false;
        if ($iPageNumber < $this->GetTotalPageCount()) {
            $oGlobal = TGlobal::instance();
            $oExecutingModule = &$oGlobal->GetExecutingModulePointer();
            $aAdditionalParameters = array('module_fnc['.$oExecutingModule->sModuleSpotName.']' => 'ExecuteAjaxCall', '_fnc' => 'ChangePageAjax', TdbShopArticleList::URL_LIST_KEY_NAME => $this->GetListIdentKey(), TdbShopArticleList::URL_LIST_REQUEST => 'GoToPage', TdbShopArticleList::URL_LIST_CURRENT_PAGE => $iPageNumber,
            );
            $sLink = $this->getActivePageService()->getLinkToActivePageRelative($aAdditionalParameters, TdbShopArticleList::GetParametersToIgnoreInPageLinks());
            if ($bGetAsJSFunction) {
                $sLink = "GetAjaxCall('{$sLink}', ShowListItems)";
            }
        }

        return $sLink;
    }

    /**
     * return link that allows you to jump to the specified page (NOTE: pages start at zero).
     *
     * @param int $iPageNumber
     *
     * @return string|false
     */
    public function GetPageJumpLink($iPageNumber)
    {
        $sLink = false;
        if ($iPageNumber < $this->GetTotalPageCount()) {
            $oGlobal = TGlobal::instance();
            $oExecutingModule = &$oGlobal->GetExecutingModulePointer();
            $aAdditionalParameters = array('module_fnc['.$oExecutingModule->sModuleSpotName.']' => 'ChangePage', TdbShopArticleList::URL_LIST_KEY_NAME => $this->GetListIdentKey(), TdbShopArticleList::URL_LIST_REQUEST => 'GoToPage', TdbShopArticleList::URL_LIST_CURRENT_PAGE => $iPageNumber);
            $sLink = $this->getActivePageService()->getLinkToActivePageRelative($aAdditionalParameters, TdbShopArticleList::GetParametersToIgnoreInPageLinks());
        }

        return $sLink;
    }

    /**
     * reset the list (call this when the base query changes to ensure that the new query takes effekt).
     */
    protected function ResetList()
    {
        parent::ResetList();
        $this->sListIdentKey = null;
    }

    /**
     * process any request made for this item.
     *
     * @param string $sRequest
     * @param bool   $bCheckIdentKey
     * @return void
     */
    public function HandleURLRequest($sRequest, $bCheckIdentKey = false)
    {
        $bAllowRequest = true;
        if ($bCheckIdentKey) {
            $currentListKey = $this->GetListIdentKey();
            $oGlobal = TGlobal::instance();
            $callingIdentKey = $oGlobal->GetUserData(TdbShopArticleList::URL_LIST_KEY_NAME);
            if ($callingIdentKey != $currentListKey) {
                $bAllowRequest = false;
            }
        }

        if ($bAllowRequest) {
            switch ($sRequest) {
                case 'NextPage':
                    $this->SetPagingInfoNextPage();
                    break;
                case 'PreviousPage':
                    $this->SetPagingInfoPreviousPage();
                    break;
                case 'GoToPage':
                    $oGlobal = TGlobal::instance();
                    $iRequestStartPage = $oGlobal->GetUserData(TdbShopArticleList::URL_LIST_CURRENT_PAGE);
                    $this->JumpToPage($iRequestStartPage);
                    break;

                default:
                    break;
            }
        }
        $this->StoreListObjectInSession();
    }

    /**
     * list all parameters that you do not want to be included in the page links.
     *
     * @return array
     */
    public static function GetParametersToIgnoreInPageLinks()
    {
        $aParameters = array('module_fnc', TdbShopArticleList::URL_LIST_KEY_NAME, TdbShopArticleList::URL_LIST_REQUEST, TdbShopArticleList::URL_LIST_CURRENT_PAGE);

        return $aParameters;
    }

    /**
     * return an assoc array of cache keys (used by, for example, MTShopArticleCatalog)
     * using this hook we can add different keys suche as brands, portals or there states
     * that may affect the article states.
     *
     * @return array
     */
    public static function GetGlobalArticleListCacheKeyParameters()
    {
        return array();
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
