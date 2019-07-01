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
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\CoreBundle\Service\SystemPageServiceInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * the shop config object. use GetInstance to fetch the current config.
/**/
class TShop extends TShopAutoParent implements IPkgShopVatable
{
    const VIEW_PATH = 'pkgShop/views/db/TShop';
    const SESSION_ACTIVE_SEARCH_CACHE_ID = 'session-shop-active-search-cache-id';
    const SESSION_AFFILIATE_CODE = 'mtshopbasketcoreaffiliatecode';
    const SESSION_ACTIVE_VARIANT_ARRAY = 'aShopActiveVariantArray';
    const CMS_COUNTER_ORDER = 'order';
    const CMS_COUNTER_CUSTOMER = 'customer';

    /**
     * the active search object.
     *
     * @var TdbShopSearchCache
     */
    protected $oActiveSearchCache = null;
    /**
     * set to true, if the content in internal cache has changed.
     *
     * @var bool
     *
     * @deprecated since 6.2.0 - should no longer be used by subclasses.
     */
    protected $bInternalCacheMarkedAsDirty = true;

    /**
     * set the affiliate partner code for the current session.
     *
     * @param string $sCode
     */
    public function SetAffiliateCode($sCode)
    {
        $_SESSION[TdbShop::SESSION_AFFILIATE_CODE] = $sCode;
    }

    /**
     * return the affiliate partner code for the current session.
     *
     * @return string
     */
    public function GetAffilateCode()
    {
        $sCode = false;
        if (array_key_exists(TdbShop::SESSION_AFFILIATE_CODE, $_SESSION)) {
            $sCode = $_SESSION[TdbShop::SESSION_AFFILIATE_CODE];
        }

        return $sCode;
    }

    /**
     * return true if guest purchases are allowed - false if a customer account is required.
     *
     * @return bool
     */
    public function allowPurchaseAsGuest()
    {
        if (property_exists($this, 'fieldAllowGuestPurchase')) {
            return $this->fieldAllowGuestPurchase;
        }

        return true;
    }

    /**
     * Factory to fetch the config for the active portal.
     * Note: the method fetches the portal id through the active page, if no portal id is passed. So make sure
     *       an active page exists if you are not passing a portal id.
     *
     * @param int $iPortalId - optional portal id. method fetches the id from the TCMSActivePage if iPortalId is not given
     *
     * @return TdbShop
     *
     * @deprecated use service chameleon_system_shop.shop_service instead
     */
    public static function &GetInstance($iPortalId = null)
    {
        $shop = null;
        $activeShopService = self::getShopService();
        if (null === $iPortalId) {
            $shop = $activeShopService->getActiveShop();
        } else {
            $shop = $activeShopService->getShopForPortalId($iPortalId);
        }

        return $shop;
    }

    /**
     * store a copy of the active search object.
     *
     * @param TdbShopSearchCache $oActiveSearchCache
     */
    public function SetActiveSearchCacheObject(TdbShopSearchCache $oActiveSearchCache)
    {
        $this->oActiveSearchCache = &$oActiveSearchCache;
        if (!is_null($oActiveSearchCache)) {
            $_SESSION[self::SESSION_ACTIVE_SEARCH_CACHE_ID] = base64_encode(serialize($oActiveSearchCache));
        } else {
            unset($_SESSION[self::SESSION_ACTIVE_SEARCH_CACHE_ID]);
        }
    }

    /**
     * return pointer to the search cache object.
     *
     * @return TdbShopSearchCache
     */
    public function &GetActiveSearchObject()
    {
        if (is_null($this->oActiveSearchCache) && array_key_exists(self::SESSION_ACTIVE_SEARCH_CACHE_ID, $_SESSION)) {
            $this->oActiveSearchCache = unserialize(base64_decode($_SESSION[self::SESSION_ACTIVE_SEARCH_CACHE_ID]));
            //        $this->oActiveSearchCache = TdbShopSearchCache::GetNewInstance();
            //        if (!$this->oActiveSearchCache->Load($_SESSION[self::SESSION_ACTIVE_SEARCH_CACHE_ID])) $this->oActiveSearchCache = null;
        }

        return $this->oActiveSearchCache;
    }

