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
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * The shop config object. Load via chameleon_system_shop.shop_service::getActiveShop().
 */
class TShop extends TShopAutoParent implements IPkgShopVatable
{
    public const VIEW_PATH = 'pkgShop/views/db/TShop';
    public const SESSION_ACTIVE_SEARCH_CACHE_ID = 'session-shop-active-search-cache-id';
    public const SESSION_AFFILIATE_CODE = 'mtshopbasketcoreaffiliatecode';
    public const SESSION_ACTIVE_VARIANT_ARRAY = 'aShopActiveVariantArray';
    public const CMS_COUNTER_ORDER = 'order';
    public const CMS_COUNTER_CUSTOMER = 'customer';

    /**
     * the active search object.
     */
    protected ?TdbShopSearchCache $oActiveSearchCache = null;

    /**
     * set the affiliate partner code for the current session.
     *
     * @param string $sCode
     *
     * @return void
     */
    public function SetAffiliateCode($sCode)
    {
        $_SESSION[TdbShop::SESSION_AFFILIATE_CODE] = $sCode;
    }

    /**
     * return the affiliate partner code for the current session.
     *
     * @return string|false
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
     * store a copy of the active search object.
     *
     * @return void
     */
    public function SetActiveSearchCacheObject(TdbShopSearchCache $oActiveSearchCache)
    {
        $this->oActiveSearchCache = $oActiveSearchCache;
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
    public function GetActiveSearchObject()
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
     * @return array|null
     */
    public static function GetActiveCategoryPath()
    {
        /** @var array<string, TdbShopCategory>|null $aCategoryList */
        static $aCategoryList;

        if (!isset($aCategoryList)) {
            $aCategoryList = null;
            $oCurrentCategory = self::getShopService()->getActiveCategory();
            if (!is_null($oCurrentCategory)) {
                $aCategoryList = [];
                do {
                    $aCategoryList[$oCurrentCategory->id] = $oCurrentCategory;
                } while ($oCurrentCategory = $oCurrentCategory->GetParent());
                $aCategoryList = array_reverse($aCategoryList, true);
            }
        }

        return $aCategoryList;
    }

    /**
     * Returns the active manufacturer.
     *
     * @return TdbShopManufacturer|null
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
     * return current active filter conditions.
     *
     * @return array
     */
    public static function GetActiveFilter()
    {
        static $aFilter = 'x';
        if ('x' === $aFilter) {
            $oGlobal = TGlobal::instance();
            $aFilter = $oGlobal->GetUserData('lf');
            if (!is_array($aFilter)) {
                $aFilter = [];
            }
            // now reduce filter list to valid filter fields
            $aValidFilterFields = TdbShop::GetValidFilterFields();
            $aValidFilter = [];
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
     * @param string $sExcludeKey
     *
     * @return string
     */
    public static function GetActiveFilterString($sExcludeKey = '')
    {
        $aFilter = TdbShop::GetActiveFilter();
        $aTmp = [];
        foreach ($aFilter as $filterKey => $filterVal) {
            if ($filterKey != $sExcludeKey) {
                $aTmp[] = TdbShop::GetFilterSQLString($filterKey, $filterVal);
            }
        }

        return implode(' AND ', $aTmp);
    }

    /**
     * @param string $sFilterKey
     * @param string $sFilterVal
     *
     * @psalm-param string|TdbShopCategory::FILTER_KEY_* $sFilterKey
     *
     * @return string
     */
    public static function GetFilterSQLString($sFilterKey, $sFilterVal)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ServiceLocator::get('database_connection');

        $sSQL = '';

        switch ($sFilterKey) {
            case TdbShopCategory::FILTER_KEY_NAME:
                $oCat = TdbShopCategory::GetNewInstance();
                /** @var $oCat TdbShopCategory */
                if ($oCat->Load($sFilterVal)) {
                    $aCatIdList = $oCat->GetAllChildrenIds();
                    $aCatIdList[] = $oCat->id;

                    $quotedCatIds = array_map(function ($id) use ($connection) {
                        return $connection->quote($id);
                    }, $aCatIdList);

                    $sSQL .= '`shop_article_shop_category_mlt`.`target_id` IN ('.implode(', ', $quotedCatIds).')';
                }
                break;

            default:
                $quotedValue = $connection->quote($sFilterVal);
                $sSQL .= "{$sFilterKey} = {$quotedValue}";
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
        return [TdbShopManufacturer::FILTER_KEY_NAME, TdbShopCategory::FILTER_KEY_NAME];
    }

    /**
     * returns the default country id. this is usually the id set in the shop table, but may also be fetched via
     * ip lookup.
     */
    public function GetDefaultCountryId(): ?string
    {
        $countryId = $this->fieldDataCountryId;

        return (false === empty($countryId)) ? $countryId : null;
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
     * @return TdbShopVat|null
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
        $cmsCounter = new esono\pkgCmsCounter\CmsCounter(ServiceLocator::get('database_connection'));

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
        $cmsCounter = new esono\pkgCmsCounter\CmsCounter(ServiceLocator::get('database_connection'));

        return $cmsCounter->get($this, self::CMS_COUNTER_CUSTOMER);
    }

    /* LINK SECTION - this section holds methods that return links to shop specific pages */

    /**
     * return link to checkout page - NOTE: all get/post parameters will be excluded. if you need some
     * make sure you remove them from the list.
     *
     * @param bool $bJumpAsFarAsPossible - set to true and the system will try to jump into the order process as far as possible (user step if not logged in, or shipping step if logged in)
     * @param bool $bTargetBasketPageWithoutRedirect
     *
     * @return string
     */
    public function GetBasketLink($bJumpAsFarAsPossible = false, $bTargetBasketPageWithoutRedirect = false)
    {
        $aParams = ['module_fnc['.$this->GetBasketModuleSpotName().']' => 'JumpToBasketPage'];
        if ($bJumpAsFarAsPossible) {
            $aParams['bJumpAsFarAsPossible'] = '1';
        }
        $oGlobal = TGlobal::instance();
        $aUserData = $oGlobal->GetUserData();
        $aExcludeParams = [];
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
                $aParams['sourceurl'] = $activePageService->getLinkToActivePageRelative([], $aExcludeParams);
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
     * @param array $aParameters - optional parameters to add to the link
     * @param bool $bForcePortalLink - set to true if you want to include the portal domain (http://..../) part in the link.
     * @param string $sAnchorName - set name to jump to within the page
     *
     * @return string
     */
    public function GetLinkToSystemPage($sSystemPageName, $aParameters = null, $bForcePortalLink = false, $sAnchorName = '')
    {
        if (null === $aParameters) {
            $aParameters = [];
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
     * @param array $aParameters - optional parameters to add to the link
     * @param string $sAnchorName - set name to jump to within the page
     *
     * @return string
     */
    public function getLinkToSystemPageRelative($sSystemPageName, $aParameters = null, $sAnchorName = '')
    {
        if (null === $aParameters) {
            $aParameters = [];
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
        $aSystemPages = [];
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
     * @deprecated since 6.1.0
     *
     * @param string $sLinkText - the text to display in the link - no OutHTML will be called - so make sure to escape the incoming text
     * @param string $sSystemPageName
     * @param array $aParameters - optional parameters to add to the link
     * @param bool $bForcePortalLink - set to true if you want to include the portal domain (http://..../) part in the link.
     * @param int $iWidth - The popup's width in pixels
     * @param int $iHeight - The popup's height in pixels
     * @param string $sCSSClass - additional css class to add to the link
     * @param string $sAnchorName - set name to jump to within the page*
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
     * @return TdbShopSystemInfo|null
     */
    public function GetShopInfo($sShopInfoName)
    {
        $oInfo = TdbShopSystemInfo::GetNewInstance();
        if (!$oInfo->LoadFromFields(['shop_id' => $this->id, 'name_internal' => $sShopInfoName])) {
            $oInfo = null;
        }

        return $oInfo;
    }

    /* RENDER SECTION - this section holds methods that render shop sepcific information */

    /**
     * render the shipping infos.
     *
     * @param string $sViewName
     * @param string $sViewType
     * @param array $aCallTimeVars
     *
     * @return string
     */
    public function RenderShippingInfo($sViewName, $sViewType, $aCallTimeVars = [])
    {
        $oView = new TViewParser();
        $oView->AddVar('oShop', $this);
        $oShippingIntroText = $this->GetShopInfo('shipping-intro');
        $oShippingEndText = $this->GetShopInfo('shipping-end');
        $oView->AddVar('oShippingIntroText', $oShippingIntroText);
        $oView->AddVar('oShippingEndText', $oShippingEndText);

        // get the shipping list for users not sigend in
        $oPublicShippingGroups = TdbShopShippingGroupList::GetPublicShippingGroups();
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
        return [];
    }

    /**
     * return ajax url to a function in the central shop handler.
     *
     * @param string $sMethod
     * @param array $aParameter
     *
     * @return string
     */
    public function GetCentralShopHandlerAjaxURL($sMethod, $aParameter = [])
    {
        $oGlobal = TGlobal::instance();

        $aParameter[MTShopCentralHandler::URL_CALLING_SPOT_NAME] = $oGlobal->GetExecutingModulePointer()->sModuleSpotName;
        $aRealParams = ['module_fnc' => [$this->fieldShopCentralHandlerSpotName => 'ExecuteAjaxCall'], '_fnc' => $sMethod, MTShopCentralHandler::URL_DATA => $aParameter];
        $oActivePage = self::getActivePageService()->getActivePage();

        return $oActivePage->GetRealURLPlain($aRealParams);
    }

    /**
     * overwrite the method to allow caching.
     *
     * @return TdbPkgShopPrimaryNaviList
     *
     * @psalm-suppress UndefinedClass
     *
     * @FIXME References `TdbShopPrimaryNaviList` where `TdbPkgShopPrimaryNaviList` is probably meant
     */
    public function GetFieldShopPrimaryNaviList()
    {
        $oNaviList = $this->GetFromInternalCache('oFieldShopPrimaryNaviList');
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
     *
     * @return void
     */
    public static function RegisterActiveVariantForSpot($sSpotName, $sParentId, $sShopVariantArticleId)
    {
        if (!array_key_exists(TdbShop::SESSION_ACTIVE_VARIANT_ARRAY, $_SESSION)) {
            $_SESSION[TdbShop::SESSION_ACTIVE_VARIANT_ARRAY] = [];
        }
        if (!array_key_exists($sSpotName, $_SESSION[TdbShop::SESSION_ACTIVE_VARIANT_ARRAY])) {
            $_SESSION[TdbShop::SESSION_ACTIVE_VARIANT_ARRAY][$sSpotName] = [];
        }
        $_SESSION[TdbShop::SESSION_ACTIVE_VARIANT_ARRAY][$sSpotName][$sParentId] = $sShopVariantArticleId;
    }

    /**
     * return the active variant for a parent article and a given spot. returns false if no
     * variant has been selected for that parent in the spot yet.
     *
     * @param string $sParentId
     *
     * @return string|false
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
     *
     * @return void
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
        $oURLData = TCMSSmartURLData::GetActive();
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
        return [['table' => $this->table, 'id' => $this->id]];
    }

    /**
     * commit the current content to cache - need only be called if something relevant
     * changes in the object.
     *
     * @return void
     */
    public function CacheCommit()
    {
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
        $aKey = ['class' => 'TdbShop', 'ident' => 'objectInstance', 'portalid' => $iPortalId];

        return ServiceLocator::get('chameleon_system_core.cache')->GetKey($aKey);
    }

    private static function getActivePageService(): ActivePageServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    private function getSystemPageService(): SystemPageServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.system_page_service');
    }

    private static function getPortalDomainService(): PortalDomainServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }

    private static function getShopService(): ShopServiceInterface
    {
        return ServiceLocator::get('chameleon_system_shop.shop_service');
    }
}
