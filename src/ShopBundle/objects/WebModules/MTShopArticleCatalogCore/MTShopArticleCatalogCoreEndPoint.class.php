<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;

if (!defined('PKG_SHOP_ALLOWED_PAGE_SIZES')) {
    define('PKG_SHOP_ALLOWED_PAGE_SIZES', '40,60,100,120');
}

/**
 * the Produkt catalog -> shows all articles of a given category, and the detail item view...
 * IMPORTANT: do not extend this class. instead extend from MTShopArticleCatalogCore.
 *
 * @deprecated since 6.2.0 - no longer used.
 **/
class MTShopArticleCatalogCoreEndPoint extends TShopUserCustomModelBase
{
    const URL_ITEM_ID = 'itemid';
    const URL_CATEGORY_ID = 'categoryid';
    const URL_PAGE = 'listpage';
    const URL_PAGE_SIZE = 'iPageSize';
    const URL_ORDER_BY = 'sortid';
    const URL_FILTER = 'lf';

    const MSG_CONSUMER_NAME = 'MTShopArticleCatalogCore';

    const SESSION_CAPTCHA = 'MTShopArticleCatalogCoreSESSIONcaptcha';
    const SESSION_ACTIVE_ORDER_BY = 'MTShopArticleCatalogCoreSESSIONorderbyid';

    /**
     * current page.
     *
     * @var int
     */
    protected $iPage = 0;
    /**
     * number of records per page.
     *
     * @var int
     */
    protected $iPageSize = 20;
    /**
     * current item.
     *
     * @var string
     */
    protected $iItemId = null;
    /**
     * current category - if not set and an item is set, then the items primary category will be used.
     * if neither item nor category is set, then the first root category will be used.
     *
     * @var string
     */
    protected $iCategoryId = null;

    /**
     * holds the current order by id.
     *
     * @var string
     */
    protected $iActiveShopModuleArticlelistOrderbyId = null;

    /**
     * any filter restrictions for the list.
     *
     * @var array
     */
    protected $aFilter = array();

    /**
     * the current article list.
     *
     * @var TdbShopArticleList
     */
    protected $oList = null;

    /**
     * set this to false if you need to prevent caching of the list/item.
     *
     * @var bool
     */
    protected $bAllowCache = true;

    /**
     * module conf data.
     *
     * @var TdbShopArticleCatalogConf
     */
    protected $oModuleConf = null;

    protected $bAllowHTMLDivWrapping = true;

    public function __sleep()
    {
        $aSleep = parent::__sleep();
        $aSleep[] = 'iPage';
        $aSleep[] = 'iPageSize';
        $aSleep[] = 'iItemId';
        $aSleep[] = 'iCategoryId';
        $aSleep[] = 'aFilter';
        $aSleep[] = 'iActiveShopModuleArticlelistOrderbyId';

        return $aSleep;
    }