    /**
     * return assoc array with the categories starting from the current root category
     * to the current active category (in that order). return null if there is no active category.
     *
     * @return array
     */
    public static function GetActiveCategoryPath()
    {
        static $aCategoryList;
        if (!isset($aCategoryList)) {
            $aCategoryList = null;
            $oCurrentCategory = self::getShopService()->getActiveCategory();
            if (!is_null($oCurrentCategory)) {
                $aCategoryList = array();
                do {
                    $aCategoryList[$oCurrentCategory->id] = $oCurrentCategory;
                } while ($oCurrentCategory = &$oCurrentCategory->GetParent());
                $aCategoryList = array_reverse($aCategoryList, true);
            }
        }

        return $aCategoryList;
    }

    /**
     * Returns the active manufacturer.
     *
     * @return null|TdbShopManufacturer
     *
     * @deprecated since 6.2.11 - use ShopService::getActiveManufacturer()
     */
    public static function GetActiveManufacturer()
    {
        static $oActiveManufacturer = 'x';
        if ('x' === $oActiveManufacturer) {
            $oActiveManufacturer = null;
            $oGlobal = TGlobal::instance();
            $iManufacturerId = $oGlobal->GetUserData(MTShopManufacturerArticleCatalogCore::URL_MANUFACTURER_ID);
            if (empty($iManufacturerId)) {
                $oItem = self::getShopService()->getActiveProduct();
                if (null !== $oItem) {
                    $oActiveManufacturer = $oItem->GetFieldShopManufacturer();
                }
            } else {
                $oActiveManufacturer = TdbShopManufacturer::GetNewInstance();
                if (!$oActiveManufacturer->Load($iManufacturerId)) {
                    $oActiveManufacturer = null;
                }
            }
        }

        return $oActiveManufacturer;
    }

    /**
     * returns the current active category.
     *
     * @return TdbShopCategory
     *
     * @deprecated - use the service chameleon_system_shop.shop_service instead (method getActiveCategory)
     */
    public static function GetActiveCategory()
    {
        return self::getShopService()->getActiveCategory();
    }

    /**
     * return the active root category.
     *
     * @return TdbShopCategory
     */
    public static function GetActiveRootCategory()
    {
        static $oActiveRootCategory = 'x';
        if ('x' === $oActiveRootCategory) {
            $oActiveRootCategory = null;
            $oActiveCategory = self::getShopService()->GetActiveCategory();
            if (null !== $oActiveCategory) {
                $oActiveRootCategory = $oActiveCategory->GetRootCategory();
            }
        }

        return $oActiveRootCategory;
    }

    /**
     * return current active filter conditions.
     *
     * @return array
     */
    public static function GetActiveFilter()
    {
        static $aFilter = 'x';
        if ('x' === $aFilter) {
            $oGlobal = TGlobal::instance();
            $aFilter = $oGlobal->GetUserData(MTShopArticleCatalogCore::URL_FILTER);
            if (!is_array($aFilter)) {
                $aFilter = array();
            }
            // now reduce filter list to valid filter fields
            $aValidFilterFields = TdbShop::GetValidFilterFields();
            $aValidFilter = array();
            foreach ($aValidFilterFields as $sField) {
                if (array_key_exists($sField, $aFilter)) {
                    $aValidFilter[$sField] = $aFilter[$sField];
                }
            }
            $aFilter = $aValidFilter;
        }

        return $aFilter;
    }

