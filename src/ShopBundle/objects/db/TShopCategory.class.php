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
use ChameleonSystem\CoreBundle\Util\UrlUtil;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class TShopCategory extends TShopCategoryAutoParent implements ICMSSeoPatternItem, IPkgShopVatable, ICmsLinkableObject
{
    const VIEW_PATH = '/pkgShop/views/db/TShopCategory';
    const FILTER_KEY_NAME = 'cattreeid';

    /**
     * return the vat group of the category.
     *
     * @return TdbShopVat
     */
    public function GetVat()
    {
        return $this->GetFieldShopVat();
    }

    /**
     * returns a link that restricts the current search to the category.
     *
     * @return string
     */
    public function GetSearchRestrictionLink()
    {
        // get current search... then add filter
        $oShop = $this->getShopService()->getActiveShop();
        $oSearchCache = &$oShop->GetActiveSearchObject();
        //$oSearchCache->aFilter[TdbShopCategory::FILTER_KEY_NAME] = $this->id;
        return $oSearchCache->GetSearchLink(array(TdbShopCategory::FILTER_KEY_NAME => $this->id));
    }

    /**
     * return the link to the category. note: if the category is connected to a primary navi
     * of the shop AND that navi node also has a page attached, then we return the link
     * to that page, instead of the link to the category (this allows us to create category entry pages).
     *
     * @param bool $bAbsolute - set to true if you want to include the domain in the link (absolute link)
     *
     * @return string
     */
    public function GetLink($bAbsolute = false, $sAnchor = null, $aOptionalParameters = array(), TdbCmsPortal $portal = null, TdbCmsLanguage $language = null)
    {
        $sInternalCacheKey = 'sLinkToCategory';
        if ($bAbsolute) {
            $sInternalCacheKey .= 'WithPortal';
        }
        $sLink = $this->GetFromInternalCache($sInternalCacheKey);
        if (is_null($sLink)) {
            $oShop = $this->getShopService()->getActiveShop();
            $oNaviNodeFound = null;
            if (method_exists($oShop, 'GetFieldShopPrimaryNaviList')) {
                $oNaviNodes = $oShop->GetFieldShopPrimaryNaviList();
                $oNaviNodeFound = $oNaviNodes->FindItemWithProperty('fieldShopCategoryId', $this->id);
                if (empty($oNaviNodeFound->fieldContentNode)) {
                    $oNaviNodeFound = null;
                }
            }
            if (!is_null($oNaviNodeFound) && !empty($oNaviNodeFound->fieldContentNode)) {
                $treeService = static::getTreeService();
                $oTreeNode = $treeService->getById($oNaviNodeFound->fieldContentNode);
                if ($bAbsolute) {
                    $sLink = $treeService->getLinkToPageForTreeAbsolute($oTreeNode, array(), $language);
                } else {
                    $sLink = $treeService->getLinkToPageForTreeRelative($oTreeNode, array(), $language);
                }
            } else {
                try {
                    if ($bAbsolute) {
                        $sPageLink = $this->getSystemPageService()->getLinkToSystemPageAbsolute('products', array(), $portal, $language);
                    } else {
                        $sPageLink = $this->getSystemPageService()->getLinkToSystemPageRelative('products', array(), $portal, $language);
                    }
                    if ('.html' === substr($sPageLink, -5)) {
                        $sPageLink = substr($sPageLink, 0, -5).'/';
                    }
                    if ('/' === substr($sPageLink, -1)) {
                        $sPageLink = substr($sPageLink, 0, -1);
                    }
                } catch (RouteNotFoundException $e) {
                    $sPageLink = '';
                }
                $sCatUrl = $this->GetURLPath();
                if ('/' !== substr($sCatUrl, 0, 1)) {
                    $sCatUrl = '/'.$sCatUrl;
                }
                $sLink = $sPageLink.$sCatUrl;
            }
            if (null === $portal) {
                $portal = $this->getPortalDomainService()->getActivePortal();
            }
            if (null !== $portal && true === $portal->fieldUseSlashInSeoUrls) {
                if (false === CHAMELEON_SEO_URL_REMOVE_TRAILING_SLASH) {
                    $sLink .= '/';
                }
            } else {
                $sLink .= '.html';
            }

            $this->SetInternalCache($sInternalCacheKey, $sLink);
        }
        if (null !== $aOptionalParameters && count($aOptionalParameters) > 0) {
            $sLink .= $this->getUrlUtil()->getArrayAsUrl($aOptionalParameters, '?', '&');
        }

        if (null !== $sAnchor) {
            $sLink .= '#'.urlencode($sAnchor);
        }

        return $sLink;
    }

    /**
     * returns a URL escaped path to the current category.
     *
     * @return string
     */
    public function GetURLPath()
    {
        return $this->fieldUrlPath;
    }

    /**
     * return an ID list of all child categories (complete depth).
     *
     * @return array
     */
    public function GetAllChildrenIds()
    {
        $aChildIdList = $this->GetFromInternalCache('aChildIdList');
        if (null !== $aChildIdList) {
            return $aChildIdList;
        }
        $aChildIdList = array();
        $oChildren = &$this->GetChildren();
        while ($oChild = &$oChildren->Next()) {
            $aChildIdList[] = $oChild->id;
            $aChildChildIdList = $oChild->GetAllChildrenIds();
            $aChildIdList = array_merge($aChildIdList, $aChildChildIdList);
        }
        $this->SetInternalCache('aChildIdList', $aChildIdList);

        return $aChildIdList;
    }

    /**
     * return all children of this category.
     *
     * @return TdbShopCategoryList
     */
    public function &GetChildren()
    {
        return TdbShopCategoryList::GetChildCategories($this->id, null, $this->GetLanguage());
    }

    /**
     * returns a count of the number of articles in the category. the method caches
     * the data since a count is expensive.
     *
     * @param bool $bIncludeSubcategoriesInCount
     *
     * @return int
     */
    public function GetNumberOfArticlesInCategory($bIncludeSubcategoriesInCount = false)
    {
        if ($bIncludeSubcategoriesInCount) {
            $oArticleList = $this->GetArticleListIncludingSubcategories();
        } else {
            $oArticleList = $this->GetArticleList();
        }

        return $oArticleList->Length();
    }

    /**
     * return article list for current category
     * note: the result ist cached in the class instance...
     *
     * @param string $sOrderBy
     * @param array  $aFilter  - any filter restrictions you want to add (must be filds within the shop table)
     *
     * @return TdbShopArticleList
     */
    public function &GetArticleList($sOrderBy = null, $aFilter = array())
    {
        $oCategoryArticles = $this->GetFromInternalCache('oArticleList');
        if (is_null($oCategoryArticles)) {
            $oCategoryArticles = &TdbShopArticleList::LoadCategoryArticleList($this->id, $sOrderBy, -1, $aFilter);
            $this->SetInternalCache('oArticleList', $oCategoryArticles);
        }

        return $oCategoryArticles;
    }

    /**
     * return article list for current category including articles for all subcategories
     * note: the result ist cached in the class instance...
     *
     * @param string $sOrderBy
     * @param array  $aFilter  - any filter restrictions you want to add (must be filds within the shop table)
     *
     * @return TdbShopArticleList
     */
    public function GetArticleListIncludingSubcategories($sOrderBy = null, $aFilter = array())
    {
        $sCacheKey = $this->getCacheKeyForArticleList($sOrderBy, $aFilter);
        $oCategoryArticles = $this->GetFromInternalCache($sCacheKey);
        if (is_null($oCategoryArticles)) {
            $aCategoryIds = $this->GetAllChildrenIds();
            $aCategoryIds[] = $this->id;
            $oCategoryArticles = &TdbShopArticleList::LoadCategoryArticleListForCategoryList($aCategoryIds, $sOrderBy, -1, $aFilter);
            $this->SetInternalCache($sCacheKey, $oCategoryArticles);
        }

        return $oCategoryArticles;
    }

    private function getCacheKeyForArticleList($sOrderBy, $aFilter)
    {
        return md5('oArticleListIncludingSubcategories'.serialize($sOrderBy).serialize($aFilter));
    }

    /**
     * return the parent category, or null if no parent is found.
     *
     * @return TdbShopCategory
     */
    public function &GetParent()
    {
        $oParent = null;
        if (!empty($this->fieldShopCategoryId)) {
            $oParent = TdbShopCategory::GetNewInstance();
            $oParent->SetLanguage($this->iLanguageId);
            if (!$oParent->Load($this->fieldShopCategoryId)) {
                $oParent = null;
            }
        }

        return $oParent;
    }

    /**
     * returns true if this category is in the active category path (ie is the active
     * category, or a parent of the active category).
     *
     * @return bool
     */
    public function IsInActivePath()
    {
        $bIsInActivePath = $this->GetFromInternalCache('bIsInActivePath');
        if (is_null($bIsInActivePath)) {
            $aCatPath = TdbShop::GetActiveCategoryPath();
            if (!is_null($aCatPath) && array_key_exists($this->id, $aCatPath)) {
                $bIsInActivePath = true;
            }
            $this->SetInternalCache('bIsInActivePath', $bIsInActivePath);
        }

        return $bIsInActivePath;
    }

    /**
     * return true if the category id passed is in the path to this category.
     *
     * @param string $sCategoryId
     *
     * @return bool
     */
    public function IsInCategoryPath($sCategoryId)
    {
        $oBreadCrumb = $this->GetBreadcrumb();
        $oMatchingNode = $oBreadCrumb->FindItemWithProperty('id', $sCategoryId);
        if ($oMatchingNode) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * return the category path as a string, each node separated by the sSeparator.
     *
     * @param string $sSeparator
     *
     * @return string
     */
    public function GetCategoryPathAsString($sSeparator = '/')
    {
        $sKey = 'sCatPathCache'.$sSeparator;

        $sPath = $this->GetFromInternalCache($sKey);
        if (is_null($sPath)) {
            $sPath = '';
            $oParent = &$this->GetParent();
            if ($oParent) {
                $sPath = $oParent->GetCategoryPathAsString($sSeparator);
            }
            if (!empty($sPath)) {
                $sPath .= $sSeparator;
            }
            $sPath .= $this->GetName();
            $this->SetInternalCache($sKey, $sPath);
        }

        return $sPath;
    }

    /**
     * return the currents category root category.
     *
     * @return TdbShopCategory
     */
    public function &GetRootCategory()
    {
        $oRootCategory = &$this->GetFromInternalCache('oRootCategory');
        if (is_null($oRootCategory)) {
            $oRootCategory = clone $this;
            while (!empty($oRootCategory->fieldShopCategoryId)) {
                $oRootCategory = &$oRootCategory->GetParent();
            }
            $this->SetInternalCache('oRootCategory', $oRootCategory);
        }

        return $oRootCategory;
    }

    /**
     * return a list of all categories to the current category.
     *
     * @return TIterator
     */
    public function &GetBreadcrumb()
    {
        $oBreadCrumb = &$this->GetFromInternalCache('oCategoryBreadcrumb');
        if (is_null($oBreadCrumb)) {
            $oBreadCrumb = &TdbShopCategoryList::GetCategoryPath($this->id, null, $this->GetLanguage());
            $this->SetInternalCache('oCategoryBreadcrumb', $oBreadCrumb);
        } else {
            $oBreadCrumb->GoToStart();
        }

        return $oBreadCrumb;
    }

    /**
     * used to display an article.
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
        $oView->AddVar('oCategory', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, self::VIEW_PATH, $sViewType);
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
        return array();
    }

    /* SECTION: CACHE RELEVANT METHODS FOR THE RENDER METHOD

    /**
     * Add view based clear cache triggers for the Render method here
     *
     * @param array $aClearTriggers - clear trigger array (with current contents)
     * @param string $sViewName - view being requested
     * @param string $sViewType - location of the view (Core, Custom-Core, Customer)
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function AddClearCacheTriggers(&$aClearTriggers, $sViewName, $sViewType)
    {
    }

    /**
     * used to set the id of a clear cache (ie. related table).
     *
     * @param string $sTableName - the table name
     *
     * @return int|null|string
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function GetClearCacheTriggerTableValue($sTableName)
    {
        $sValue = '';
        switch ($sTableName) {
            case $this->table:
                $sValue = $this->id;
                break;

            default:
                break;
        }

        return $sValue;
    }

    /**
     * returns an array with all table names that are relevant for the render function.
     *
     * @param string $sViewName - the view name being requested (if know by the caller)
     * @param string $sViewType - the view type (core, custom-core, customer) being requested (if know by the caller)
     *
     * @return array
     */
    public static function GetCacheRelevantTables($sViewName = null, $sViewType = null)
    {
        $aTables = array();
        $aTables[] = 'shop_category';

        return $aTables;
    }

    /**
     * return keywords for the meta tag on article detail pages.
     *
     * @return array
     */
    public function GetMetaKeywords()
    {
        if (strlen(trim(strip_tags($this->fieldMetaKeywords))) > 0) {
            $aKeywords = explode(',', trim(strip_tags($this->fieldMetaKeywords)));
        } else {
            $aKeywords = explode(' ', $this->fieldName.' '.strip_tags($this->fieldDescriptionShort));
        }

        return $aKeywords;
    }

    /**
     * return meta description.
     *
     * @return string
     */
    public function GetMetaDescription()
    {
        $sDesc = trim($this->fieldMetaDescription);
        if (empty($sDesc)) {
            $oParent = &$this->GetParent();
            if (!is_null($oParent)) {
                $sDesc .= $oParent->GetMetaDescription().' ';
            }
            $sDesc .= $this->fieldName.' '.strip_tags($this->fieldDescriptionShort);
        }

        return $sDesc;
    }

    /**
     * returns count of articles found in category
     * if $bRecursive is true, then this check walks down the category tree recursively.
     *
     * @param bool $bRecursive
     *
     * @return int - number of articles found in category
     *
     * @deprecated since 6.2.0 - use GetNumberOfArticlesInCategory() instead.
     */
    protected function NumberOfArticlesInCategory($bRecursive = false)
    {
        return $this->GetNumberOfArticlesInCategory($bRecursive);
    }

    /**
     * Hook for implementing category display logic.
     *
     * @return bool
     */
    public function AllowDisplayInShop()
    {
        $bShowCat = false;

        $oShop = TdbShop::GetInstance();
        if (!is_null($oShop) && !$oShop->fieldShowEmptyCategories) {
            $bShowCat = ($this->GetNumberOfArticlesInCategory(true) > 0);
        } else {
            $bShowCat = true;
        }

        if (false == $this->fieldActive || false == $this->fieldTreeActive) {
            $bShowCat = false;
        }

        $targetPage = $this->getTargetPage();
        if (null === $targetPage || false === $targetPage) {
            return true;
        }
        // show only if the user has access to the category target page
        if (false === $targetPage->AllowAccessByCurrentUser()) {
            return false;
        }

        return $bShowCat;
    }

    /**
     * Get SEO pattern of actual article.
     *
     * @param string $sPaternIn
     *
     * @return array
     */
    public function GetSeoPattern(&$sPaternIn)
    {
        //$sPaternIn = "[{PORTAL_NAME}] - [{CATEGORY_NAME}]"; //default
        $aPatRepl = null;

        if (!empty($this->sqlData['seo_pattern'])) {
            $sPaternIn = $this->sqlData['seo_pattern'];
        }

        $aPatRepl = array();
        $activePage = $this->getActivePageService()->getActivePage();
        $aPatRepl['PORTAL_NAME'] = $activePage->GetPortal()->GetTitle();
        $aPatRepl['PAGE_NAME'] = $activePage->GetName();
        $aPatRepl['CATEGORY_NAME'] = $this->GetName();

        return $aPatRepl;
    }

    /**
     * return the name formated for the breadcrumb.
     */
    public function GetBreadcrumbName()
    {
        return $this->GetName();
    }

    /**
     * return the part of the category that should be used as part of the product name.
     *
     * @return string
     */
    public function GetProductNameExtensions()
    {
        $sName = trim($this->fieldNameProduct);
        if (empty($sName)) {
            $sName = $this->fieldName;
        }

        return $sName;
    }

    /**
     * returns the category color.
     *
     * @param string $sDefaultColor - default color = black;
     *
     * @return string
     */
    public function GetCurrentColorCode($sDefaultColor = '000000')
    {
        $oRootCat = $this->GetRootCategory();
        /** @var $oRootCat TdbShopCategory */
        $sColorcode = $oRootCat->fieldColorcode;
        if ('' == $sColorcode) {
            $sColorcode = $sDefaultColor;
        }

        return $sColorcode;
    }

    /**
     * @return TIterator
     */
    public function getParentCategories()
    {
        return TdbShopCategoryList::getParentCategoryList($this->id);
    }

    /**
     * return true if the tree until this category is active.
     *
     * @return bool
     */
    public function parentCategoriesAreActive()
    {
        $treeIsActive = true;
        $parentCategoryList = $this->getParentCategories();
        while ($category = $parentCategoryList->Next()) {
            if (false === $category->fieldActive) {
                $treeIsActive = false;
                break;
            }
        }

        return $treeIsActive;
    }

    /**
     * @return TdbCmsTplPage
     */
    public function getTargetPage()
    {
        static $defaultPage = null; // since most categories will have the same default page, we store a static copy for performance
        $targetPage = $this->GetFromInternalCache('target_page');
        if (null !== $targetPage) {
            return $targetPage;
        }

        if ('' !== $this->fieldDetailPageCmsTreeId) {
            $node = static::getTreeService()->getById($this->fieldDetailPageCmsTreeId);
            if (null !== $node) {
                $targetPage = $node->GetLinkedPageObject();
            }
        }

        if (null === $targetPage || false === $targetPage) {
            if (null !== $defaultPage) {
                return $defaultPage;
            }
            $activePortal = self::getPortalDomainService()->getActivePortal();
            if (null === $activePortal) {
                return null;
            }
            $targetPageId = $activePortal->GetSystemPageId('products');
            $defaultPage = TdbCmsTplPage::GetNewInstance($targetPageId);
            $targetPage = $defaultPage;
        }

        $this->SetInternalCache('target_page', $targetPage);

        return $targetPage;
    }

    protected function GetQueryString(array $conditions)
    {
        $activeRestriction = TdbShopCategoryList::GetActiveCategoryQueryRestriction();
        if ('' !== $activeRestriction) {
            $conditions[] = $activeRestriction;
        }

        return parent::GetQueryString($conditions);
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return ShopServiceInterface
     */
    private function getShopService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
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
    private function getPortalDomainService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }

    /**
     * @return UrlUtil
     */
    private function getUrlUtil()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.url');
    }
}