    /**
     * add your custom methods as array to $this->methodCallAllowed here
     * to allow them to be called from web.
     */
    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'WriteReview';
        $this->methodCallAllowed[] = 'ChangePage';
        $this->methodCallAllowed[] = 'ChangePageAjax';
    }

    /**
     * send a paging signal to the list with iListIdent.
     *
     * @param string $iListIdent
     *
     * @return void
     */
    public function ChangePage($iListIdent = null)
    {
    }

    /**
     * send a paging signal to the list with iListIdent
     * since we changed the list to act on the signal on its own, we only need to return the
     * result here.
     *
     * @param string $iListIdent
     *
     * @return MTShopArticleListResponse
     */
    public function ChangePageAjax($iListIdent = null)
    {
        $oResponse = new MTShopArticleListResponse();
        /** @var $oResponse MTShopArticleListResponse */
        if (null === $iListIdent) {
            $oGlobal = TGlobal::instance();
            $iListIdent = $oGlobal->GetUserData(TdbShopArticleList::URL_LIST_KEY_NAME);
        }

        $oListItem = $this->oList;
        if (null !== $oListItem) {
            $bViewNameExists = false;
            $bViewTypeExists = false;
            $bCallTimeVarsExists = false;

            $aItemRawData = TdbShopArticleList::GetInstanceDataFromSession($iListIdent);
            if (is_array($aItemRawData)) {
                $bViewNameExists = (array_key_exists('sViewName', $aItemRawData));
                $bViewTypeExists = (array_key_exists('sViewType', $aItemRawData));
                $bCallTimeVarsExists = (array_key_exists('aCallTimeVars', $aItemRawData));
            }
            if ($bViewNameExists && $bViewTypeExists && $bCallTimeVarsExists) {
                // fetch data for requested item
                $oResponse->iNumberOfResults = $oListItem->GetNumberOfRecordsForCurrentPage();
                $oResponse->bHasNextPage = $oListItem->HasNextPage();
                $oResponse->bHasPreviousPage = $oListItem->HasPreviousPage();
                $oResponse->sItemPage = $oListItem->Render($aItemRawData['sViewName'], $aItemRawData['sViewType'], $aItemRawData['aCallTimeVars']);
                $oResponse->iListKey = $iListIdent;
            }
        }

        $_SESSION['iLastPageNumber'] = $oListItem->GetCurrentPageNumber() - 1;

        return $oResponse;
    }

    public function Init()
    {
        parent::Init();
        $this->oModuleConf = TdbShopArticleCatalogConf::GetNewInstance();
        $this->oModuleConf->LoadFromField('cms_tpl_module_instance_id', $this->instanceID);
        $this->iPageSize = $this->GetActivePageSize();

        $oCategory = TdbShop::GetActiveCategory();
        if (is_object($oCategory)) {
            $this->iActiveShopModuleArticlelistOrderbyId = $this->oModuleConf->GetDefaultOrderBy($oCategory);
        }

        // load the list url parameters...
        if ($this->global->UserDataExists(self::URL_PAGE)) {
            $this->iPage = intval($this->global->GetUserData(self::URL_PAGE));
        }
        $iUserActiveShopModuleArticlelistOrderbyId = self::GetActiveShopModuleArticlelistOrderbyId($this->instanceID);
        if (!is_null($iUserActiveShopModuleArticlelistOrderbyId)) {
            $this->iActiveShopModuleArticlelistOrderbyId = $iUserActiveShopModuleArticlelistOrderbyId;
        }
        $this->aFilter = TdbShop::GetActiveFilter();

        // data valid?
        if ($this->iPage < 0) {
            $this->iPage = 0;
        }
        $oActiveItem = self::GetActiveItem();
        if (!is_null($oActiveItem)) {
            $this->bAllowCache = false;
            $this->iItemId = $oActiveItem->id;
            $sAddItemId = $this->iItemId;
            // detail view! add to view history... note that we always add the parent if we are dealing with a variant
            if ($oActiveItem->IsVariant()) {
                $sAddItemId = $oActiveItem->fieldVariantParentId;
            }
            $oUser = TdbDataExtranetUser::GetInstance();
            $oUser->AddArticleIdToViewHistory($sAddItemId);

            // and update product view count
            $oActiveItem->UpdateProductViewCount();
        }
        $oActiveCategory = self::GetActiveCategory();
        if (!is_null($oActiveCategory)) {
            $this->iCategoryId = $oActiveCategory->id;
        }

        $_SESSION[self::SESSION_ACTIVE_ORDER_BY] = $this->iActiveShopModuleArticlelistOrderbyId;

        $oActivePage = $this->getActivePageService()->getActivePage();
        $oActiveItem = TdbShop::GetActiveItem();
        if (!$oActiveItem && isset($oActivePage)) {
            $this->LoadArticleList(); // load the list only if we are not on a detail page
            $_SESSION['sLastPage'] = $oActivePage->GetRealURLPlain();
            $_SESSION['iLastPageNumber'] = $this->iPage;
        }
    }

    /**
     * @return int
     */
    protected function GetActivePageSize()
    {
        $iPageSize = $this->oModuleConf->fieldPageSize;
        if ($this->global->UserDataExists(self::URL_PAGE_SIZE)) {
            $iPageSizeTmp = intval($this->global->GetUserData(self::URL_PAGE_SIZE));

            $aAllowedPageSize = explode(',', PKG_SHOP_ALLOWED_PAGE_SIZES);
            $aAllowedPageSize[] = $this->oModuleConf->fieldPageSize;
            foreach ($aAllowedPageSize as $i => $iSize) {
                $aAllowedPageSize[$i] = intval(trim($iSize));
            }
            if (in_array($iPageSizeTmp, $aAllowedPageSize)) {
                $iPageSize = $iPageSizeTmp;
            }
        }

        return $iPageSize;
    }

    /**
     * Get the current active item.
     *
     * @return TdbShopArticle|null
     */
    public static function GetActiveItem()
    {
        return TdbShop::GetActiveItem();
    }

    /**
     * Get the current active item.
     *
     * @return TdbShopCategory|null
     */
    public static function GetActiveCategory()
    {
        return TdbShop::GetActiveCategory();
    }

    public function Execute()
    {
        parent::Execute();
        // Initialize objects
        $this->data['oActiveCategory'] = self::GetActiveCategory();
        $this->data['oActiveArticle'] = self::GetActiveItem();
        $this->data['oModuleConf'] = $this->oModuleConf;
        // load list...

        if (!is_null($this->data['oActiveArticle'])) {
            // show detail page if we have an active article
            $this->ViewArticleHook();
        } else {
            $this->data['oArticleList'] = $this->oList;
            $this->data['iPage'] = $this->iPage;
            $this->data['iPageSize'] = $this->iPageSize;
            $this->data['iActiveShopModuleArticlelistOrderbyId'] = $this->iActiveShopModuleArticlelistOrderbyId;

            if ($this->oList->Length() > 0) {
                $this->data['oOrderByList'] = $this->GetOrderByFilterList();
            } else {
                $this->data['oOrderByList'] = false;
            }
        }

        $this->data['oFilterManufacturer'] = TdbShopManufacturer::GetInstanceForCurrentFilter();
        $this->data['sActiveOrderByString'] = $this->GetActiveOrderByString($this->iActiveShopModuleArticlelistOrderbyId, $this->instanceID);

        $aListRequest = array();
        $oGlobal = TGlobal::instance();
        if ($oGlobal->UserDataExists(TdbShopArticleList::URL_LIST_KEY_NAME)) {
            $aListRequest[$oGlobal->GetUserData(TdbShopArticleList::URL_LIST_KEY_NAME)] = $oGlobal->GetUserData(TdbShopArticleList::URL_LIST_REQUEST);
        }
        $this->data['aListRequest'] = $aListRequest;

        return $this->data;
    }

    /**
     * write a review.
     *
     * @return void
     */
    public function WriteReview()
    {
        // validate user input...
        $bDataValide = false;
        $oMsgManager = TCMSMessageManager::GetInstance();
        $oGlobal = TGlobal::instance();
        $aUserData = $oGlobal->GetUserData(TdbShopArticleReview::INPUT_BASE_NAME);
        if (is_array($aUserData)) {
            $bDataValide = true;

            $sCaptcha = '';
            if (array_key_exists('captcha', $aUserData)) {
                $sCaptcha = trim($aUserData['captcha']);
            }
            if (empty($sCaptcha) || $sCaptcha != $this->GetCaptchaValue()) {
                $bDataValide = false;
                $oMsgManager->AddMessage(
                    TdbShopArticleReview::MSG_CONSUMER_BASE_NAME.'-captcha',
                    'INPUT-ERROR-INVALID-CAPTCHA'
                );
            }

            $aRequiredFields = array('rating', 'author_name', 'author_email');
            foreach ($aRequiredFields as $sFieldName) {
                $sVal = '';
                if (array_key_exists($sFieldName, $aUserData)) {
                    $sVal = trim($aUserData[$sFieldName]);
                }
                if (empty($sVal)) {
                    $bDataValide = false;
                    $oMsgManager->AddMessage(
                        TdbShopArticleReview::MSG_CONSUMER_BASE_NAME.'-'.$sFieldName,
                        'ERROR-USER-REQUIRED-FIELD-MISSING'
                    );
                }
            }
        }
        if ($bDataValide) {
            if (!preg_match('#^[0-5]$#', $aUserData['rating'])) {
                $oMsgManager->AddMessage(
                    TdbShopArticleReview::MSG_CONSUMER_BASE_NAME.'-rating',
                    'ERROR-INVALID-FIELD-VALUE-RATING'
                );
                $bDataValide = false;
            }
        }

        if ($bDataValide) {
            // save item
            $oReviewItem = TdbShopArticleReview::GetNewInstance();
            /** @var $oReviewItem TdbShopArticleReview */
            $aUserData['shop_article_id'] = $this->iItemId;
            $oReviewItem->LoadFromRowProtected($aUserData);
            $oReviewItem->AllowEditByAll(true);
            $oReviewItem->Save();

            // notify the shop owner
            $oReviewItem->SendNewReviewNotification();

            $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-SUBMITTED', $aUserData);
            $oActiveItm = static::GetActiveItem();
            $oActiveCategory = static::GetActiveCategory();
            $iActiveCategoryId = null;
            if (is_object($oActiveCategory)) {
                $iActiveCategoryId = $oActiveCategory->id;
            }
            $sURL = $oActiveItm->GetDetailLink(true, $iActiveCategoryId);
            $this->getRedirectService()->redirect($sURL);
        }
    }

    /**
     * @return false|string
     */
    protected function GetCaptchaValue()
    {
        $sCaptchaValue = false;
        if (array_key_exists(self::SESSION_CAPTCHA, $_SESSION)) {
            $sCaptchaValue = $_SESSION[self::SESSION_CAPTCHA];
        }

        return $sCaptchaValue;
    }

    /**
     * @return string
     */
    protected function GenerateCaptcha()
    {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $val = $num1 + $num2;
        $_SESSION[self::SESSION_CAPTCHA] = $val;

        $sCaptchQuestion = TGlobal::Translate('chameleon_system_shop.module_article_catalog.captcha', array('%num1%' => $num1, '%num2%' => $num2));

        return $sCaptchQuestion;
    }

    /**
     * called if the user requested to view an item.
     *
     * @return void
     */
    protected function ViewArticleHook()
    {
        $this->SetTemplate('MTShopArticleCatalog', 'system/article');

        // add review item and fill with user data
        $oReviewItem = TdbShopArticleReview::GetNewInstance();
        /** @var $oReviewItem TdbShopArticleReview */
        $oGlobal = TGlobal::instance();
        $aReviewData = array();
        if ($oGlobal->UserDataExists(TdbShopArticleReview::INPUT_BASE_NAME)) {
            $aReviewData = $oGlobal->GetUserData(TdbShopArticleReview::INPUT_BASE_NAME);
        } else {
            $oUser = TdbDataExtranetUser::GetInstance();
            if ($oUser->IsLoggedIn()) {
                $aReviewData['author_name'] = $oUser->GetUserAlias();
                $aReviewData['author_email'] = $oUser->GetUserEMail();
            }
        }
        $aReviewData['captcha-question'] = $this->GenerateCaptcha();

        if (is_array($aReviewData)) {
            $oReviewItem->LoadFromRowProtected($aReviewData);
        }
        $this->data['oReviewEntryItem'] = $oReviewItem;
    }

    /**
     * load the article list and store it in $this->oList.
     *
     * @return void
     */
    protected function LoadArticleList()
    {
        if (is_null($this->oList)) {
            $iListIdent = $this->global->GetUserData(TdbShopArticleList::URL_LIST_KEY_NAME);
            if ($iListIdent) {
                $this->oList = TdbShopArticleList::GetInstanceFromSession($iListIdent);
            }
            if (is_null($this->oList)) {
                $oActiveCategory = self::GetActiveCategory();
                /** @var $oActiveCategory TdbShopCategory */
                $sOrderListBy = $this->GetActiveOrderByString($this->iActiveShopModuleArticlelistOrderbyId);
                if (!is_null($oActiveCategory)) {
                    if ($this->oModuleConf->fieldShowSubcategoryProducts) {
                        $this->oList = $oActiveCategory->GetArticleListIncludingSubcategories($sOrderListBy, $this->aFilter);
                    } else {
                        $this->oList = $oActiveCategory->GetArticleList($sOrderListBy, $this->aFilter);
                    }
                } else {
                    $this->oList = $this->getListWhenNoCategoryDefined($sOrderListBy, $this->aFilter);
                }
            }

            $this->PostLoadArticleListHook();

            if (!is_null($this->oList)) {
                $this->oList->RestorePagingInfoFromSession($this->iPageSize * $this->iPage, $this->iPageSize);

                $sRequest = $this->global->GetUserData(TdbShopArticleList::URL_LIST_REQUEST);
                $this->oList->HandleURLRequest($sRequest, true);
            }
        }
    }

    /**
     * @param string $sOrderListBy
     * @param array $filter
     *
     * @return TdbShopArticleList
     */
    protected function getListWhenNoCategoryDefined($sOrderListBy, $filter)
    {
        return TdbShopArticleList::LoadArticleList($sOrderListBy, 100, $filter);
    }

    /**
     * method is called just after loading the article list and BEFORE setting the paging data.
     *
     * @return void
     */
    protected function PostLoadArticleListHook()
    {
    }

    /**
     * return order by list for category view.
     *
     * @return TdbShopModuleArticlelistOrderbyList
     */
    protected function GetOrderByFilterList()
    {
        $aFilterIds = $this->oModuleConf->GetMLTIdList('shop_module_articlelist_orderby');
        $oList = TdbShopModuleArticlelistOrderbyList::GetListForIds($aFilterIds);
        $oList->bAllowItemCache = true;
        // fill item cache
        $i = 0;
        while ($oItem = $oList->Next()) {
            ++$i;
        }
        $oList->GoToStart();

        return $oList;
    }

    /**
     * return the active order by id based on session or url parameter.
     *
     * @return string|null
     *
     * @param string $sInstanceId
     */
    public function GetActiveShopModuleArticlelistOrderbyId($sInstanceId = '')
    {
        $iActiveShopModuleArticlelistOrderbyId = null;
        $oGlobal = TGlobal::instance();
        if ($oGlobal->UserDataExists(self::URL_ORDER_BY)) {
            /** @var string $iActiveShopModuleArticlelistOrderbyId */
            $iActiveShopModuleArticlelistOrderbyId = $oGlobal->GetUserData(self::URL_ORDER_BY);
        } else {
            if (array_key_exists(self::SESSION_ACTIVE_ORDER_BY.$sInstanceId, $_SESSION)) {
                /** @var string $iActiveShopModuleArticlelistOrderbyId */
                $iActiveShopModuleArticlelistOrderbyId = $_SESSION[self::SESSION_ACTIVE_ORDER_BY.$sInstanceId];
            }
        }

        return $iActiveShopModuleArticlelistOrderbyId;
    }

    /**
     * return the current order string for the article lists.
     *
     * @return string
     *
     * @param null|string $sDefaultActiveShopModuleArticlelistOrderbyId
     * @param null|string $sInstanceId
     */
    public function GetActiveOrderByString($sDefaultActiveShopModuleArticlelistOrderbyId = null, $sInstanceId = '')
    {
        static $oOrderBy = null;
        if (is_null($oOrderBy)) {
            // remove default order by - for performance reason we should be allowed to sort by nothing
            $sOrder = ''; //`shop_article`.`list_rank` DESC, `shop_article`.`name` ASC';
            $iActiveShopModuleArticlelistOrderbyId = $this->GetActiveShopModuleArticlelistOrderbyId($sInstanceId);
            if (is_null($iActiveShopModuleArticlelistOrderbyId)) {
                $iActiveShopModuleArticlelistOrderbyId = $sDefaultActiveShopModuleArticlelistOrderbyId;
            }

            if (!is_null($iActiveShopModuleArticlelistOrderbyId)) {
                $oOrderBy = TdbShopModuleArticlelistOrderby::GetNewInstance();
                /** @var $oOrderBy TdbShopModuleArticlelistOrderby */
                if (!$oOrderBy->Load($iActiveShopModuleArticlelistOrderbyId)) {
                    $oOrderBy = null;
                }
            }
            if (is_null($oOrderBy)) {
                $oShop = TdbShop::GetInstance();
                $oOrderBy = $oShop->GetFieldShopModuleArticlelistOrderby();
            }
            if (!is_null($oOrderBy)) {
                $sOrder = $oOrderBy->GetOrderByString();
                $_SESSION[self::SESSION_ACTIVE_ORDER_BY.$sInstanceId] = $iActiveShopModuleArticlelistOrderbyId;
            }
        } else {
            $sOrder = $oOrderBy->GetOrderByString();
        }

        return $sOrder;
    }

    /**
     * prevent caching if there are messages.
     *
     * @return bool
     */
    public function _AllowCache()
    {
        return false; // we currently cannot cache the list, since each list has a unique state key per user which would be include in the cached result (#26067)
    }

    /**
     * return an assoc array of parameters that describe the state of the module.
     *
     * @return array
     */
    public function _GetCacheParameters()
    {
        $parameters = parent::_GetCacheParameters();

        $parameters['sActivePageId'] = $this->getActivePageService()->getActivePage()->id;
        $parameters['iPage'] = $this->iPage;
        $parameters['iActiveShopModuleArticlelistOrderbyId'] = $this->iActiveShopModuleArticlelistOrderbyId;
        $parameters['iPageSize'] = $this->iPageSize;
        $parameters['iItemId'] = $this->iItemId;
        $parameters['iCategoryId'] = $this->iCategoryId;
        $parameters['aFilter'] = md5(serialize($this->aFilter));
        $aAdditionalParameters = TdbShopArticleList::GetGlobalArticleListCacheKeyParameters();
        if (count($aAdditionalParameters)) {
            foreach ($aAdditionalParameters as $sKey => $sVal) {
                $parameters[$sKey] = $sVal;
            }
        }

        return $parameters;
    }

    /**
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
        $aClearCacheInfo = parent::_GetCacheTableInfos();

        if (!is_array($aClearCacheInfo)) {
            $aClearCacheInfo = array();
        }

        // add all tables required by the article
        $aArticleCacheTables = TdbShopArticle::GetCacheRelevantTables();
        foreach ($aArticleCacheTables as $tableName) {
            $aClearCacheInfo[] = array('table' => $tableName, 'id' => '');
        }

        // also react to the shop settings
        $oShop = TdbShop::GetInstance();
        $aClearCacheInfo[] = array('table' => 'shop', 'id' => $oShop->id);
        $aClearCacheInfo[] = array('table' => 'shop_article_catalog_conf ', 'id' => $this->instanceID);

        return $aClearCacheInfo;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    private function getRedirectService(): ICmsCoreRedirect
    {
        return ServiceLocator::get('chameleon_system_core.redirect');
    }
}