    /**
     * return an sql string for the current filter.
     *
     * @return string
     */
    public static function GetActiveFilterString($sExcludeKey = '')
    {
        $aFilter = TdbShop::GetActiveFilter();
        $aTmp = array();
        foreach ($aFilter as $filterKey => $filterVal) {
            if ($filterKey != $sExcludeKey) {
                $aTmp[] = TdbShop::GetFilterSQLString($filterKey, $filterVal);
            }
        }

        return implode(' AND ', $aTmp);
    }

    public static function GetFilterSQLString($sFilterKey, $sFilterVal)
    {
        $sSQL = '';
        switch ($sFilterKey) {
            case TdbShopCategory::FILTER_KEY_NAME:
                $oCat = TdbShopCategory::GetNewInstance();
                /** @var $oCat TdbShopCategory */
                if ($oCat->Load($sFilterVal)) {
                    $aCatIdList = $oCat->GetAllChildrenIds();
                    $aCatIdList[] = $oCat->id;
                    $aCatIdList = TTools::MysqlRealEscapeArray($aCatIdList);
                    $sSQL .= "`shop_article_shop_category_mlt`.`target_id` IN ('".implode("', '", $aCatIdList)."')";
                }

                break;

            default:
                $sSQL .= "{$sFilterKey} = '".MySqlLegacySupport::getInstance()->real_escape_string($sFilterVal)."'";
                break;
        }
        if (!empty($sSQL)) {
            $sSQL = "({$sSQL})";
        }

        return $sSQL;
    }

    /**
     * returns all fields that may be passed as filter fields.
     *
     * @return array
     */
    public static function GetValidFilterFields()
    {
        return array(TdbShopManufacturer::FILTER_KEY_NAME, TdbShopCategory::FILTER_KEY_NAME);
    }

    /**
     * Get the current active item.
     *
     * @return TdbShopArticle
     *
     * @deprecated - use the service chameleon_system_shop.shop_service instead (method getActiveProduct)
     */
    public static function GetActiveItem()
    {
        return self::getShopService()->getActiveProduct();
    }

    /**
     * Returns the active variant for given article.
     * If no Variant is active return given article.
     *
     * @param TdbShopArticle $oArticle
     *
     * @return TdbShopArticle
     */
    public static function GetActiveItemVariant($oArticle)
    {
        $aVariantTypeSelection = TdbShopVariantDisplayHandler::GetActiveVariantTypeSelection(true);
        if (is_array($aVariantTypeSelection)) {
            $oSet = &$oArticle->GetFieldShopVariantSet();
            if (null === $oSet) {
                return $oArticle;
            }
            $oTypes = &$oSet->GetFieldShopVariantTypeList();
            if (count($aVariantTypeSelection) == $oTypes->Length()) {
                if (!$oArticle->IsVariant()) {
                    $oVariants = &$oArticle->GetFieldShopArticleVariantsList($aVariantTypeSelection);
                } else {
                    $oParentArticle = $oArticle->GetFieldVariantParent();
                    $oVariants = &$oParentArticle->GetFieldShopArticleVariantsList($aVariantTypeSelection);
                }

                if (1 == $oVariants->Length()) {
                    $oArticle = $oVariants->Current();
                }
            }
        } elseif (!$oArticle->IsVariant()) {
            $oVariants = &$oArticle->GetFieldShopArticleVariantsList();
            if (1 == $oVariants->Length()) {
                $oArticle = $oVariants->Current();
            }
        }

        return $oArticle;
    }

    /**
     * returns the default country id. this is usually the id set in the shop table, but may also be fetched via
     * ip lookup.
     *
     * @return int
     */
    public function GetDefaultCountryId()
    {
        // Not yet implemented
    }

    /**
     * return the name of the layout spot in which a basket module can be found.
     * this basket module will recieve add to basket request, etc.
     *
     * @return string
     */
    public function GetBasketModuleSpotName()
    {
        return $this->fieldBasketSpotName;
    }

    /**
     * return the default vat group.
     *
     * @return TdbShopVat
     */
    public function GetVat()
    {
        return $this->GetFieldShopVat();
    }

    /**
     * reserves the next free order number, and returns it
     * NOTE: the order number returned will be permanently reserved.
     *
     * @return int
     */
    public function GetNextFreeOrderNumber()
    {
        $cmsCounter = new \esono\pkgCmsCounter\CmsCounter(\ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection'));

        return $cmsCounter->get($this, self::CMS_COUNTER_ORDER);
    }

    /**
     * reserves the next free customer number, and returns it
     * NOTE: the order number returned will be permanently reserved.
     *
     * @return int
     */
    public function GetNextFreeCustomerNumber()
    {
        $cmsCounter = new \esono\pkgCmsCounter\CmsCounter(\ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection'));

        return $cmsCounter->get($this, self::CMS_COUNTER_CUSTOMER);
    }

    /* LINK SECTION - this section holds methods that return links to shop specific pages         */

    /**
     * return link to checkout page - NOTE: all get/post parameters will be excluded. if you need some
     * make sure you remove them from the list.
     *
     * @param bool $bJumpAsFarAsPossible             - set to true and the system will try to jump into the order process as far as possible (user step if not logged in, or shipping step if logged in)
     * @param bool $bTargetBasketPageWithoutRedirect
     *
     * @return string
     */
    public function GetBasketLink($bJumpAsFarAsPossible = false, $bTargetBasketPageWithoutRedirect = false)
    {
        $aParams = array('module_fnc['.$this->GetBasketModuleSpotName().']' => 'JumpToBasketPage');
        if ($bJumpAsFarAsPossible) {
            $aParams['bJumpAsFarAsPossible'] = '1';
        }
        $oGlobal = TGlobal::instance();
        $aUserData = $oGlobal->GetUserData();
        $aExcludeParams = array();
        if (is_array($aUserData)) {
            $aExcludeParams = array_keys($aUserData);
        }

        $activePageService = self::getActivePageService();

        if ($bTargetBasketPageWithoutRedirect) {
            $systemPageService = $this->getSystemPageService();
            $aExcludeParams[] = 'sourceurl';
            $checkoutSystemPage = $systemPageService->getSystemPage('checkout');
            $activePage = $activePageService->getActivePage();
            if (null === $checkoutSystemPage || $activePage->GetMainTreeId() !== $checkoutSystemPage->fieldCmsTreeId) {
                $aParams['sourceurl'] = $activePageService->getLinkToActivePageRelative(array(), $aExcludeParams);
            }
            try {
                $sURL = $systemPageService->getLinkToSystemPageRelative('checkout', $aParams);
            } catch (RouteNotFoundException $e) {
                $sURL = '';
            }
        } else {
            $sURL = $activePageService->getLinkToActivePageRelative($aParams, $aExcludeParams);
        }

        return $sURL;
    }

    /**
     * return the url to a system page with the internal name given by sSystemPageName
     * the system pages are defined through the shop config in the table shop_system_pages
     * Note: the links will be cached to reduce load.
     *
     * @deprecated since 6.1.0 - use chameleon_system_core.system_page_service::getLinkToSystemPage*() instead
     *
     * @param string $sSystemPageName
     * @param array  $aParameters      - optional parameters to add to the link
     * @param bool   $bForcePortalLink - set to true if you want to include the portal domain (http://..../) part in the link.
     * @param string $sAnchorName      - set name to jump to within the page
     *
     * @return string
     */
    public function GetLinkToSystemPage($sSystemPageName, $aParameters = null, $bForcePortalLink = false, $sAnchorName = '')
    {
        if (null === $aParameters) {
            $aParameters = array();
        }
        try {
            if ($bForcePortalLink) {
                $link = $this->getSystemPageService()->getLinkToSystemPageAbsolute($sSystemPageName, $aParameters);
            } else {
                $link = $this->getSystemPageService()->getLinkToSystemPageRelative($sSystemPageName, $aParameters);
            }
        } catch (RouteNotFoundException $e) {
            $link = '';
        }
        $sAnchorName = trim($sAnchorName);
        if (!empty($sAnchorName)) {
            $link .= '#'.$sAnchorName;
        }

        return $link;
    }

    /**
     * always returns relative url of the requested link
     * so the protocol and domain will be cut off if original link (from GetLinkToSystemPage) contains them.
     *
     * @deprecated since 6.1.0 - use chameleon_system_core.system_page_service::getLinkToSystemPageRelative() instead
     *
     * @param string $sSystemPageName
     * @param array  $aParameters     - optional parameters to add to the link
     * @param string $sAnchorName     - set name to jump to within the page
     *
     * @return string
     */
    public function getLinkToSystemPageRelative($sSystemPageName, $aParameters = null, $sAnchorName = '')
    {
        if (null === $aParameters) {
            $aParameters = array();
        }
        $link = $this->getSystemPageService()->getLinkToSystemPageRelative($sSystemPageName, $aParameters);
        $sAnchorName = trim($sAnchorName);
        if (!empty($sAnchorName)) {
            $link .= '#'.$sAnchorName;
        }

        return $link;
    }

    /**
     * return list of system page names.
     *
     * @return array
     */
    public function GetSystemPageNames()
    {
        $aSystemPages = array();
        $oPortal = self::getPortalDomainService()->getActivePortal();
        if ($oPortal) {
            $aSystemPages = $oPortal->GetSystemPageNames();
        }

        return $aSystemPages;
    }

    /**
     * return the link to a system page with the internal name given by sSystemPageName
     * the system pages are defined through the shop config in the table shop_system_pages
     * Note: the links will be cached to reduce load.
     *
     * @deprecated
     *
     * @param string $sLinkText        - the text to display in the link - no OutHTML will be called - so make sure to escape the incoming text
     * @param string $sSystemPageName
     * @param array  $aParameters      - optional parameters to add to the link
     * @param bool   $bForcePortalLink - set to true if you want to include the portal domain (http://..../) part in the link.
     * @param int    $iWidth           - The popup's width in pixels
     * @param int    $iHeight          - The popup's height in pixels
     * @param string $sCSSClass        - additional css class to add to the link
     * @param string $sAnchorName      - set name to jump to within the page*
     *
     * @return string
     */
    public function GetLinkToSystemPageAsPopUp($sLinkText, $sSystemPageName, $aParameters = null, $bForcePortalLink = false, $iWidth = 600, $iHeight = 450, $sCSSClass = '', $sAnchorName = '')
    {
        $sLink = '';
        $oPortal = self::getPortalDomainService()->getActivePortal();
        if ($oPortal) {
            $sLink = $oPortal->GetLinkToSystemPageAsPopUp($sLinkText, $sSystemPageName, $aParameters, $bForcePortalLink, $iWidth, $iHeight, false, $sCSSClass, $sAnchorName);
        }

        return $sLink;
    }

    /**
     * return the node for the system page with the name sSystemPageName. see GetLinkToSystemPage for details.
     *
     * @deprecated since 6.1.0 - use system_page_service::getSystemPage()->fieldCmsTreeId instead
     *
     * @param string $sSystemPageName
     *
     * @return string|null
     */
    public function GetSystemPageNodeId($sSystemPageName)
    {
        $systemPage = $this->getSystemPageService()->getSystemPage($sSystemPageName);
        if (null === $systemPage) {
            return null;
        }

        return $systemPage->fieldCmsTreeId;
    }

    /**
     * return the system info with the given name.
     *
     * @param string $sShopInfoName - internal name of the info record
     *
     * @return TdbShopSystemInfo
     */
    public function GetShopInfo($sShopInfoName)
    {
        $oInfo = TdbShopSystemInfo::GetNewInstance();
        if (!$oInfo->LoadFromFields(array('shop_id' => $this->id, 'name_internal' => $sShopInfoName))) {
            $oInfo = null;
        }

        return $oInfo;
    }

    /**
     * return an array with system page links.
     *
     * @deprecated
     *
     * @param bool $bForcePortalLink
     *
     * @return array
     */
    protected function GetSystemPageLinkList($bForcePortalLink)
    {
        trigger_error('do not use GetSystemPageLinkList on shop, shop system pages are now in portal.', E_USER_ERROR);
    }

    /* RENDER SECTION - this section holds methods that render shop sepcific information          */

    /**
     * render the shipping infos.
     *
     * @return string
     */
    public function RenderShippingInfo($sViewName, $sViewType, $aCallTimeVars = array())
    {
        $oView = new TViewParser();
        $oView->AddVar('oShop', $this);
        $oShippingIntroText = $this->GetShopInfo('shipping-intro');
        $oShippingEndText = $this->GetShopInfo('shipping-end');
        $oView->AddVar('oShippingIntroText', $oShippingIntroText);
        $oView->AddVar('oShippingEndText', $oShippingEndText);

        // get the shipping list for users not sigend in
        $oPublicShippingGroups = &TdbShopShippingGroupList::GetPublicShippingGroups();
        $oView->AddVar('oPublicShippingGroups', $oPublicShippingGroups);

        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, self::VIEW_PATH, $sViewType);
    }

    /**
     * used to add additional parameters to any render method for the shop object.
     *
     * @param string $sViewName - the view being requested
     * @param string $sViewType - view type (Core, Custom, Customer)
     *
     * @return array
     */
    protected function GetAdditionalViewVariables($sViewName, $sViewType)
    {
        return array();
    }

    /**
     * return ajax url to a function in the central shop handler.
     *
     * @param string $sMethod
     * @param array  $aParameter
     *
     * @return string
     */
    public function GetCentralShopHandlerAjaxURL($sMethod, $aParameter = array())
    {
        $oGlobal = TGlobal::instance();

        $aParameter[MTShopCentralHandler::URL_CALLING_SPOT_NAME] = $oGlobal->GetExecutingModulePointer()->sModuleSpotName;
        $aRealParams = array('module_fnc' => array($this->fieldShopCentralHandlerSpotName => 'ExecuteAjaxCall'), '_fnc' => $sMethod, MTShopCentralHandler::URL_DATA => $aParameter);
        $oActivePage = self::getActivePageService()->getActivePage();

        return $oActivePage->GetRealURLPlain($aRealParams);
    }

    /**
     * overwrite the method to allow caching.
     *
     * @return TdbShopPrimaryNaviList
     */
    public function &GetFieldShopPrimaryNaviList()
    {
        $oNaviList = &$this->GetFromInternalCache('oFieldShopPrimaryNaviList');
        if (is_null($oNaviList)) {
            $oNaviList = TdbShopPrimaryNaviList::GetListForShopId($this->id, $this->iLanguageId);
            $oNaviList->bAllowItemCache = true;
            $this->SetInternalCache('oFieldShopPrimaryNaviList', $oNaviList);
        }

        return $oNaviList;
    }

    /**
     * register the active variant for an article with a spot - this allow us to later refresh all
     * data for that spot without loosing the currentl selected variant info.
     *
     * @param string $sSpotName
     * @param string $sParentId
     * @param string $sShopVariantArticleId
     */
    public static function RegisterActiveVariantForSpot($sSpotName, $sParentId, $sShopVariantArticleId)
    {
        if (!array_key_exists(TdbShop::SESSION_ACTIVE_VARIANT_ARRAY, $_SESSION)) {
            $_SESSION[TdbShop::SESSION_ACTIVE_VARIANT_ARRAY] = array();
        }
        if (!array_key_exists($sSpotName, $_SESSION[TdbShop::SESSION_ACTIVE_VARIANT_ARRAY])) {
            $_SESSION[TdbShop::SESSION_ACTIVE_VARIANT_ARRAY][$sSpotName] = array();
        }
        $_SESSION[TdbShop::SESSION_ACTIVE_VARIANT_ARRAY][$sSpotName][$sParentId] = $sShopVariantArticleId;
    }

    /**
     * return the active variant for a parent article and a given spot. returns false if no
     * variant has been selected for that parent in the spot yet.
     *
     * @param string $sParentId
     *
     * @return string
     */
    public static function GetRegisteredActiveVariantForCurrentSpot($sParentId)
    {
        $sShopVariantArticleId = false;
        if (array_key_exists(TdbShop::SESSION_ACTIVE_VARIANT_ARRAY, $_SESSION)) {
            $oGlobal = TGlobal::instance();
            $oExecutingModuleSpot = $oGlobal->GetExecutingModulePointer();
            if ($oExecutingModuleSpot) {
                $sSpotName = $oExecutingModuleSpot->sModuleSpotName;
                if (array_key_exists($sSpotName, $_SESSION[TdbShop::SESSION_ACTIVE_VARIANT_ARRAY]) && array_key_exists($sParentId, $_SESSION[TdbShop::SESSION_ACTIVE_VARIANT_ARRAY][$sSpotName])) {
                    $sShopVariantArticleId = $_SESSION[TdbShop::SESSION_ACTIVE_VARIANT_ARRAY][$sSpotName][$sParentId];
                }
            }
        }

        return $sShopVariantArticleId;
    }

    /**
     * clear all registered varaints for alle spots.
     */
    public static function ResetAllRegisteredActiveVariantsForAllSpots()
    {
        if (array_key_exists(TdbShop::SESSION_ACTIVE_VARIANT_ARRAY, $_SESSION)) {
            unset($_SESSION[TdbShop::SESSION_ACTIVE_VARIANT_ARRAY]);
        }
    }

    /**
     * return an array of all parameters that define a state of a page and therefore must be
     * included in links that want to call an action on a page.
     *
     * @return array
     */
    public static function GetURLPageStateParameters()
    {
        // ------------------------------------------------------------------------
        $oURLData = &TCMSSmartURLData::GetActive();
        $aSeoParam = $oURLData->getSeoURLParameters();
        $aSeoParam[] = MTShopBasketCoreEndpoint::URL_REQUEST_PARAMETER;
        $aSeoParam[] = 'module_fnc';
        $aSeoParam[] = 'pagedef';

        return array_keys(TGlobal::instance()->GetRawUserData(null, $aSeoParam));
    }

    /**
     * return all trigger items for the article.
     *
     * @return array
     */
    public function CacheGetTriggerList()
    {
        return array(array('table' => $this->table, 'id' => $this->id));
    }

    /**
     * commit the current content to cache - need only be called if something relevant
     * changes in the object.
     */
    public function CacheCommit()
    {
        $this->bInternalCacheMarkedAsDirty = false;
    }

    /**
     * get the cache key used to id the object in cache.
     *
     * @param string|int|null $iPortalId
     *
     * @return string
     */
    protected static function CacheGetKey($iPortalId = null)
    {
        if (is_null($iPortalId)) {
            $portal = self::getPortalDomainService()->getActivePortal();
            if (null !== $portal) {
                $iPortalId = $portal->id;
            }
        }
        $aKey = array('class' => 'TdbShop', 'ident' => 'objectInstance', 'portalid' => $iPortalId);

        return TCacheManager::GetKey($aKey);
    }

    protected function SetInternalCache($varName, $content)
    {
        parent::SetInternalCache($varName, $content);
        $this->bInternalCacheMarkedAsDirty = true;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private static function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return SystemPageServiceInterface
     */
    private function getSystemPageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.system_page_service');
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private static function getPortalDomainService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }

    /**
     * @return ShopServiceInterface
     */
    private static function getShopService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
    }
}
